@extends('layouts.app')
@section('title','Suppliers')
@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>Suppliers</h1>
        <p>Manage vendor and supplier information</p>
    </div>
    <div class="page-header-actions">
        <a href="{{ route('suppliers.create') }}" class="btn btn-primary">+ New Supplier</a>
    </div>
</div>

<div class="filter-bar mb-2">
    <form method="GET" class="inline-form">
        <div class="field">
            <input type="text" name="search" placeholder="Search name or email..." value="{{ request('search') }}" style="width:260px;">
        </div>
        <button type="submit" class="btn">Search</button>
        @if(request('search'))<a href="{{ route('suppliers.index') }}" class="btn">Clear</a>@endif
    </form>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="tbl">
            <thead>
                <tr>
                    <th>Supplier</th><th>Company</th><th>Contact</th>
                    <th>Payment Terms</th><th>Orders</th><th>Status</th><th></th>
                </tr>
            </thead>
            <tbody>
            @forelse($suppliers as $s)
            <tr>
                <td>
                    <div class="strong">{{ $s->name }}</div>
                    <div class="muted text-sm">{{ $s->email }}</div>
                </td>
                <td class="muted">{{ $s->company ?: '—' }}</td>
                <td class="muted">{{ $s->phone ?: '—' }}</td>
                <td><span class="badge badge-blue">{{ $s->payment_terms }}</span></td>
                <td>{{ $s->purchase_orders_count }}</td>
                <td>
                    <span class="badge {{ $s->status === 'active' ? 'badge-green' : 'badge-gray' }}">{{ ucfirst($s->status) }}</span>
                </td>
                <td class="text-right">
                    <a href="{{ route('suppliers.show',$s) }}" class="btn btn-sm">View</a>
                    <a href="{{ route('suppliers.edit',$s) }}" class="btn btn-sm">Edit</a>
                    <form method="POST" action="{{ route('suppliers.destroy',$s) }}" style="display:inline"
                          onsubmit="return confirm('Delete this supplier?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="muted" style="padding:24px;text-align:center;">No suppliers found. <a href="{{ route('suppliers.create') }}">Add your first supplier</a>.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $suppliers->appends(request()->query())->links() }}</div>
</div>

@endsection
