@extends('layouts.app')
@section('title','Payroll')
@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>Payroll</h1>
        <p>Employee salary management and payment tracking</p>
    </div>
    <div class="page-header-actions">
        <button type="button" class="btn" onclick="toggleGenerate()">Generate Payroll</button>
        <a href="{{ route('payrolls.create') }}" class="btn btn-primary">+ New Payroll</a>
    </div>
</div>

<div class="form-section mb-2" id="generatePanel" style="display:none;">
    <div class="form-section-header"><h3>Generate Payroll for Period</h3></div>
    <div class="form-section-body">
        <form method="POST" action="{{ route('payrolls.generate') }}">
            @csrf
            <div class="form-grid form-grid-3">
                <div class="field">
                    <label>Payroll Period</label>
                    <input type="text" name="payroll_period" placeholder="e.g., November 2026" required>
                </div>
                <div class="field">
                    <label>Start Date</label>
                    <input type="date" name="start_date" required>
                </div>
                <div class="field">
                    <label>End Date</label>
                    <input type="date" name="end_date" required>
                </div>
            </div>
            <div style="margin-top:12px;display:flex;gap:10px;">
                <button type="submit" class="btn btn-success">Generate</button>
                <button type="button" class="btn" onclick="toggleGenerate()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<div class="filter-bar mb-2">
    <form method="GET" class="inline-form">
        <div class="field">
            <input type="text" name="period" placeholder="Filter by period..." value="{{ request('period') }}" style="width:220px;">
        </div>
        <button type="submit" class="btn">Filter</button>
        @if(request('period'))<a href="{{ route('payrolls.index') }}" class="btn">Clear</a>@endif
    </form>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="tbl">
            <thead>
                <tr><th>Employee</th><th>Period</th><th class="text-right">Base</th>
                    <th class="text-right">Overtime</th><th class="text-right">Deductions</th>
                    <th class="text-right">Net Salary</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
            @forelse($payrolls as $payroll)
            <tr>
                <td class="strong">{{ $payroll->employee->full_name }}</td>
                <td class="muted">{{ $payroll->payroll_period }}</td>
                <td class="text-right">${{ number_format($payroll->base_salary,2) }}</td>
                <td class="text-right">${{ number_format($payroll->overtime_pay,2) }}</td>
                <td class="text-right text-danger">${{ number_format($payroll->deductions + $payroll->tax,2) }}</td>
                <td class="text-right strong">${{ number_format($payroll->net_salary,2) }}</td>
                <td>
                    <span class="badge {{ $payroll->status === 'paid' ? 'badge-green' : ($payroll->status === 'pending' ? 'badge-amber' : 'badge-red') }}">
                        {{ ucfirst($payroll->status) }}
                    </span>
                </td>
                <td class="text-right">
                    <a href="{{ route('payrolls.show',$payroll) }}" class="btn btn-sm">View</a>
                    @if($payroll->status === 'pending')
                        <form method="POST" action="{{ route('payrolls.mark-paid',$payroll) }}" style="display:inline"
                              onsubmit="return confirm('Mark as paid?')">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success">Pay</button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('payrolls.destroy',$payroll) }}" style="display:inline"
                          onsubmit="return confirm('Delete this payroll record?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="muted" style="padding:24px;text-align:center;">No payroll records found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $payrolls->appends(request()->query())->links() }}</div>
</div>

@push('scripts')
<script>
function toggleGenerate() {
    const p = document.getElementById('generatePanel');
    p.style.display = p.style.display === 'none' ? 'block' : 'none';
}
</script>
@endpush
@endsection
