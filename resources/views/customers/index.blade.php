@extends('layouts.app')
@section('title','Customers')
@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>Customers</h1>
        <p>Manage customer accounts</p>
    </div>
    <div class="page-header-actions">
        <a href="{{ route('customers.create') }}" class="btn btn-primary">+ New Customer</a>
    </div>
</div>

<div class="filter-bar mb-2">
    <form method="GET" class="inline-form">
        <div class="field">
            <input type="text" name="search" placeholder="Name, email, phone or TIN..." value="{{ request('search') }}" style="width:260px;">
        </div>
        <button type="submit" class="btn">Search</button>
        @if(request('search'))<a href="{{ route('customers.index') }}" class="btn">Clear</a>@endif
    </form>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="tbl">
            <thead>
                <tr><th>Name</th><th>TIN</th><th>VAT / VRN</th><th>Email</th><th>Phone</th><th>Sales</th><th></th></tr>
            </thead>
            <tbody>
            @forelse($customers as $c)
            <tr>
                <td class="strong">{{ $c->name }}</td>
                <td class="muted">{{ $c->tin ?: '—' }}</td>
                <td class="muted">{{ $c->vat_number ?: '—' }}</td>
                <td class="muted">{{ $c->email ?: '—' }}</td>
                <td class="muted">{{ $c->phone ?: '—' }}</td>
                <td><span class="badge badge-blue">{{ $c->sales_count }}</span></td>
                <td class="text-right">
                    <a href="{{ route('customers.edit',$c) }}" class="btn btn-sm">Edit</a>
                    <form method="POST" action="{{ route('customers.destroy',$c) }}" style="display:inline"
                          onsubmit="return confirm('Delete this customer?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="muted" style="padding:24px;text-align:center;">No customers. <a href="{{ route('customers.create') }}">Add one</a>.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $customers->appends(request()->query())->links() }}</div>
</div>
@endsection
