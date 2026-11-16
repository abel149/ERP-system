<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IssueItem extends Model
{
    protected $fillable = [
        'issue_id', 'raw_material_id', 'quantity', 'unit_cost', 'unit',
    ];

    protected $casts = [
        'quantity'  => 'decimal:2',
        'unit_cost' => 'decimal:2',
    ];

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }

    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(RawMaterial::class);
    }

    /** Line total: qty × unit_cost */
    public function getTotalAttribute(): float
    {
        return round((float) $this->quantity * (float) $this->unit_cost, 2);
    }

    public function getWeightKgAttribute(): ?float
    {
        return match (strtolower(trim($this->unit ?? $this->rawMaterial?->unit ?? ''))) {
            'kg', 'kilogram', 'kilograms' => (float) $this->quantity,
            'g', 'gram', 'grams' => (float) $this->quantity / 1000,
            default => null,
        };
    }
}
