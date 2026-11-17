<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Daily Production Report (form OF-BIP-PD-002): the day's worker lines,
 * attendance summary and the totals printed at the bottom of the sheet.
 */
class ProductionReport extends Model
{
    protected $fillable = [
        'report_date', 'week_no',
        'coordinator_name', 'coordinator_position',
        'prepared_by', 'prepared_date', 'checked_by', 'checked_date',
        'total_workers', 'working_hours', 'notes', 'user_id',
    ];

    protected $casts = [
        'report_date' => 'date',
        'prepared_date' => 'date',
        'checked_date' => 'date',
        'total_workers' => 'integer',
        'working_hours' => 'decimal:2',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(ProductionReportLine::class)->orderBy('position')->orderBy('id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getReportTitleAttribute(): string
    {
        return 'Daily Production Report week' . ($this->week_no ? ' '.$this->week_no : '');
    }

    public function getTotalAmountAttribute(): float
    {
        return round((float) $this->lines->sum('total_amount'), 2);
    }

    public function getTotalImpurityAttribute(): float
    {
        return round((float) $this->lines->sum('impurity'), 2);
    }

    public function getTotalRemainingAttribute(): float
    {
        return round((float) $this->lines->sum('remaining'), 2);
    }

    public function getTotalCleanAttribute(): float
    {
        return round((float) $this->lines->sum(fn ($line) => $line->clean), 2);
    }

    /** Grand-total average yield (clean ÷ total amount). */
    public function getAverageYieldAttribute(): float
    {
        return $this->total_amount > 0 ? round($this->total_clean / $this->total_amount, 4) : 0.0;
    }

    public function getPresentWorkersAttribute(): int
    {
        $employees = $this->lines->pluck('employee_id')->filter()->unique();

        return $employees->isNotEmpty() ? $employees->count() : $this->lines->count();
    }

    public function getAbsentWorkersAttribute(): int
    {
        return max(0, (int) $this->total_workers - $this->present_workers);
    }

    /**
     * Production Totals box: one row per group with its net kg and impurity.
     *
     * @return array<int, array{code: string, label: string, net: float, impurity: float}>
     */
    public function getGroupTotalsAttribute(): array
    {
        return $this->lines
            ->groupBy(fn ($line) => $line->group_code ?: '—')
            ->map(function ($group, $code) {
                $first = $group->first();
                $name = strtolower($first->material_name_en ?: 'output');
                $work = strtolower($first->work_type ?: '');
                $label = $work !== '' ? "{$name} ({$work})" : $name;

                return [
                    'code' => (string) $code,
                    'label' => $label . ($code !== '—' ? ' '.$code : ''),
                    'net' => round((float) $group->sum('total_amount'), 2),
                    'impurity' => round((float) $group->sum('impurity'), 2),
                ];
            })
            ->values()
            ->all();
    }
}
