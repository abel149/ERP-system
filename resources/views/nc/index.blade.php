@extends('layouts.app')
@section('title', 'NC')
@section('content')
<div class="page-header">
    <div>
        <p class="eyebrow">Production · Non-conforming material</p>
        <h1 class="page-title">NC &amp; rework</h1>
        <p class="page-subtitle">Impurity waiting for rework, and final NC that has gone to NC stock for sale.</p>
    </div>
</div>

<div class="stack">
    <section class="card">
        <div class="card-header"><div><h2>Waiting for rework <span class="badge badge-warning">{{ $inRework->count() }}</span></h2><p class="card-subtitle">Clean output is already in FG. Submit the rework results when done.</p></div></div>
        @if($inRework->isEmpty())
            <div class="empty-state"><p class="empty-title">No NC waiting for rework</p></div>
        @else
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Batch</th><th>Issue</th><th>Finished good</th><th class="num">NC to rework</th><th class="num">Cost carried</th><th></th></tr></thead>
                <tbody>
                @foreach($inRework as $batch)
                <tr>
                    <td><a class="mono" href="{{ route('productions.show', $batch) }}">{{ $batch->production_number }}</a></td>
                    <td class="mono">{{ $batch->issue_numbers }}</td>
                    <td>{{ $batch->product?->name }}</td>
                    <td class="num"><strong>{{ number_format($batch->non_conforming, 2) }} kg</strong></td>
                    <td class="num">ETB {{ number_format($batch->nc_value, 2) }}</td>
                    <td class="num">
                        @if(auth()->user()->hasRole('admin','production'))
                        <a class="btn btn-primary btn-sm" href="{{ route('productions.rework', $batch) }}">Submit rework</a>
                        @endif
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </section>

    <section class="card">
        <div class="card-header"><div><h2>NC sent to NC stock</h2><p class="card-subtitle">Final NC after rework. Sold through sales at the NC price.</p></div></div>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Date</th><th>Batch</th><th>Issue</th><th>NC product</th><th class="num">Quantity</th><th class="num">Cost value</th><th class="num">Sell price / kg</th></tr></thead>
                <tbody>
                @forelse($lots as $lot)
                <tr>
                    <td class="muted">{{ $lot->created_at->format('d M Y') }}</td>
                    <td><a class="mono" href="{{ route('productions.show', $lot->production_id) }}">{{ $lot->production?->production_number }}</a></td>
                    <td class="mono">{{ $lot->production?->issue_numbers }}</td>
                    <td>{{ $lot->product?->name ?? '—' }}</td>
                    <td class="num"><strong>{{ number_format($lot->quantity, 2) }} {{ $lot->unit }}</strong></td>
                    <td class="num">{{ number_format($lot->cost_value, 2) }}</td>
                    <td class="num">{{ $lot->product ? number_format($lot->product->sell_price, 2) : '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="7"><div class="empty-state"><p class="empty-title">No NC in stock yet</p><p>Final NC appears here after a rework is posted.</p></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($lots->hasPages())<div class="pagination-wrap">{{ $lots->links() }}</div>@endif
    </section>
</div>
@endsection
