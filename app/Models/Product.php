<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'name', 'sku', 'description',
        'cost_price', 'sell_price', 'quantity',
        'low_stock_threshold', 'is_active', 'is_nc',
    ];

    protected $casts = [
        'cost_price'          => 'decimal:2',
        'sell_price'          => 'decimal:2',
        'quantity'            => 'decimal:2',
        'low_stock_threshold' => 'decimal:2',
        'is_active'           => 'boolean',
        'is_nc'               => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->quantity <= $this->low_stock_threshold;
    }

    /**
     * Adjust product stock by a signed decimal delta and record the movement.
     * Positive = stock in; negative = stock out. When the movement is a
     * costed receipt, pass the unit cost so the blend is auditable.
     */
    public function adjustStock(float $delta, string $type, ?string $note = null, ?string $reference = null, ?int $userId = null, ?float $unitCost = null): void
    {
        $newQuantity = (float) $this->quantity + $delta;

        if ($newQuantity < 0) {
            throw new \DomainException("Insufficient stock for {$this->name}. Available: {$this->quantity}.");
        }

        $this->update(['quantity' => $newQuantity]);

        $this->stockMovements()->create([
            'user_id'        => $userId ?? auth()->id(),
            'type'           => $type,
            'quantity'       => $delta,
            'quantity_after' => $newQuantity,
            'unit_cost'      => $unitCost,
            'reference'      => $reference,
            'note'           => $note,
        ]);
    }

    /**
     * Receive stock at a known unit cost and roll the product's cost_price
     * into a weighted average of what is already on hand and what arrives:
     * (on-hand x old cost + incoming x new cost) / (on-hand + incoming).
     */
    public function receive(float $quantity, float $unitCost, string $type, ?string $note = null, ?string $reference = null): void
    {
        $onHand = max(0, (float) $this->quantity);
        $average = $onHand + $quantity > 0
            ? ($onHand * (float) $this->cost_price + $quantity * $unitCost) / ($onHand + $quantity)
            : $unitCost;

        $this->adjustStock($quantity, $type, $note, $reference, unitCost: $unitCost);
        $this->update(['cost_price' => round($average, 2)]);
    }

    /** Finished goods are always stocked in kilograms. */
    public function getStockUnitAttribute(): string
    {
        return 'kg';
    }

    /** Stock level with its unit, e.g. "47 kg". */
    public function getQuantityDisplayAttribute(): string
    {
        $qty = rtrim(rtrim(number_format((float) $this->quantity, 2), '0'), '.');

        return "{$qty} {$this->stock_unit}";
    }

    /** Kilograms of clean output become kilograms of stock, as-is. */
    public function stockUnitsFor(float $kg): float
    {
        return round($kg, 2);
    }

    /**
     * Auto-generate a unique SKU like FG-00001 or NC-00001.
     */
    public static function nextSku(bool $isNc = false): string
    {
        $prefix = $isNc ? 'NC' : 'FG';
        $last = static::where('sku', 'like', $prefix . '-%')->orderByDesc('id')->value('sku');
        $n = $last ? ((int) substr($last, strlen($prefix) + 1)) + 1 : 1;
        return $prefix . '-' . str_pad($n, 5, '0', STR_PAD_LEFT);
    }
}
