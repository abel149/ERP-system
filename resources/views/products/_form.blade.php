<div class="form-row">
    <div class="field">
        <label for="name">Name *</label>
        <input type="text" id="name" name="name" value="{{ old('name', $product->name ?? '') }}" required>
        @error('name')<div class="error">{{ $message }}</div>@enderror
    </div>
    <div class="field">
        <label for="sku">SKU *</label>
        <input type="text" id="sku" name="sku" value="{{ old('sku', $product->sku ?? '') }}" required>
        @error('sku')<div class="error">{{ $message }}</div>@enderror
    </div>
</div>
<div class="form-row">
    <div class="field">
        <label for="category_id">Category</label>
        <select id="category_id" name="category_id">
            <option value="">— None —</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id ?? '') == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        @error('category_id')<div class="error">{{ $message }}</div>@enderror
    </div>
    <div class="field">
        <label for="low_stock_threshold">Low stock alert at</label>
        <input type="number" id="low_stock_threshold" name="low_stock_threshold" min="0" value="{{ old('low_stock_threshold', $product->low_stock_threshold ?? 5) }}" required>
        @error('low_stock_threshold')<div class="error">{{ $message }}</div>@enderror
    </div>
</div>
<div class="form-row">
    <div class="field">
        <label for="cost_price">Cost price *</label>
        <input type="number" id="cost_price" name="cost_price" step="0.01" min="0" value="{{ old('cost_price', $product->cost_price ?? '0.00') }}" required>
        @error('cost_price')<div class="error">{{ $message }}</div>@enderror
    </div>
    <div class="field">
        <label for="sell_price">Sell price *</label>
        <input type="number" id="sell_price" name="sell_price" step="0.01" min="0" value="{{ old('sell_price', $product->sell_price ?? '0.00') }}" required>
        @error('sell_price')<div class="error">{{ $message }}</div>@enderror
    </div>
</div>
@if (!isset($product))
    <div class="field">
        <label for="quantity">Opening stock quantity *</label>
        <input type="number" id="quantity" name="quantity" min="0" value="{{ old('quantity', 0) }}" required>
        <div class="hint">Recorded as an "in" stock movement.</div>
        @error('quantity')<div class="error">{{ $message }}</div>@enderror
    </div>
@else
    <div class="field">
        <label>Current stock</label>
        <input type="text" value="{{ $product->quantity_display }}" disabled>
        <div class="hint">Use the <a href="{{ route('products.adjust', $product) }}">stock adjustment screen</a> to change quantity.</div>
    </div>
@endif
<div class="field">
    <label for="description">Description</label>
    <textarea id="description" name="description" rows="3">{{ old('description', $product->description ?? '') }}</textarea>
    @error('description')<div class="error">{{ $message }}</div>@enderror
</div>
<div class="field">
    <label style="font-weight:400">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" style="width:auto" @checked(old('is_active', $product->is_active ?? true))>
        Active (can be sold)
    </label>
</div>
