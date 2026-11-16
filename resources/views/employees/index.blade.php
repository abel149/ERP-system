@extends('layouts.app')
@section('title','Employees')
@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>Employees</h1>
        <p>Manage employee records and information</p>
    </div>
    <div class="page-header-actions">
        <a href="{{ route('employees.create') }}" class="btn btn-primary">+ New Employee</a>
    </div>
</div>

<div class="filter-bar mb-2 no-print">
    <form method="GET" class="inline-form">
        <div class="field">
            <input type="text" name="search" placeholder="Name, email or department..." value="{{ request('search') }}" style="width:280px;">
        </div>
        <div class="field">
            <select name="status" style="width:140px;">
                <option value="">All Status</option>
                <option value="active" @selected(request('status')=='active')>Active</option>
                <option value="inactive" @selected(request('status')=='inactive')>Inactive</option>
                <option value="terminated" @selected(request('status')=='terminated')>Terminated</option>
            </select>
        </div>
        <button type="submit" class="btn">Filter</button>
        @if(request()->hasAny(['search','status']))<a href="{{ route('employees.index') }}" class="btn">Clear</a>@endif
    </form>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="tbl">
            <thead>
                <tr><th>Code</th><th>Employee</th><th>Department</th><th>Position</th>
                    <th class="text-right">Base Salary</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
            @forelse($employees as $employee)
            <tr>
                <td class="muted strong">{{ $employee->employee_code }}</td>
                <td>
                    <div class="strong">{{ $employee->full_name }}</div>
                    <div class="muted text-sm">{{ $employee->email }}</div>
                </td>
                <td class="muted">{{ $employee->department }}</td>
                <td class="muted">{{ $employee->position }}</td>
                <td class="text-right">${{ number_format($employee->base_salary,2) }}</td>
                <td>
                    @php $sc = match($employee->status) { 'active'=>'badge-green', 'inactive'=>'badge-amber', default=>'badge-red' }; @endphp
                    <span class="badge {{ $sc }}">{{ ucfirst($employee->status) }}</span>
                </td>
                <td class="text-right">
                    <a href="{{ route('employees.show',$employee) }}" class="btn btn-sm">View</a>
                    <a href="{{ route('employees.edit',$employee) }}" class="btn btn-sm">Edit</a>
                    <form method="POST" action="{{ route('employees.destroy',$employee) }}" style="display:inline"
                          onsubmit="return confirm('Delete this employee?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="muted" style="padding:24px;text-align:center;">No employees found. <a href="{{ route('employees.create') }}">Add first employee</a>.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $employees->appends(request()->query())->links() }}</div>
</div>
@endsection
