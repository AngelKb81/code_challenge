<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Request extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'request_type',
        'item_id',
        'item_name',
        'item_description',
        'item_category',
        'item_brand',
        'estimated_cost',
        'supplier_info',
        'justification',
        'start_date',
        'end_date',
        'status',
        'reason',
        'notes',
        'quantity_requested',
        'approved_at',
        'approved_by',
        'rejection_reason',
        'returned_at',
        'priority',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
        'returned_at' => 'datetime',
        'estimated_cost' => 'decimal:2',
    ];

    /**
     * Get the user that made the request.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the item being requested.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the admin who approved the request.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Check if request is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->status === 'in_use' && $this->end_date->isPast();
    }

    /**
     * Check if request is active.
     */
    public function isActive(): bool
    {
        return in_array($this->status, ['approved', 'in_use']);
    }

    /**
     * Get the duration of the request in days.
     */
    public function getDurationAttribute(): int
    {
        return $this->start_date->diffInDays($this->end_date);
    }

    /**
     * Get days remaining until end date.
     */
    public function getDaysRemainingAttribute(): int
    {
        if ($this->end_date->isPast()) {
            return 0;
        }
        return now()->diffInDays($this->end_date);
    }

    /**
     * Get days overdue.
     */
    public function getDaysOverdueAttribute(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        return $this->end_date->diffInDays(now());
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter pending requests.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to filter approved requests.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope to filter active requests.
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['approved', 'in_use']);
    }

    /**
     * Scope to filter overdue requests.
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'in_use')
            ->where('end_date', '<', now());
    }

    /**
     * Scope to filter by user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by item.
     */
    public function scopeByItem($query, $itemId)
    {
        return $query->where('item_id', $itemId);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('start_date', [$startDate, $endDate])
            ->orWhereBetween('end_date', [$startDate, $endDate]);
    }

    /**
     * Scope per richieste di item esistenti.
     */
    public function scopeExistingItems($query)
    {
        return $query->where('request_type', 'existing_item');
    }

    /**
     * Scope per richieste di acquisto.
     */
    public function scopePurchaseRequests($query)
    {
        return $query->where('request_type', 'purchase_request');
    }

    /**
     * Verifica se è una richiesta di acquisto.
     */
    public function isPurchaseRequest(): bool
    {
        return $this->request_type === 'purchase_request';
    }

    /**
     * Verifica se è una richiesta per item esistente.
     */
    public function isExistingItemRequest(): bool
    {
        return $this->request_type === 'existing_item';
    }

    /**
     * Ottiene il nome dell'item (dinamico in base al tipo).
     */
    public function getItemNameAttribute(): string
    {
        if ($this->isPurchaseRequest()) {
            return $this->attributes['item_name'] ?? 'Item da acquistare';
        }

        return $this->item ? $this->item->name : 'Item non disponibile';
    }

    /**
     * Controllo sovrapposizioni temporali per item esistenti.
     */
    public static function hasOverlappingRequests($itemId, $startDate, $endDate, $excludeRequestId = null)
    {
        $query = self::where('item_id', $itemId)
            ->whereIn('status', ['approved', 'in_use'])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($subQ) use ($startDate, $endDate) {
                        $subQ->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            });

        if ($excludeRequestId) {
            $query->where('id', '!=', $excludeRequestId);
        }

        return $query->exists();
    }

    /**
     * Scope to filter by priority.
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }
}
