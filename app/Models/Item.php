<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'category',
        'status',
        'brand',
        'description',
        'quantity',
        'serial_number',
        'location',
        'purchase_price',
        'purchase_date',
        'warranty_expiry',
        'supplier',
        'notes',
        'image_path',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'purchase_date' => 'date',
        'warranty_expiry' => 'date',
        'purchase_price' => 'decimal:2',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['available_quantity'];

    /**
     * Get all requests for this item.
     */
    public function requests(): HasMany
    {
        return $this->hasMany(Request::class);
    }

    /**
     * Get active requests for this item.
     */
    public function activeRequests(): HasMany
    {
        return $this->hasMany(Request::class)->whereIn('status', ['approved', 'in_use']);
    }

    /**
     * Get pending requests for this item.
     */
    public function pendingRequests(): HasMany
    {
        return $this->hasMany(Request::class)->where('status', 'pending');
    }

    /**
     * Check if item is available.
     */
    public function isAvailable(): bool
    {
        return $this->getAvailableQuantity() > 0;
    }

    /**
     * Check if item is under warranty.
     */
    public function isUnderWarranty(): bool
    {
        return $this->warranty_expiry && $this->warranty_expiry->isFuture();
    }

    /**
     * Scope to filter by category.
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to filter available items.
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    /**
     * Scope to filter items with actual availability > 0.
     */
    public function scopeActuallyAvailable($query)
    {
        return $query->where('status', 'available')
            ->where('quantity', '>', 0)
            ->get()
            ->filter(function ($item) {
                return $item->getAvailableQuantity() > 0;
            });
    }

    /**
     * Scope to search items.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('brand', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('serial_number', 'like', "%{$search}%");
        });
    }

    /**
     * Calculate and update the status based on quantity.
     */
    public function calculateAndUpdateStatus(): void
    {
        $status = $this->calculateStatus();
        if ($this->status !== $status) {
            $this->update(['status' => $status]);
        }
    }

    /**
     * Calculate the status based on current condition.
     * Note: This method is kept for consistency but with simplified logic.
     * Availability is now handled by the available_quantity attribute.
     */
    public function calculateStatus(): string
    {
        // Status now only reflects operational condition, not stock availability
        return 'available';
    }

        /**
     * Get the quantity available for immediate use (today).
     * Calculates based on requests that are currently active (overlapping with today).
     * Excludes purchase_request type as they add items to inventory rather than remove them.
     */
    public function getAvailableQuantityAttribute(): int
    {
        $today = Carbon::today();
        
        $currentlyUsedQuantity = $this->requests()
            ->where('status', 'approved')
            ->where('request_type', 'existing_item')
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->sum('quantity_requested');

        return max(0, $this->quantity - $currentlyUsedQuantity);
    }

    /**
     * Helper method to get available quantity.
     */
    public function getAvailableQuantity(): int
    {
        return $this->getAvailableQuantityAttribute();
    }
}
