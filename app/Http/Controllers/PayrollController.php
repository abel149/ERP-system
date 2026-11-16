<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Payroll;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function index()
    {
        $payrolls = Payroll::with('employee')->latest()->paginate(15);

        return view('payrolls.index', compact('payrolls'));
    }

    public function create()
    {
        $employees = Employee::where('status', 'active')->get();

        return view('payrolls.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'payroll_period' => ['required', 'string', 'max:255'],
            'base_salary' => ['required', 'numeric', 'min:0'],
            'overtime_hours' => ['nullable', 'numeric', 'min:0'],
            'overtime_pay' => ['nullable', 'numeric', 'min:0'],
            'bonus' => ['nullable', 'numeric', 'min:0'],
            'deductions' => ['nullable', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:pending,paid,cancelled'],
            'pay_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['overtime_hours'] = $data['overtime_hours'] ?? 0;
        $data['overtime_pay'] = $data['overtime_pay'] ?? 0;
        $data['bonus'] = $data['bonus'] ?? 0;
        $data['deductions'] = $data['deductions'] ?? 0;
        $data['tax'] = $data['tax'] ?? 0;

        $data['net_salary'] = $data['base_salary'] + $data['overtime_pay'] + $data['bonus'] - $data['deductions'] - $data['tax'];

        Payroll::create($data);

        return redirect()->route('payrolls.index')->with('success', 'Payroll created successfully.');
    }

    public function show(Payroll $payroll)
    {
        $payroll->load('employee');

        return view('payrolls.show', compact('payroll'));
    }

    public function edit(Payroll $payroll)
    {
        $payroll->load('employee');
        $employees = Employee::where('status', 'active')->get();

        return view('payrolls.edit', compact('payroll', 'employees'));
    }

    public function update(Request $request, Payroll $payroll)
    {
        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'payroll_period' => ['required', 'string', 'max:255'],
            'base_salary' => ['required', 'numeric', 'min:0'],
            'overtime_hours' => ['nullable', 'numeric', 'min:0'],
            'overtime_pay' => ['nullable', 'numeric', 'min:0'],
            'bonus' => ['nullable', 'numeric', 'min:0'],
            'deductions' => ['nullable', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:pending,paid,cancelled'],
            'pay_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['overtime_hours'] = $data['overtime_hours'] ?? 0;
        $data['overtime_pay'] = $data['overtime_pay'] ?? 0;
        $data['bonus'] = $data['bonus'] ?? 0;
        $data['deductions'] = $data['deductions'] ?? 0;
        $data['tax'] = $data['tax'] ?? 0;

        $data['net_salary'] = $data['base_salary'] + $data['overtime_pay'] + $data['bonus'] - $data['deductions'] - $data['tax'];

        $payroll->update($data);

        return redirect()->route('payrolls.index')->with('success', 'Payroll updated successfully.');
    }

    public function markAsPaid(Payroll $payroll)
    {
        $payroll->update([
            'status' => 'paid',
            'pay_date' => now()->toDateString(),
        ]);

        return back()->with('success', 'Payroll marked as paid.');
    }

    public function destroy(Payroll $payroll)
    {
        $payroll->delete();

        return back()->with('success', 'Payroll deleted successfully.');
    }

    public function generate(Request $request)
    {
        $request->validate([
            'payroll_period' => ['required', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date'],
        ]);

        $employees = Employee::where('status', 'active')->get();

        foreach ($employees as $employee) {
            $existingPayroll = Payroll::where('employee_id', $employee->id)
                ->where('payroll_period', $request->payroll_period)
                ->first();

            if ($existingPayroll) {
                continue;
            }

            $totalHours = $employee->attendances()
                ->whereBetween('date', [$request->start_date, $request->end_date])
                ->whereNotNull('check_in')
                ->whereNotNull('check_out')
                ->get()
                ->sum(function ($attendance) {
                    return $attendance->work_hours;
                });

            $standardHours = 160;
            $overtimeHours = max(0, $totalHours - $standardHours);
            $overtimeRate = $employee->base_salary / $standardHours * 1.5;
            $overtimePay = $overtimeHours * $overtimeRate;

            $tax = $employee->base_salary * 0.1;

            Payroll::create([
                'employee_id' => $employee->id,
                'payroll_period' => $request->payroll_period,
                'base_salary' => $employee->base_salary,
                'overtime_hours' => $overtimeHours,
                'overtime_pay' => $overtimePay,
                'bonus' => 0,
                'deductions' => 0,
                'tax' => $tax,
                'net_salary' => $employee->base_salary + $overtimePay - $tax,
                'status' => 'pending',
            ]);
        }

        return redirect()->route('payrolls.index')->with('success', 'Payroll generated for all active employees.');
    }
}
