@extends('layouts.app')

@section('title', 'Edit Attendance')

@section('content')
<div class="card">
    <form method="POST" action="{{ route('attendances.update', $attendance) }}">
        @csrf @method('PUT')

        <div class="form-grid">
            <div class="field">
                <label>Employee</label>
                <select name="employee_id" required>
                    <option value="">Select Employee</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" {{ old('employee_id', $attendance->employee_id) == $employee->id ? 'selected' : '' }}>
                            {{ $employee->full_name }} ({{ $employee->employee_code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label>Date</label>
                <input type="date" name="date" required value="{{ old('date', $attendance->date->format('Y-m-d')) }}">
            </div>

            <div class="field">
                <label>Check In Time</label>
                <input type="time" name="check_in" value="{{ old('check_in', $attendance->check_in ? $attendance->check_in->format('H:i') : '') }}">
            </div>

            <div class="field">
                <label>Check Out Time</label>
                <input type="time" name="check_out" value="{{ old('check_out', $attendance->check_out ? $attendance->check_out->format('H:i') : '') }}">
            </div>

            <div class="field">
                <label>Status</label>
                <select name="status" required>
                    <option value="present" {{ old('status', $attendance->status) === 'present' ? 'selected' : '' }}>Present</option>
                    <option value="absent" {{ old('status', $attendance->status) === 'absent' ? 'selected' : '' }}>Absent</option>
                    <option value="late" {{ old('status', $attendance->status) === 'late' ? 'selected' : '' }}>Late</option>
                    <option value="half_day" {{ old('status', $attendance->status) === 'half_day' ? 'selected' : '' }}>Half Day</option>
                </select>
            </div>

            <div class="field full-width">
                <label>Notes</label>
                <textarea name="notes" rows="3">{{ old('notes', $attendance->notes) }}</textarea>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('attendances.index') }}" class="btn">Cancel</a>
            <button type="submit" class="btn btn-primary">Update Attendance</button>
        </div>
    </form>
</div>
@endsection
