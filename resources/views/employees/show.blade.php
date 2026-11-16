@extends('layouts.app')

@section('title', 'Employee Details')

@section('content')
<div class="card">
    <div class="form-grid">
        <div class="field">
            <label>Employee Code</label>
            <div>{{ $employee->employee_code }}</div>
        </div>

        <div class="field">
            <label>Full Name</label>
            <div>{{ $employee->full_name }}</div>
        </div>

        <div class="field">
            <label>Email</label>
            <div>{{ $employee->email }}</div>
        </div>

        <div class="field">
            <label>Phone</label>
            <div>{{ $employee->phone ?: '—' }}</div>
        </div>

        <div class="field">
            <label>Department</label>
            <div>{{ $employee->department }}</div>
        </div>

        <div class="field">
            <label>Position</label>
            <div>{{ $employee->position }}</div>
        </div>

        <div class="field">
            <label>Base Salary</label>
            <div>${{ number_format($employee->base_salary, 2) }}</div>
        </div>

        <div class="field">
            <label>Hire Date</label>
            <div>{{ $employee->hire_date->format('F j, Y') }}</div>
        </div>

        <div class="field">
            <label>Status</label>
            <div>
                <span class="badge {{ $employee->status === 'active' ? 'badge-green' : ($employee->status === 'inactive' ? 'badge-amber' : 'badge-red') }}">
                    {{ ucfirst($employee->status) }}
                </span>
            </div>
        </div>

        <div class="field full-width">
            <label>Address</label>
            <div>{{ $employee->address ?: '—' }}</div>
        </div>
    </div>

    <div class="form-actions">
        <a href="{{ route('employees.edit', $employee) }}" class="btn btn-primary">Edit Employee</a>
        <a href="{{ route('employees.index') }}" class="btn">Back to List</a>
    </div>
</div>

<div class="card mt">
    <h3>Attendance Records</h3>
    <table class="data">
        <thead>
            <tr><th>Date</th><th>Check In</th><th>Check Out</th><th>Status</th><th>Notes</th></tr>
        </thead>
        <tbody>
            @forelse ($employee->attendances->take(10) as $attendance)
                <tr>
                    <td>{{ $attendance->date->format('M j, Y') }}</td>
                    <td>{{ $attendance->check_in ? $attendance->check_in->format('H:i') : '—' }}</td>
                    <td>{{ $attendance->check_out ? $attendance->check_out->format('H:i') : '—' }}</td>
                    <td>
                        <span class="badge {{ $attendance->status === 'present' ? 'badge-green' : ($attendance->status === 'absent' ? 'badge-red' : 'badge-amber') }}">
                            {{ ucfirst($attendance->status) }}
                        </span>
                    </td>
                    <td class="muted">{{ $attendance->notes ?: '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted">No attendance records found.</td></tr>
            @endforelse
        </tbody>
    </table>
    <a href="{{ route('employees.attendances', $employee) }}" class="btn btn-sm mt">View All Attendance</a>
</div>

<div class="card mt">
    <h3>Payroll Records</h3>
    <table class="data">
        <thead>
            <tr><th>Period</th><th>Base Salary</th><th>Overtime</th><th>Net Salary</th><th>Status</th></tr>
        </thead>
        <tbody>
            @forelse ($employee->payrolls->take(10) as $payroll)
                <tr>
                    <td>{{ $payroll->payroll_period }}</td>
                    <td>${{ number_format($payroll->base_salary, 2) }}</td>
                    <td>${{ number_format($payroll->overtime_pay, 2) }}</td>
                    <td><strong>${{ number_format($payroll->net_salary, 2) }}</strong></td>
                    <td>
                        <span class="badge {{ $payroll->status === 'paid' ? 'badge-green' : ($payroll->status === 'pending' ? 'badge-amber' : 'badge-red') }}">
                            {{ ucfirst($payroll->status) }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="muted">No payroll records found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
