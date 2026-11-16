@extends('layouts.app')
@section('title','Raw Materials')
@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>Raw Materials</h1>
        <p>Materials in stock, ready to be issued to production</p>
    </div>
    <div class="page-header-actions">
        <a href="{{ route('raw-materials.create') }}" class="btn btn-primary">+ New Material</a>
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
        @if(request()->hasAny(['search','category_id']))<a href="{{ route('raw-materials.index') }}" class="btn">Clear</a>@endif
    </form>
</div>

<div class="card">
    <div class="table-wrap">
        <table class="tbl">
            <thead>
                <tr><th>Material</th><th>SKU</th><th>Category</th><th>Supplier</th>
                    <th class="text-right">Stock</th><th class="text-right">Unit price</th><th class="text-right">Total cost</th>
                    <th>Status</th><th></th></tr>
            </thead>
            <tbody>
            @forelse($materials as $material)
            <tr>
                <td class="strong"><a href="{{ route('raw-materials.show',$material) }}">{{ $material->name }}</a></td>
                <td class="muted">{{ $material->sku }}</td>
                <td class="muted">{{ $material->category?->name ?? '—' }}</td>
                <td class="muted">{{ $material->supplier?->name ?? '—' }}</td>
                <td class="text-right">
                    <span class="badge {{ $material->quantity == 0 ? 'badge-red' : ($material->is_low_stock ? 'badge-amber' : 'badge-green') }}">
                        {{ number_format($material->quantity, 2) }} {{ $material->unit }}
                    </span>
                </td>
                <td class="text-right">{{ number_format($material->cost_price, 2) }} <span class="muted">/ {{ $material->unit }}</span></td>
                <td class="text-right strong">{{ number_format($material->total_cost, 2) }}</td>
                <td><span class="badge {{ $material->status === 'active' ? 'badge-green' : 'badge-gray' }}">{{ ucfirst($material->status) }}</span></td>
                <td class="text-right">
                    <a href="{{ route('raw-materials.edit',$material) }}" class="btn btn-sm">Edit</a>
                    <form method="POST" action="{{ route('raw-materials.destroy',$material) }}" style="display:inline"
                          onsubmit="return confirm('Delete this material?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="9" class="muted" style="padding:24px;text-align:center;">No raw materials found.</td></tr>
            @endforelse
            </tbody>
            <tfoot>
                <tr><td colspan="6" class="text-right strong">Total stock value (all materials) · ETB</td>
                    <td class="text-right strong">{{ number_format($stockValue, 2) }}</td><td colspan="2"></td></tr>
            </tfoot>
        </table>
    </div>
    <div class="pagination">{{ $materials->appends(request()->query())->links() }}</div>
</div>
@endsection
