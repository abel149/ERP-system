@extends('layouts.app')
@section('title','Purchase Order')
@section('content')

@php
    $statusColors = ['draft'=>'badge-gray','sent'=>'badge-blue','partial'=>'badge-amber','received'=>'badge-green','cancelled'=>'badge-red'];
    $payColors    = ['unpaid'=>'badge-red','partial'=>'badge-amber','paid'=>'badge-green'];
@endphp

<div class="page-header no-print">
    <div class="page-header-left">
        <h1>{{ $purchaseOrder->po_number }}</h1>
        <p>Purchase Order from {{ $purchaseOrder->supplier->name }}</p>
    </div>
    <div class="page-header-actions">
        @if($purchaseOrder->status === 'draft')
            <form method="POST" action="{{ route('purchase-orders.destroy',$purchaseOrder) }}" onsubmit="return confirm('Delete this PO?')" style="display:inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">Delete</button>
            </form>
        @endif
        @if(in_array($purchaseOrder->status,['sent','partial','draft']))
            <form method="POST" action="{{ route('purchase-orders.receive',$purchaseOrder) }}" onsubmit="return confirm('Mark as received and update stock?')" style="display:inline">
                @csrf
                <button type="submit" class="btn btn-success">Mark as Received</button>
            </form>
        @endif
        <button onclick="window.print()" class="btn">Print</button>
        <a href="{{ route('purchase-orders.index') }}" class="btn">Back</a>
    </div>
</div>

<div class="card mb-2">
    <div class="card-body">
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:24px;">
            <div>
                <div class="muted text-sm mb-1">Supplier</div>
                <div class="strong">{{ $purchaseOrder->supplier->name }}</div>
                @if($purchaseOrder->supplier->company)<div class="muted">{{ $purchaseOrder->supplier->company }}</div>@endif
                @if($purchaseOrder->supplier->email)<div class="muted text-sm">{{ $purchaseOrder->supplier->email }}</div>@endif
            </div>
            <div>
                <div class="muted text-sm mb-1">Dates</div>
                <div><span class="muted">Order: </span>{{ $purchaseOrder->order_date->format('M d, Y') }}</div>
                <div><span class="muted">Expected: </span>{{ $purchaseOrder->expected_date?->format('M d, Y') ?? '—' }}</div>
                @if($purchaseOrder->received_date)<div><span class="muted">Received: </span>{{ $purchaseOrder->received_date->format('M d, Y') }}</div>@endif
            </div>
            <div>
                <div class="muted text-sm mb-1">Status</div>
                <div class="mb-1"><span class="badge {{ $statusColors[$purchaseOrder->status] ?? 'badge-gray' }}">{{ ucfirst($purchaseOrder->status) }}</span></div>
                <div><span class="badge {{ $payColors[$purchaseOrder->payment_status] ?? 'badge-gray' }}">Payment: {{ ucfirst($purchaseOrder->payment_status) }}</span></div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-2">
    <div class="card-header"><h3>Order Items</h3></div>
    <div class="table-wrap">
        <table class="tbl">
            <thead>
                <tr><th>#</th><th>Description</th><th>Product</th>
                    <th class="text-right">Qty</th><th class="text-right">Received</th>
                    <th class="text-right">Unit Cost</th><th class="text-right">Total</th></tr>
            </thead>
            <tbody>
            @foreach($purchaseOrder->items as $i => $item)
            <tr>
                <td class="muted">{{ $i+1 }}</td>
                <td class="strong">{{ $item->description }}</td>
                <td class="muted">{{ $item->product?->sku ?? '—' }}</td>
                <td class="text-right">{{ $item->quantity }}</td>
                <td class="text-right">
                    <span class="badge {{ $item->received_qty >= $item->quantity ? 'badge-green' : ($item->received_qty > 0 ? 'badge-amber' : 'badge-gray') }}">
                        {{ $item->received_qty }}
                    </span>
                </td>
                <td class="text-right">${{ number_format($item->unit_cost,2) }}</td>
                <td class="text-right strong">${{ number_format($item->total,2) }}</td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        <div style="text-align:right;max-width:300px;margin-left:auto;">
            <div class="d-flex justify-between mb-1"><span class="muted">Subtotal</span><span>${{ number_format($purchaseOrder->subtotal,2) }}</span></div>
            <div class="d-flex justify-between mb-1"><span class="muted">Tax</span><span>${{ number_format($purchaseOrder->tax,2) }}</span></div>
            <div class="d-flex justify-between mb-1"><span class="muted">Shipping</span><span>${{ number_format($purchaseOrder->shipping,2) }}</span></div>
            <hr class="divider">
            <div class="d-flex justify-between"><span class="strong" style="font-size:16px;">Total</span><span class="strong" style="font-size:16px;">${{ number_format($purchaseOrder->total,2) }}</span></div>
        </div>
    </div>
</div>

@if($purchaseOrder->notes)
<div class="card card-pad"><h3 class="mb-1">Notes</h3><p class="muted">{{ $purchaseOrder->notes }}</p></div>
@endif
@endsection
