@extends('layouts.app')

@section('title', 'Payroll Details')

@section('content')
<div class="card">
    <h2>{{ $payroll->employee->full_name }} - Payroll</h2>
    <p class="muted">Period: {{ $payroll->payroll_period }}</p>
</div>

<div class="card mt">
    <div class="form-grid">
        <div class="field">
            <label>Employee</label>
            <div>{{ $payroll->employee->full_name }} ({{ $payroll->employee->employee_code }})</div>
        </div>

        <div class="field">
            <label>Payroll Period</label>
            <div>{{ $payroll->payroll_period }}</div>
        </div>

        <div class="field">
            <label>Base Salary</label>
            <div>${{ number_format($payroll->base_salary, 2) }}</div>
        </div>

        <div class="field">
            <label>Overtime Hours</label>
            <div>{{ number_format($payroll->overtime_hours, 2) }} hrs</div>
        </div>

        <div class="field">
            <label>Overtime Pay</label>
            <div>${{ number_format($payroll->overtime_pay, 2) }}</div>
        </div>

        <div class="field">
            <label>Bonus</label>
            <div>${{ number_format($payroll->bonus, 2) }}</div>
        </div>

        <div class="field">
            <label>Deductions</label>
            <div class="text-red">${{ number_format($payroll->deductions, 2) }}</div>
        </div>

        <div class="field">
            <label>Tax</label>
            <div class="text-red">${{ number_format($payroll->tax, 2) }}</div>
        </div>

        <div class="field">
            <label>Net Salary</label>
            <div class="text-green"><strong>${{ number_format($payroll->net_salary, 2) }}</strong></div>
        </div>

        <div class="field">
            <label>Status</label>
            <div>
                <span class="badge {{ $payroll->status === 'paid' ? 'badge-green' : ($payroll->status === 'pending' ? 'badge-amber' : 'badge-red') }}">
                    {{ ucfirst($payroll->status) }}
                </span>
            </div>
        </div>

        <div class="field">
            <label>Pay Date</label>
            <div>{{ $payroll->pay_date ? $payroll->pay_date->format('F j, Y') : '—' }}</div>
        </div>

        <div class="field full-width">
            <label>Notes</label>
            <div>{{ $payroll->notes ?: '—' }}</div>
        </div>
    </div>

    <div class="form-actions">
        @if($payroll->status === 'pending')
            <form method="POST" action="{{ route('payrolls.mark-paid', $payroll) }}" style="display:inline"
                  onsubmit="return confirm('Mark this payroll as paid?')">
                @csrf
                <button type="submit" class="btn btn-success">Mark as Paid</button>
            </form>
        @endif
        <a href="{{ route('payrolls.edit', $payroll) }}" class="btn btn-primary">Edit Payroll</a>
        <a href="{{ route('payrolls.index') }}" class="btn">Back to List</a>
    </div>
</div>
@endsection
