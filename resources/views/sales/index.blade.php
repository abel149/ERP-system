@extends('layouts.app')
@section('title','Sales Orders')
@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>Sales Orders</h1>
        <p>{{ $sales->total() }} total transaction(s)</p>
    </div>
    <div class="page-header-actions">
        <a href="{{ route('reports.sales') }}" class="btn">View Report</a>
        <a href="{{ route('sales.create') }}" class="btn btn-primary">+ New Sale</a>
    </div>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="tbl">
            <thead>
                <tr><th>Invoice</th><th>Customer</th><th>Cashier</th>
                    <th class="text-right">Total</th><th class="text-right">Paid</th>
                    <th>Method</th><th>Status</th><th>Date</th><th></th></tr>
            </thead>
            <tbody>
            @forelse($sales as $sale)
            <tr>
                <td><a href="{{ route('sales.show',$sale) }}" class="strong">{{ $sale->invoice_no }}</a></td>
                <td>{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                <td class="muted">{{ $sale->user->name }}</td>
                <td class="text-right strong">${{ number_format($sale->total,2) }}</td>
                <td class="text-right">${{ number_format($sale->paid,2) }}</td>
                <td><span class="badge badge-gray">{{ ucfirst(str_replace('_',' ',$sale->payment_method)) }}</span></td>
                <td>
                    @if($sale->balance <= 0)
                        <span class="badge badge-green">Paid</span>
                    @else
                        <span class="badge badge-amber">Bal ${{ number_format($sale->balance,2) }}</span>
                    @endif
                </td>
                <td class="muted">{{ $sale->created_at->format('M d, Y') }}</td>
                <td class="text-right">
                    <a href="{{ route('sales.show',$sale) }}" class="btn btn-sm">View</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="9" class="muted" style="padding:24px;text-align:center;">No sales yet. <a href="{{ route('sales.create') }}">Create your first sale</a>.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $sales->links() }}</div>
</div>
@endsection
