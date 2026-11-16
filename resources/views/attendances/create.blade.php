@extends('layouts.app')

@section('title', 'Record Attendance')

@section('content')
<div class="card">
    <form method="POST" action="{{ route('attendances.store') }}">
        @csrf

        <div class="form-grid">
            <div class="field">
                <label>Employee</label>
                <select name="employee_id" required>
                    <option value="">Select Employee</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                            {{ $employee->full_name }} ({{ $employee->employee_code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label>Date</label>
                <input type="date" name="date" required value="{{ old('date', now()->toDateString()) }}">
            </div>

            <div class="field">
                <label>Check In Time</label>
                <input type="time" name="check_in" value="{{ old('check_in') }}">
            </div>

            <div class="field">
                <label>Check Out Time</label>
                <input type="time" name="check_out" value="{{ old('check_out') }}">
            </div>

            <div class="field">
                <label>Status</label>
                <select name="status" required>
                    <option value="present" {{ old('status') === 'present' ? 'selected' : '' }}>Present</option>
                    <option value="absent" {{ old('status') === 'absent' ? 'selected' : '' }}>Absent</option>
                    <option value="late" {{ old('status') === 'late' ? 'selected' : '' }}>Late</option>
                    <option value="half_day" {{ old('status') === 'half_day' ? 'selected' : '' }}>Half Day</option>
                </select>
            </div>

            <div class="field full-width">
                <label>Notes</label>
                <textarea name="notes" rows="3">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('attendances.index') }}" class="btn">Cancel</a>
            <button type="submit" class="btn btn-primary">Record Attendance</button>
        </div>
    </form>
</div>
@endsection
