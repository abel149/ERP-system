@extends('layouts.app')
@section('title','New Product')
@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>New Product</h1>
        <p>Add a new inventory item</p>
    </div>
    <div class="page-header-actions">
        <a href="{{ route('products.index') }}" class="btn">Cancel</a>
    </div>
</div>

<form method="POST" action="{{ route('products.store') }}">
@csrf
<div class="form-section">
    <div class="form-section-header"><h3>Product Information</h3></div>
    <div class="form-section-body">
        <div class="form-grid">
            <div class="field">
                <label class="required">Product Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Wireless Mouse">
                @error('name')<div class="error">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label class="required">SKU</label>
                <input type="text" name="sku" value="{{ old('sku') }}" required placeholder="e.g. WM-001">
                @error('sku')<div class="error">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label>Category</label>
                <select name="category_id">
                    <option value="">— None —</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label>Low Stock Alert At</label>
                <input type="number" name="low_stock_threshold" min="0" value="{{ old('low_stock_threshold', 5) }}">
            </div>
            <div class="field">
                <label class="required">Cost Price ($)</label>
                <input type="number" name="cost_price" step="0.01" min="0" value="{{ old('cost_price','0.00') }}" required>
                @error('cost_price')<div class="error">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label class="required">Selling Price ($)</label>
                <input type="number" name="sell_price" step="0.01" min="0" value="{{ old('sell_price','0.00') }}" required>
                @error('sell_price')<div class="error">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label class="required">Opening Stock Qty</label>
                <input type="number" name="quantity" min="0" step="0.01" value="{{ old('quantity', 0) }}" required>
                <div class="hint">Recorded as an initial stock-in movement.</div>
            </div>
            <div class="field" style="display:flex;align-items:center;gap:8px;padding-top:22px;">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" id="is_active" style="width:auto;accent-color:var(--accent);" @checked(old('is_active', true))>
                <label for="is_active" style="font-weight:400;margin:0;">Active (can be sold)</label>
            </div>
            <div class="field full-width">
                <label>Description</label>
                <textarea name="description" rows="2" placeholder="Optional product description...">{{ old('description') }}</textarea>
            </div>
        </div>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Create Product</button>
        <a href="{{ route('products.index') }}" class="btn">Cancel</a>
    </div>
</div>
</form>
@endsection
