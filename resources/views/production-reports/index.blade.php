@extends('layouts.app')
@section('title', 'Daily production reports')
@section('content')
@php $fmt = fn ($v) => rtrim(rtrim(number_format((float) $v, 2), '0'), '.'); @endphp

<div class="page-header">
    <div>
        <p class="eyebrow">Production · OF-BIP-PD-002</p>
        <h1 class="page-title">Daily production reports</h1>
        <p class="page-subtitle">Worker output, impurity and yield per group, submitted by the production team.</p>
    </div>
    <div class="action-group">
        <a href="{{ route('productions.index') }}" class="btn btn-secondary">Productions</a>
        @if(auth()->user()->hasRole('admin', 'production'))
        <a href="{{ route('production-reports.create') }}" class="btn btn-primary">+ New daily report</a>
        @endif
    </div>
</div>

<section class="card">
    <div class="card-header">
        <div><h2>Reports</h2><p class="card-subtitle">One report per working day.</p></div>
        <form method="GET" class="filter-form">
            <input class="form-control" type="date" name="from" value="{{ request('from') }}" aria-label="From date">
            <input class="form-control" type="date" name="to" value="{{ request('to') }}" aria-label="To date">
            <input class="form-control" type="search" name="search" value="{{ request('search') }}" placeholder="Week or coordinator…" aria-label="Search">
            <button class="btn btn-secondary" type="submit">Filter</button>
            @if(request()->hasAny(['from', 'to', 'search']))
            <a class="btn btn-ghost" href="{{ route('production-reports.index') }}">Clear</a>
            @endif
        </form>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th><th>Week</th><th>Coordinator</th><th>Present / Total</th>
                    <th class="num">Net kg</th><th class="num">Impurity</th><th class="num">Clean</th>
                    <th class="num">Yield</th><th></th>
                </tr>
            </thead>
            <tbody>
            @forelse($reports as $report)
                <tr>
                    <td>
                        <a class="mono" href="{{ route('production-reports.show', $report) }}">{{ $report->report_date->format('d M Y') }}</a>
                        <div class="muted small">{{ $report->report_date->format('D') }}</div>
                    </td>
                    <td>{{ $report->week_no ?: '—' }}</td>
                    <td>{{ $report->coordinator_name ?: '—' }}</td>
                    <td>
                        {{ $report->present_workers }} / {{ $report->total_workers }}
                        <div class="muted small">{{ $report->absent_workers }} absent</div>
                    </td>
                    <td class="num">{{ $fmt($report->total_amount) }}</td>
                    <td class="num">{{ $fmt($report->total_impurity) }}</td>
                    <td class="num">{{ $fmt($report->total_clean) }}</td>
                    <td class="num">{{ $fmt($report->average_yield) }}</td>
                    <td class="text-right">
                        <a href="{{ route('production-reports.show', $report) }}" class="btn btn-sm">View</a>
                        @if(auth()->user()->hasRole('admin', 'production'))
                        <a href="{{ route('production-reports.edit', $report) }}" class="btn btn-sm">Edit</a>
                        <form method="POST" action="{{ route('production-reports.destroy', $report) }}" style="display:inline" onsubmit="return confirm('Delete this report?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">
                        <div class="empty-state">
                            <p class="empty-title">No reports yet</p>
                            <p>Create the first daily production report for the week.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($reports->hasPages())
    <div class="pagination-wrap">{{ $reports->links() }}</div>
    @endif
</section>
@endsection
