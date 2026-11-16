<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Issue extends Model
{
    protected $fillable = [
        'raw_material_id', 'issue_number', 'status',
        'quantity', 'pad_type', 'pad_number', 'description',
        'intended_product_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    // ---- Relationships ----

    /** @deprecated kept for backfill compatibility — use items() instead */
    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(RawMaterial::class);
    }

    public function intendedProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'intended_product_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(IssueItem::class);
    }

    public function productionItem(): HasOne
    {
        return $this->hasOne(ProductionItem::class);
    }

    // ---- Accessors ----

    /**
     * Total quantity across all ingredient lines.
     * Falls back to the legacy `quantity` column for older records.
     */
    public function getTotalQuantityAttribute(): float
    {
        $sum = $this->items->sum('quantity');
        return $sum > 0 ? (float) $sum : (float) $this->quantity;
    }

    /**
     * Total value (qty × unit_cost) across all ingredient lines.
     */
    public function getTotalValueAttribute(): float
    {
        return (float) $this->items->sum(fn($i) => $i->quantity * $i->unit_cost);
    }

    /**
     * Whether this issue has already been linked to a production batch.
     */
    public function getIsConsumedAttribute(): bool
    {
        return $this->status === 'consumed';
    }

    // ---- Helpers ----

    public static function nextIssueNumber(): string
    {
        $last = static::orderByDesc('id')->value('issue_number');
        $n = $last ? ((int) substr($last, 4)) + 1 : 1;
        return 'ISS-' . str_pad($n, 5, '0', STR_PAD_LEFT);
    }
}
