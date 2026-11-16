<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id', 'product_id', 'quantity', 'unit_price', 'total',
        'unit_cost', 'rent_cost', 'utility_cost', 'machine_cost', 'other_cost',
        'margin_percent', 'vat_percent', 'vat_amount',
    ];

    protected $casts = [
        'quantity'       => 'decimal:2',
        'unit_price'     => 'decimal:2',
        'total'          => 'decimal:2',
        'unit_cost'      => 'decimal:2',
        'rent_cost'      => 'decimal:2',
        'utility_cost'   => 'decimal:2',
        'machine_cost'   => 'decimal:2',
        'other_cost'     => 'decimal:2',
        'margin_percent' => 'decimal:2',
        'vat_percent'    => 'decimal:2',
        'vat_amount'     => 'decimal:2',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** Other costs (rent + utility + machine time + OC) per stock unit. */
    public function getOtherCostsAttribute(): float
    {
        return (float) $this->rent_cost + (float) $this->utility_cost
            + (float) $this->machine_cost + (float) $this->other_cost;
    }

    /** Full unit cost: product cost (materials + packing) + other costs. */
    public function getTotalCostAttribute(): float
    {
        return (float) $this->unit_cost + $this->other_costs;
    }

    /** Gross profit earned on this line (before VAT). */
    public function getMarginAmountAttribute(): float
    {
        return ((float) $this->unit_price - $this->total_cost) * (float) $this->quantity;
    }
}
