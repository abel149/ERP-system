@extends('layouts.app')
@section('title', 'Edit daily production report')
@section('content')
<div class="page-header">
    <div>
        <p class="eyebrow">Production · OF-BIP-PD-002</p>
        <h1 class="page-title">Edit daily production report</h1>
        <p class="page-subtitle">{{ $report->report_date->format('l, d M Y') }} · week {{ $report->week_no ?: '—' }}</p>
    </div>
    <a href="{{ route('production-reports.show', $report) }}" class="btn btn-secondary">&larr; Back to report</a>
</div>

@include('production-reports._form', [
    'action' => route('production-reports.update', $report),
    'method' => 'PUT',
    'submitLabel' => 'Update report',
])
@endsection
