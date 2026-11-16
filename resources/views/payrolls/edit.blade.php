@extends('layouts.app')

@section('title', 'Edit Payroll')

@section('content')
<div class="card">
    <form method="POST" action="{{ route('payrolls.update', $payroll) }}">
        @csrf @method('PUT')

        <div class="form-grid">
            <div class="field">
                <label>Employee</label>
                <select name="employee_id" required>
                    <option value="">Select Employee</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" {{ old('employee_id', $payroll->employee_id) == $employee->id ? 'selected' : '' }}>
                            {{ $employee->full_name }} ({{ $employee->employee_code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label>Payroll Period</label>
                <input type="text" name="payroll_period" placeholder="e.g., October 2026" required value="{{ old('payroll_period', $payroll->payroll_period) }}">
            </div>

            <div class="field">
                <label>Base Salary</label>
                <input type="number" name="base_salary" step="0.01" min="0" required value="{{ old('base_salary', $payroll->base_salary) }}">
            </div>

            <div class="field">
                <label>Overtime Hours</label>
                <input type="number" name="overtime_hours" step="0.5" min="0" value="{{ old('overtime_hours', $payroll->overtime_hours) }}">
            </div>

            <div class="field">
                <label>Overtime Pay</label>
                <input type="number" name="overtime_pay" step="0.01" min="0" value="{{ old('overtime_pay', $payroll->overtime_pay) }}">
            </div>

            <div class="field">
                <label>Bonus</label>
                <input type="number" name="bonus" step="0.01" min="0" value="{{ old('bonus', $payroll->bonus) }}">
            </div>

            <div class="field">
                <label>Deductions</label>
                <input type="number" name="deductions" step="0.01" min="0" value="{{ old('deductions', $payroll->deductions) }}">
            </div>

            <div class="field">
                <label>Tax</label>
                <input type="number" name="tax" step="0.01" min="0" value="{{ old('tax', $payroll->tax) }}">
            </div>

            <div class="field">
                <label>Status</label>
                <select name="status" required>
                    <option value="pending" {{ old('status', $payroll->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="paid" {{ old('status', $payroll->status) === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="cancelled" {{ old('status', $payroll->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <div class="field">
                <label>Pay Date</label>
                <input type="date" name="pay_date" value="{{ old('pay_date', $payroll->pay_date ? $payroll->pay_date->format('Y-m-d') : '') }}">
            </div>

            <div class="field full-width">
                <label>Notes</label>
                <textarea name="notes" rows="3">{{ old('notes', $payroll->notes) }}</textarea>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('payrolls.index') }}" class="btn">Cancel</a>
            <button type="submit" class="btn btn-primary">Update Payroll</button>
        </div>
    </form>
</div>
@endsection
