@extends('layouts.app')
@section('title', $rawMaterial->name)
@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>{{ $rawMaterial->name }}</h1>
        <p>{{ $rawMaterial->sku }} · {{ $rawMaterial->category?->name ?? 'Uncategorized' }}</p>
    </div>
    <div class="page-header-actions">
        <a href="{{ route('issues.create', ['material_id' => $rawMaterial->id]) }}" class="btn btn-primary">Issue to Production</a>
        <a href="{{ route('raw-materials.edit',$rawMaterial) }}" class="btn">Edit</a>
        <a href="{{ route('raw-materials.index') }}" class="btn">Back</a>
    </div>
</div>

<div class="card" style="max-width:680px;margin-bottom:16px;">
    <div class="table-wrap">
        <table class="tbl">
            <tbody>
                <tr><td class="muted">Stock on hand</td>
                    <td><span class="badge {{ $rawMaterial->quantity == 0 ? 'badge-red' : ($rawMaterial->is_low_stock ? 'badge-amber' : 'badge-green') }}">
                        {{ number_format($rawMaterial->quantity, 2) }} {{ $rawMaterial->unit }}</span></td></tr>
                <tr><td class="muted">Low stock alert at</td><td>{{ number_format($rawMaterial->min_stock, 2) }} {{ $rawMaterial->unit }}</td></tr>
                <tr><td class="muted">Unit price</td><td>ETB {{ number_format($rawMaterial->cost_price, 2) }} / {{ $rawMaterial->unit }}</td></tr>
                <tr><td class="muted">Total cost (stock × unit price)</td><td class="strong">ETB {{ number_format($rawMaterial->total_cost, 2) }}</td></tr>
                <tr><td class="muted">Supplier</td><td>{{ $rawMaterial->supplier?->name ?? '—' }}</td></tr>
                <tr><td class="muted">Status</td>
                    <td><span class="badge {{ $rawMaterial->status === 'active' ? 'badge-green' : 'badge-gray' }}">{{ ucfirst($rawMaterial->status) }}</span></td></tr>
                @if($rawMaterial->description)
                <tr><td class="muted">Description</td><td>{{ $rawMaterial->description }}</td></tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="form-section-header"><h3>Issued to Production</h3></div>
    <div class="table-wrap">
        <table class="tbl">
            <thead>
                <tr><th>Issue #</th><th class="text-right">Quantity</th><th class="text-right">Unit price</th><th class="text-right">Total cost</th><th>Status</th><th>Date</th></tr>
            </thead>
            <tbody>
            @forelse($rawMaterial->issueItems as $line)
            <tr>
                <td class="strong"><a href="{{ route('issues.show', $line->issue) }}">{{ $line->issue->issue_number }}</a></td>
                <td class="text-right">{{ number_format($line->quantity, 2) }} {{ $line->unit ?? $rawMaterial->unit }}</td>
                <td class="text-right">{{ number_format($line->unit_cost, 2) }}</td>
                <td class="text-right strong">{{ number_format($line->total, 2) }}</td>
                <td><span class="badge {{ $line->issue->status === 'issued' ? 'badge-amber' : 'badge-green' }}">{{ $line->issue->status === 'issued' ? 'Waiting for production' : 'Consumed' }}</span></td>
                <td class="muted">{{ $line->issue->created_at->format('Y-m-d') }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="muted" style="padding:24px;text-align:center;">No issues recorded for this material yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
