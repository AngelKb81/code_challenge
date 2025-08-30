<?php

namespace App\Models;

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
        return $this->status === 'available' && $this->quantity > 0;
    }

    /**
     * Get available quantity (considering active requests).
     */
    public function getAvailableQuantityAttribute(): int
    {
        $requestedQuantity = $this->activeRequests()->sum('quantity_requested');
        return max(0, $this->quantity - $requestedQuantity);
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
        return $query->where('status', 'available')->where('quantity', '>', 0);
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
}
