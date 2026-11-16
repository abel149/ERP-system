<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'payroll_period',
        'base_salary',
        'overtime_hours',
        'overtime_pay',
        'bonus',
        'deductions',
        'tax',
        'net_salary',
        'status',
        'pay_date',
        'notes',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'bonus' => 'decimal:2',
        'deductions' => 'decimal:2',
        'tax' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'pay_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function calculateNetSalary()
    {
        $this->net_salary = $this->base_salary + $this->overtime_pay + $this->bonus - $this->deductions - $this->tax;
        $this->save();
    }
}
