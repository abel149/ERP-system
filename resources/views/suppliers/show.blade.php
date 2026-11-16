@extends('layouts.app')
@section('title','Supplier Detail')
@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>{{ $supplier->name }}</h1>
        <p>{{ $supplier->company ?: 'Supplier detail' }}</p>
    </div>
    <div class="page-header-actions">
        <a href="{{ route('suppliers.edit',$supplier) }}" class="btn btn-primary">Edit</a>
        <a href="{{ route('suppliers.index') }}" class="btn">Back</a>
    </div>
</div>

<div class="two-col">
    <div class="card card-pad">
        <h3 style="margin-bottom:16px;">Contact Information</h3>
        <table class="tbl">
            <tr><td class="muted" style="width:140px;">Name</td><td class="strong">{{ $supplier->name }}</td></tr>
            <tr><td class="muted">Company</td><td>{{ $supplier->company ?: '—' }}</td></tr>
            <tr><td class="muted">Email</td><td>{{ $supplier->email ?: '—' }}</td></tr>
            <tr><td class="muted">Phone</td><td>{{ $supplier->phone ?: '—' }}</td></tr>
            <tr><td class="muted">Tax Number</td><td>{{ $supplier->tax_number ?: '—' }}</td></tr>
            <tr><td class="muted">Payment Terms</td><td><span class="badge badge-blue">{{ $supplier->payment_terms }}</span></td></tr>
            <tr><td class="muted">Status</td><td><span class="badge {{ $supplier->status === 'active' ? 'badge-green' : 'badge-gray' }}">{{ ucfirst($supplier->status) }}</span></td></tr>
            <tr><td class="muted">Address</td><td>{{ $supplier->address ?: '—' }}</td></tr>
            @if($supplier->notes)
            <tr><td class="muted">Notes</td><td>{{ $supplier->notes }}</td></tr>
            @endif
        </table>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Purchase Orders</h3>
            <a href="{{ route('purchase-orders.create') }}" class="btn btn-sm btn-primary">New PO</a>
        </div>
        <div class="table-wrap">
            <table class="tbl">
                <thead><tr><th>PO #</th><th>Total</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                @forelse($supplier->purchaseOrders as $po)
                <tr>
                    <td><a href="{{ route('purchase-orders.show',$po) }}" class="strong">{{ $po->po_number }}</a></td>
                    <td>${{ number_format($po->total,2) }}</td>
                    <td><span class="badge badge-blue">{{ ucfirst($po->status) }}</span></td>
                    <td class="muted">{{ $po->order_date->format('M d, Y') }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="muted">No purchase orders yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
