@extends('layouts.app')
@section('title','New Employee')
@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>New Employee</h1>
        <p>Add a new staff member to the system</p>
    </div>
    <div class="page-header-actions">
        <a href="{{ route('employees.index') }}" class="btn">Cancel</a>
    </div>
</div>

<form method="POST" action="{{ route('employees.store') }}">
@csrf
<div class="form-section mb-2">
    <div class="form-section-header"><h3>Personal Information</h3></div>
    <div class="form-section-body">
        <div class="form-grid">
            <div class="field">
                <label class="required">Employee Code</label>
                <input type="text" name="employee_code" value="{{ old('employee_code') }}" required placeholder="e.g. EMP-007">
                @error('employee_code')<div class="error">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label class="required">First Name</label>
                <input type="text" name="first_name" value="{{ old('first_name') }}" required>
                @error('first_name')<div class="error">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label class="required">Last Name</label>
                <input type="text" name="last_name" value="{{ old('last_name') }}" required>
                @error('last_name')<div class="error">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label class="required">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required>
                @error('email')<div class="error">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label>Phone</label>
                <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+1 234 567 8900">
            </div>
            <div class="field full-width">
                <label>Address</label>
                <textarea name="address" rows="2">{{ old('address') }}</textarea>
            </div>
        </div>
    </div>
</div>

<div class="form-section">
    <div class="form-section-header"><h3>Employment Details</h3></div>
    <div class="form-section-body">
        <div class="form-grid">
            <div class="field">
                <label class="required">Department</label>
                <input type="text" name="department" value="{{ old('department') }}" required placeholder="e.g. Engineering">
            </div>
            <div class="field">
                <label class="required">Position / Job Title</label>
                <input type="text" name="position" value="{{ old('position') }}" required placeholder="e.g. Senior Developer">
            </div>
            <div class="field">
                <label class="required">Base Salary ($)</label>
                <input type="number" name="base_salary" step="0.01" min="0" value="{{ old('base_salary') }}" required>
                @error('base_salary')<div class="error">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label class="required">Hire Date</label>
                <input type="date" name="hire_date" value="{{ old('hire_date', now()->toDateString()) }}" required>
            </div>
            <div class="field">
                <label class="required">Status</label>
                <select name="status" required>
                    <option value="active"     @selected(old('status','active')=='active')>Active</option>
                    <option value="inactive"   @selected(old('status')=='inactive')>Inactive</option>
                    <option value="terminated" @selected(old('status')=='terminated')>Terminated</option>
                </select>
            </div>
        </div>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Create Employee</button>
        <a href="{{ route('employees.index') }}" class="btn">Cancel</a>
    </div>
</div>
</form>
@endsection
