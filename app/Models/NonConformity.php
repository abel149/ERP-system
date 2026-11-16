<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class NonConformity extends Model
{
    protected $table = 'non_conformities';

    protected $fillable = [
        'production_id', 'quantity', 'status',
        'reworked_by_production_id', 'product_id', 'description', 'unit', 'cost_value',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'cost_value' => 'decimal:2',
    ];

    // ---- Relationships ----

    /** The production batch that generated this NC lot */
    public function production(): BelongsTo
    {
        return $this->belongsTo(Production::class);
    }

    /** The rework production batch that consumed this NC lot */
    public function reworkedBy(): BelongsTo
    {
        return $this->belongsTo(Production::class, 'reworked_by_production_id');
    }

    /** The NC product this lot was registered as (after rework) */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // ---- Helpers ----

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'  => 'Pending Rework',
            'reworked' => 'Sent to Rework',
            'final'    => 'Registered as NC Product',
            default    => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending'  => 'warning',
            'reworked' => 'info',
            'final'    => 'success',
            default    => 'secondary',
        };
    }
}
