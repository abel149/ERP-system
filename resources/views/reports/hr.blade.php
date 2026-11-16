@extends('layouts.app')
@section('title','HR Report')
@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>HR Report</h1>
        <p>Attendance and payroll summary</p>
    </div>
    <div class="page-header-actions no-print">
        <a href="{{ route('reports.sales') }}" class="btn">Sales Report</a>
        <a href="{{ route('reports.inventory') }}" class="btn">Inventory Report</a>
        <button onclick="window.print()" class="btn btn-primary">Print</button>
    </div>
</div>

<form method="GET" class="filter-bar mb-2 no-print">
    <div class="field">
        <label>Month</label>
        <input type="month" name="month" value="{{ $month }}">
    </div>
    <div style="margin-top:18px;">
        <button type="submit" class="btn btn-primary">Apply</button>
    </div>
</form>

<div class="kpi-grid" style="grid-template-columns:repeat(4,1fr);">
    <div class="kpi-card">
        <div class="kpi-top"><div class="kpi-icon green">&#10003;</div></div>
        <div class="kpi-label">Present Days</div>
        <div class="kpi-value">{{ $summary['total_present'] }}</div>
        <div class="kpi-sub">{{ $summary['working_days'] }} working days logged</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-top"><div class="kpi-icon red">&#10007;</div></div>
        <div class="kpi-label">Absent</div>
        <div class="kpi-value">{{ $summary['total_absent'] }}</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-top"><div class="kpi-icon amber">&#8987;</div></div>
        <div class="kpi-label">Late Arrivals</div>
        <div class="kpi-value">{{ $summary['total_late'] }}</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-top"><div class="kpi-icon cyan">&#128181;</div></div>
        <div class="kpi-label">Total Payroll</div>
        <div class="kpi-value">${{ number_format($summary['total_payroll'],0) }}</div>
        <div class="kpi-sub">{{ $summary['paid_payrolls'] }} paid, {{ $summary['pending_payrolls'] }} pending</div>
    </div>
</div>

<div class="dash-grid">
    {{-- Attendance per Employee --}}
    <div class="card">
        <div class="card-header"><h3>Attendance Summary</h3></div>
        <div class="table-wrap">
            <table class="tbl">
                <thead>
                    <tr><th>Employee</th><th>Dept</th>
                        <th class="text-right">Present</th><th class="text-right">Absent</th>
                        <th class="text-right">Late</th><th class="text-right">Hours</th></tr>
                </thead>
                <tbody>
                @forelse($empAttendance as $ea)
                <tr>
                    <td class="strong">{{ $ea['employee']->full_name }}</td>
                    <td class="muted">{{ $ea['employee']->department }}</td>
                    <td class="text-right"><span class="badge badge-green">{{ $ea['present'] }}</span></td>
                    <td class="text-right"><span class="badge {{ $ea['absent'] > 0 ? 'badge-red' : 'badge-gray' }}">{{ $ea['absent'] }}</span></td>
                    <td class="text-right"><span class="badge {{ $ea['late'] > 0 ? 'badge-amber' : 'badge-gray' }}">{{ $ea['late'] }}</span></td>
                    <td class="text-right">{{ $ea['hours'] }}h</td>
                </tr>
                @empty
                <tr><td colspan="6" class="muted" style="padding:20px;text-align:center;">No attendance data for this month.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Payroll Summary --}}
    <div class="card">
        <div class="card-header">
            <h3>Payroll Records</h3>
        </div>
        <div class="table-wrap">
            <table class="tbl">
                <thead>
                    <tr><th>Employee</th><th class="text-right">Base</th>
                        <th class="text-right">Net</th><th>Status</th></tr>
                </thead>
                <tbody>
                @forelse($payrolls as $pr)
                <tr>
                    <td class="strong">{{ $pr->employee?->full_name ?? '—' }}</td>
                    <td class="text-right">${{ number_format($pr->base_salary,2) }}</td>
                    <td class="text-right strong">${{ number_format($pr->net_salary,2) }}</td>
                    <td><span class="badge {{ $pr->status === 'paid' ? 'badge-green' : 'badge-amber' }}">{{ ucfirst($pr->status) }}</span></td>
                </tr>
                @empty
                <tr><td colspan="4" class="muted" style="padding:20px;text-align:center;">No payroll records for this month.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
