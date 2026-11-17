<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One worker row on a daily production report. Employee code/name and the
 * material names are snapshots so the printed sheet never changes later.
 */
class ProductionReportLine extends Model
{
    protected $fillable = [
        'production_report_id', 'employee_id',
        'group_code', 'employee_code', 'employee_name',
        'raw_material_id', 'material_code', 'material_name_am', 'material_name_en',
        'work_type', 'total_amount', 'impurity', 'remaining', 'working_hours', 'position',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'impurity' => 'decimal:2',
        'remaining' => 'decimal:2',
        'working_hours' => 'decimal:2',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(ProductionReport::class, 'production_report_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(RawMaterial::class);
    }

    /** Clean (good) output: total amount minus impurity and what is left. */
    public function getCleanAttribute(): float
    {
        return max(0, round((float) $this->total_amount - (float) $this->impurity - (float) $this->remaining, 2));
    }

    /** kg processed per working hour. */
    public function getTargetPerHourAttribute(): float
    {
        $hours = (float) $this->working_hours;

        return $hours > 0 ? round((float) $this->total_amount / $hours, 4) : 0.0;
    }

    /** Clean kg per working hour. */
    public function getSpeedAttribute(): float
    {
        $hours = (float) $this->working_hours;

        return $hours > 0 ? round($this->clean / $hours, 4) : 0.0;
    }

    /** Yield as a fraction (0.9 = 90%), shown like the spreadsheet. */
    public function getYieldRateAttribute(): float
    {
        $amount = (float) $this->total_amount;

        return $amount > 0 ? round($this->clean / $amount, 4) : 0.0;
    }

    /** "Processed (Work Done)" is the same figure as TOTAL AMOUNT on the sheet. */
    public function getProcessedAttribute(): float
    {
        return (float) $this->total_amount;
    }

    /** Whether this row recorded production (control / other-work rows stay blank). */
    public function getHasOutputAttribute(): bool
    {
        return (float) $this->total_amount > 0 || (float) $this->impurity > 0;
    }
}
