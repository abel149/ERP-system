@extends('layouts.app')
@section('title', 'New daily production report')
@section('content')
<div class="page-header">
    <div>
        <p class="eyebrow">Production · OF-BIP-PD-002</p>
        <h1 class="page-title">New daily production report</h1>
        <p class="page-subtitle">Record each worker's output, impurity and yield for the day.</p>
    </div>
    <a href="{{ route('production-reports.index') }}" class="btn btn-secondary">&larr; All reports</a>
</div>

@include('production-reports._form', [
    'action' => route('production-reports.store'),
    'method' => 'POST',
    'submitLabel' => 'Save report',
])
@endsection
