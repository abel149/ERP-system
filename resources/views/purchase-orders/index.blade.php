@extends('layouts.app')
@section('title','Purchase Orders')
@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>Purchase Orders</h1>
        <p>Manage procurement and stock replenishment</p>
    </div>
    <div class="page-header-actions">
        <a href="{{ route('purchase-orders.create') }}" class="btn btn-primary">+ New PO</a>
    </div>
</div>

<div class="filter-bar mb-2">
    <form method="GET" class="inline-form">
        <div class="field">
            <select name="status" style="width:160px;">
                <option value="">All Statuses</option>
                @foreach(['draft','sent','partial','received','cancelled'] as $s)
                    <option value="{{ $s }}" @selected(request('status')==$s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn">Filter</button>
        @if(request('status'))<a href="{{ route('purchase-orders.index') }}" class="btn">Clear</a>@endif
    </form>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="tbl">
            <thead>
                <tr><th>PO #</th><th>Supplier</th><th>Order Date</th><th>Expected</th>
                    <th class="text-right">Total</th><th>Status</th><th>Payment</th><th></th></tr>
            </thead>
            <tbody>
            @forelse($orders as $po)
            @php
                $statusColors = ['draft'=>'badge-gray','sent'=>'badge-blue','partial'=>'badge-amber','received'=>'badge-green','cancelled'=>'badge-red'];
                $payColors    = ['unpaid'=>'badge-red','partial'=>'badge-amber','paid'=>'badge-green'];
            @endphp
            <tr>
                <td><a href="{{ route('purchase-orders.show',$po) }}" class="strong">{{ $po->po_number }}</a></td>
                <td>{{ $po->supplier->name }}</td>
                <td class="muted">{{ $po->order_date->format('M d, Y') }}</td>
                <td class="muted">{{ $po->expected_date?->format('M d, Y') ?? '—' }}</td>
                <td class="text-right strong">${{ number_format($po->total,2) }}</td>
                <td><span class="badge {{ $statusColors[$po->status] ?? 'badge-gray' }}">{{ ucfirst($po->status) }}</span></td>
                <td><span class="badge {{ $payColors[$po->payment_status] ?? 'badge-gray' }}">{{ ucfirst($po->payment_status) }}</span></td>
                <td class="text-right">
                    <a href="{{ route('purchase-orders.show',$po) }}" class="btn btn-sm">View</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="muted" style="padding:24px;text-align:center;">No purchase orders found. <a href="{{ route('purchase-orders.create') }}">Create one</a>.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $orders->appends(request()->query())->links() }}</div>
</div>
@endsection
