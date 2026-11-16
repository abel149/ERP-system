<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackagingType extends Model
{
    protected $fillable = ['name', 'capacity_kg', 'unit_cost', 'is_active'];

    protected $casts = [
        'capacity_kg' => 'decimal:2',
        'unit_cost'   => 'decimal:2',
        'is_active'   => 'boolean',
    ];

    /** Packages needed to hold this many kilograms — exact fraction. */
    public function packsFor(float $kg): float
    {
        return round($kg / (float) $this->capacity_kg, 2);
    }

    /** Packing cost for this many kilograms: packs x unit cost. */
    public function costFor(float $kg, ?float $packs = null): float
    {
        return round(($packs ?? $this->packsFor($kg)) * (float) $this->unit_cost, 2);
    }
}
