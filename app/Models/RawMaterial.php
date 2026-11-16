<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RawMaterial extends Model
{
    protected $fillable = [
        'name', 'sku', 'category_id', 'supplier_id',
        'quantity', 'unit', 'cost_price', 'min_stock',
        'description', 'status',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'min_stock' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function issues(): HasMany
    {
        return $this->hasMany(Issue::class);
    }

    /** Ingredient lines this material appears on (one issue can hold several materials) */
    public function issueItems(): HasMany
    {
        return $this->hasMany(IssueItem::class);
    }

    /** Stock value = quantity on hand × unit price */
    public function getTotalCostAttribute(): float
    {
        return round((float) $this->quantity * (float) $this->cost_price, 2);
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->quantity <= $this->min_stock;
    }

    /**
     * Adjust on-hand stock by a signed delta.
     * Positive = stock in, negative = issued to production.
     */
    public function adjustStock(float $delta): void
    {
        $newQuantity = $this->quantity + $delta;

        if ($newQuantity < 0) {
            throw new \DomainException("Insufficient stock for {$this->name}. Available: {$this->quantity} {$this->unit}.");
        }

        $this->update(['quantity' => $newQuantity]);
    }
}
