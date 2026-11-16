<div class="form-row">
    <div class="field">
        <label for="name">Name *</label>
        <input type="text" id="name" name="name" value="{{ old('name', $rawMaterial->name ?? '') }}" required>
        @error('name')<div class="error">{{ $message }}</div>@enderror
    </div>
    <div class="field">
        <label for="sku">SKU *</label>
        <input type="text" id="sku" name="sku" value="{{ old('sku', $rawMaterial->sku ?? '') }}" required>
        @error('sku')<div class="error">{{ $message }}</div>@enderror
    </div>
</div>
<div class="form-row">
    <div class="field">
        <label for="category_id">Category</label>
        <select id="category_id" name="category_id">
            <option value="">— None —</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $rawMaterial->category_id ?? '') == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        @error('category_id')<div class="error">{{ $message }}</div>@enderror
    </div>
    <div class="field">
        <label for="supplier_id">Supplier</label>
        <select id="supplier_id" name="supplier_id">
            <option value="">— None —</option>
            @foreach ($suppliers as $supplier)
                <option value="{{ $supplier->id }}" @selected(old('supplier_id', $rawMaterial->supplier_id ?? '') == $supplier->id)>{{ $supplier->name }}</option>
            @endforeach
        </select>
        @error('supplier_id')<div class="error">{{ $message }}</div>@enderror
    </div>
</div>
<div class="form-row">
    <div class="field">
        <label for="unit">Unit *</label>
        <input type="text" id="unit" name="unit" value="{{ old('unit', $rawMaterial->unit ?? 'kg') }}" required placeholder="e.g. kg, L, pcs">
        @error('unit')<div class="error">{{ $message }}</div>@enderror
    </div>
    <div class="field">
        <label for="min_stock">Low stock alert at</label>
        <input type="number" id="min_stock" name="min_stock" step="0.01" min="0" value="{{ old('min_stock', $rawMaterial->min_stock ?? 0) }}" required>
        @error('min_stock')<div class="error">{{ $message }}</div>@enderror
    </div>
</div>
<div class="form-row">
    <div class="field">
        <label for="cost_price">Unit price (per unit) *</label>
        <input type="number" id="cost_price" name="cost_price" step="0.01" min="0" value="{{ old('cost_price', $rawMaterial->cost_price ?? '0.00') }}" required>
        @error('cost_price')<div class="error">{{ $message }}</div>@enderror
    </div>
    <div class="field">
        <label>Total cost (quantity × unit price)</label>
        <input type="text" id="total_cost" disabled
               data-stock="{{ isset($rawMaterial) ? $rawMaterial->quantity : '' }}"
               value="{{ isset($rawMaterial) ? number_format($rawMaterial->total_cost, 2) : '0.00' }}">
        <div class="hint">Calculated automatically.</div>
    </div>
</div>
<div class="form-row">
    @if (!isset($rawMaterial))
        <div class="field">
            <label for="quantity">Opening stock quantity *</label>
            <input type="number" id="quantity" name="quantity" step="0.01" min="0" value="{{ old('quantity', 0) }}" required>
            @error('quantity')<div class="error">{{ $message }}</div>@enderror
        </div>
    @else
        <div class="field">
            <label>Current stock</label>
            <input type="text" value="{{ $rawMaterial->quantity }} {{ $rawMaterial->unit }}" disabled>
            <div class="hint">Stock changes only through material issues.</div>
        </div>
    @endif
</div>
<div class="field">
    <label for="description">Description</label>
    <textarea id="description" name="description" rows="3">{{ old('description', $rawMaterial->description ?? '') }}</textarea>
    @error('description')<div class="error">{{ $message }}</div>@enderror
</div>
<div class="field">
    <label style="font-weight:400">
        <input type="hidden" name="status" value="inactive">
        <input type="checkbox" name="status" value="active" style="width:auto" @checked(old('status', $rawMaterial->status ?? 'active') === 'active')>
        Active
    </label>
</div>
<script>
(() => {
    const price = document.getElementById('cost_price');
    const total = document.getElementById('total_cost');
    const qty = document.getElementById('quantity');
    const update = () => {
        const q = qty ? Number(qty.value) || 0 : Number(total.dataset.stock) || 0;
        total.value = (q * (Number(price.value) || 0)).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    };
    price.addEventListener('input', update);
    qty?.addEventListener('input', update);
    update();
})();
</script>
