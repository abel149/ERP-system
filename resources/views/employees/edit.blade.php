@extends('layouts.app')

@section('title', 'Edit Employee')

@section('content')
<div class="card">
    <form method="POST" action="{{ route('employees.update', $employee) }}">
        @csrf @method('PUT')

        <div class="form-grid">
            <div class="field">
                <label>Employee Code</label>
                <input type="text" name="employee_code" required value="{{ old('employee_code', $employee->employee_code) }}">
            </div>

            <div class="field">
                <label>First Name</label>
                <input type="text" name="first_name" required value="{{ old('first_name', $employee->first_name) }}">
            </div>

            <div class="field">
                <label>Last Name</label>
                <input type="text" name="last_name" required value="{{ old('last_name', $employee->last_name) }}">
            </div>

            <div class="field">
                <label>Email</label>
                <input type="email" name="email" required value="{{ old('email', $employee->email) }}">
            </div>

            <div class="field">
                <label>Phone</label>
                <input type="text" name="phone" value="{{ old('phone', $employee->phone) }}">
            </div>

            <div class="field">
                <label>Department</label>
                <input type="text" name="department" required value="{{ old('department', $employee->department) }}">
            </div>

            <div class="field">
                <label>Position</label>
                <input type="text" name="position" required value="{{ old('position', $employee->position) }}">
            </div>

            <div class="field">
                <label>Base Salary</label>
                <input type="number" name="base_salary" step="0.01" min="0" required value="{{ old('base_salary', $employee->base_salary) }}">
            </div>

            <div class="field">
                <label>Hire Date</label>
                <input type="date" name="hire_date" required value="{{ old('hire_date', $employee->hire_date->format('Y-m-d')) }}">
            </div>

            <div class="field">
                <label>Status</label>
                <select name="status" required>
                    <option value="active" {{ old('status', $employee->status) === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status', $employee->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="terminated" {{ old('status', $employee->status) === 'terminated' ? 'selected' : '' }}>Terminated</option>
                </select>
            </div>

            <div class="field full-width">
                <label>Address</label>
                <textarea name="address" rows="3">{{ old('address', $employee->address) }}</textarea>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('employees.index') }}" class="btn">Cancel</a>
            <button type="submit" class="btn btn-primary">Update Employee</button>
        </div>
    </form>
</div>
@endsection
