<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One production batch = the issue(s) it consumed.
 *
 * Status flow:
 *   recorded   – manager submitted clean output + NC, nothing in stock yet
 *   in_rework  – clean output pushed to FG, NC held for rework
 *   completed  – everything posted (rework output to FG, final NC to NC stock)
 */
class Production extends Model
{
    protected $fillable = [
        'product_id', 'production_number', 'status', 'batch_type',
        'input_quantity', 'input_unit',
        'output_quantity', 'non_conforming', 'impurity', 'purity',
        'material_cost', 'packing_cost', 'packaging_type_id', 'package_count',
        'cost_per_unit', 'stock_quantity', 'nc_value',
        'rework_output', 'rework_nc', 'rework_packing_cost', 'rework_packaging_type_id',
        'rework_package_count', 'rework_cost_per_unit',
        'rework_stock_quantity', 'nc_product_id', 'rework_notes',
        'fg_posted_at', 'rework_completed_at',
        'pad_type', 'pad_number', 'description',
    ];

    protected $casts = [
        'input_quantity'        => 'decimal:4',
        'output_quantity'       => 'decimal:2',
        'non_conforming'        => 'decimal:2',
        'impurity'              => 'decimal:4',
        'purity'                => 'decimal:4',
        'material_cost'         => 'decimal:2',
        'packing_cost'          => 'decimal:2',
        'package_count'         => 'decimal:2',
        'cost_per_unit'         => 'decimal:4',
        'stock_quantity'        => 'decimal:2',
        'nc_value'              => 'decimal:2',
        'rework_output'         => 'decimal:2',
        'rework_nc'             => 'decimal:2',
        'rework_packing_cost'   => 'decimal:2',
        'rework_package_count'  => 'decimal:2',
        'rework_cost_per_unit'  => 'decimal:4',
        'rework_stock_quantity' => 'decimal:2',
        'fg_posted_at'          => 'datetime',
        'rework_completed_at'   => 'datetime',
    ];

    public const STATUS_LABELS = [
        'recorded'    => 'Results submitted',
        'in_progress' => 'Results submitted',
        'in_rework'   => 'NC in rework',
        'completed'   => 'Completed',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function ncProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'nc_product_id');
    }

    public function packagingType(): BelongsTo
    {
        return $this->belongsTo(PackagingType::class);
    }

    public function reworkPackagingType(): BelongsTo
    {
        return $this->belongsTo(PackagingType::class, 'rework_packaging_type_id');
    }

    /** Issues consumed in this batch */
    public function items(): HasMany
    {
        return $this->hasMany(ProductionItem::class);
    }

    /** Final NC posted to NC stock */
    public function ncLots(): HasMany
    {
        return $this->hasMany(NonConformity::class);
    }

    public function getIssueNumbersAttribute(): string
    {
        return $this->items->map(fn ($i) => $i->issue?->issue_number)->filter()->implode(', ');
    }

    public function getTotalInputAttribute(): float
    {
        if ($this->input_quantity !== null) {
            return (float) $this->input_quantity;
        }
        return (float) $this->items->sum(fn ($i) => $i->issue ? $i->issue->total_quantity : 0);
    }

    /** Weight that is neither clean output nor NC (moisture, dust, spillage) */
    public function getLossQuantityAttribute(): float
    {
        return max(0, round($this->total_input - (float) $this->output_quantity - (float) $this->non_conforming, 4));
    }

    public function getReworkLossAttribute(): float
    {
        if ($this->rework_output === null) {
            return 0;
        }
        return max(0, round((float) $this->non_conforming - (float) $this->rework_output - (float) $this->rework_nc, 4));
    }

    /** Material + packing of the first stage */
    public function getTotalDirectCostAttribute(): float
    {
        return round((float) $this->material_cost + (float) $this->packing_cost, 2);
    }

    /** Batch cost including rework packing */
    public function getTotalBatchCostAttribute(): float
    {
        return round($this->total_direct_cost + (float) $this->rework_packing_cost, 2);
    }

    /** Clean kg that reached FG across both stages */
    public function getTotalCleanOutputAttribute(): float
    {
        return (float) $this->output_quantity + (float) $this->rework_output;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'completed' => 'success',
            'in_rework' => 'warning',
            default     => 'info',
        };
    }

    public static function nextProductionNumber(): string
    {
        $last = static::orderByDesc('id')->value('production_number');
        $n = $last ? ((int) substr($last, 4)) + 1 : 1;
        return 'PRD-' . str_pad($n, 5, '0', STR_PAD_LEFT);
    }
}
