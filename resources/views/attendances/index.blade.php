@extends('layouts.app')
@section('title','Attendance')
@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>Attendance</h1>
        <p>Track employee check-ins and working hours</p>
    </div>
    <div class="page-header-actions">
        <a href="{{ route('attendances.create') }}" class="btn btn-primary">+ Record Attendance</a>
    </div>
</div>

<div class="form-section mb-2">
    <div class="form-section-header"><h3>Quick Check-In / Check-Out</h3></div>
    <div class="form-section-body">
        @php $activeEmployees = \App\Models\Employee::where('status','active')->orderBy('full_name')->get(); @endphp
        <div class="two-col">
            <form method="POST" action="{{ route('attendances.check-in') }}" class="d-flex" style="gap:10px;align-items:flex-end;">
                @csrf
                <div class="field flex-1">
                    <label>Employee</label>
                    <select name="employee_id" required>
                        <option value="">Select Employee...</option>
                        @foreach($activeEmployees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->employee_code }})</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-success">Check In</button>
            </form>
            <form method="POST" action="{{ route('attendances.check-out') }}" class="d-flex" style="gap:10px;align-items:flex-end;">
                @csrf
                <div class="field flex-1">
                    <label>Employee</label>
                    <select name="employee_id" required>
                        <option value="">Select Employee...</option>
                        @foreach($activeEmployees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }} ({{ $emp->employee_code }})</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-warning">Check Out</button>
            </form>
        </div>
    </div>
</div>

<div class="filter-bar mb-2">
    <form method="GET" class="inline-form">
        <div class="field">
            <input type="date" name="date" value="{{ request('date', now()->toDateString()) }}">
        </div>
        <button type="submit" class="btn">Filter</button>
        <a href="{{ route('attendances.index') }}" class="btn">Today</a>
    </form>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="tbl">
            <thead>
                <tr><th>Employee</th><th>Date</th><th>Check In</th><th>Check Out</th>
                    <th class="text-right">Hours</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
            @forelse($attendances as $att)
            <tr>
                <td class="strong">{{ $att->employee->full_name }}</td>
                <td class="muted">{{ $att->date->format('M j, Y') }}</td>
                <td>{{ $att->check_in ? \Carbon\Carbon::parse($att->check_in)->format('H:i') : '—' }}</td>
                <td>{{ $att->check_out ? \Carbon\Carbon::parse($att->check_out)->format('H:i') : '—' }}</td>
                <td class="text-right">{{ $att->work_hours ?? '—' }}</td>
                <td>
                    @php $sc = match($att->status) { 'present'=>'badge-green','absent'=>'badge-red','late'=>'badge-amber',default=>'badge-gray' }; @endphp
                    <span class="badge {{ $sc }}">{{ ucfirst($att->status) }}</span>
                </td>
                <td class="text-right">
                    <a href="{{ route('attendances.edit',$att) }}" class="btn btn-sm">Edit</a>
                    <form method="POST" action="{{ route('attendances.destroy',$att) }}" style="display:inline"
                          onsubmit="return confirm('Delete this record?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="muted" style="padding:24px;text-align:center;">No attendance records found for this date.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $attendances->appends(request()->query())->links() }}</div>
</div>
@endsection
