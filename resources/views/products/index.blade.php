@extends('layouts.app')
@section('title','Products')
@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>Products</h1>
        <p>Manage inventory items and stock levels</p>
    </div>
    <div class="page-header-actions">
        <a href="{{ route('products.create') }}" class="btn btn-primary">+ New Product</a>
    </div>
</div>

<div class="filter-bar mb-2">
    <form method="GET" class="inline-form">
        <div class="field">
            <input type="text" name="search" placeholder="Name or SKU..." value="{{ request('search') }}" style="width:240px;">
        </div>
        <div class="field">
            <select name="category_id" style="width:160px;">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(request('category_id')==$cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn">Filter</button>
        @if(request()->hasAny(['search','category_id']))<a href="{{ route('products.index') }}" class="btn">Clear</a>@endif
    </form>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="tbl">
            <thead>
                <tr><th>Product</th><th>SKU</th><th>Category</th>
                    <th class="text-right">Cost</th>
                    <th>Stock</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
            @forelse($products as $product)
            @php $margin = $product->cost_price > 0 ? round(($product->sell_price - $product->cost_price) / $product->cost_price * 100, 1) : 0; @endphp
            <tr>
                <td class="strong">{{ $product->name }}</td>
                <td class="muted">{{ $product->sku }}</td>
                <td class="muted">{{ $product->category?->name ?? '—' }}</td>
                <td class="text-right">${{ number_format($product->cost_price,2) }}</td>
               <td>
                    <span class="badge {{ $product->quantity == 0 ? 'badge-red' : ($product->is_low_stock ? 'badge-amber' : 'badge-green') }}">
                        {{ $product->quantity_display }}
                    </span>
                </td>
                <td><span class="badge {{ $product->is_active ? 'badge-green' : 'badge-gray' }}">{{ $product->is_active ? 'Active' : 'Off' }}</span></td>
                <td class="text-right">
                    <a href="{{ route('products.adjust',$product) }}" class="btn btn-sm">Stock</a>
                    <a href="{{ route('products.edit',$product) }}" class="btn btn-sm">Edit</a>
                    <form method="POST" action="{{ route('products.destroy',$product) }}" style="display:inline"
                          onsubmit="return confirm('Delete this product?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="9" class="muted" style="padding:24px;text-align:center;">No products found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $products->appends(request()->query())->links() }}</div>
</div>
@endsection
