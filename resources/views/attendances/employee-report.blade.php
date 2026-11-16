@extends('layouts.app')

@section('title', 'Attendance Report - ' . $employee->full_name)

@section('content')
<div class="card">
    <h2>{{ $employee->full_name }} - Attendance Report</h2>
    <p class="muted">Employee Code: {{ $employee->employee_code }} | Department: {{ $employee->department }}</p>
</div>

<div class="card mt">
    <table class="data">
        <thead>
            <tr><th>Date</th><th>Check In</th><th>Check Out</th><th>Hours</th><th>Status</th><th>Notes</th></tr>
        </thead>
        <tbody>
            @forelse ($attendances as $attendance)
                <tr>
                    <td>{{ $attendance->date->format('M j, Y') }}</td>
                    <td>{{ $attendance->check_in ? $attendance->check_in->format('H:i') : '—' }}</td>
                    <td>{{ $attendance->check_out ? $attendance->check_out->format('H:i') : '—' }}</td>
                    <td>{{ $attendance->work_hours }}</td>
                    <td>
                        <span class="badge {{ $attendance->status === 'present' ? 'badge-green' : ($attendance->status === 'absent' ? 'badge-red' : 'badge-amber') }}">
                            {{ ucfirst($attendance->status) }}
                        </span>
                    </td>
                    <td class="muted">{{ $attendance->notes ?: '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="muted">No attendance records found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination mt">{{ $attendances->links() }}</div>
</div>

<div class="card mt">
    <div class="form-actions">
        <a href="{{ route('employees.show', $employee) }}" class="btn">Back to Employee</a>
        <a href="{{ route('attendances.index') }}" class="btn">All Attendance</a>
    </div>
</div>
@endsection
