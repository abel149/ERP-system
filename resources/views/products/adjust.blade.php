@extends('layouts.app')

@section('title', 'Adjust Stock — ' . $product->name)

@section('content')
<div class="two-col">
    <div class="card">
        <h2>{{ $product->name }} <span class="muted">({{ $product->sku }})</span></h2>
        <p class="muted" style="margin-bottom:18px">
            Current stock: <strong>{{ $product->quantity_display }}</strong>
        </p>

        <form method="POST" action="{{ route('products.adjust.store', $product) }}" id="adjust-form">
            @csrf
            <div class="field">
                <label for="type">Adjustment type</label>
                <select id="type" name="type">
                    <option value="in" @selected(old('type') === 'in')>Stock in (restock / purchase)</option>
                    <option value="out" @selected(old('type') === 'out')>Stock out (damage / loss)</option>
                    <option value="adjustment" @selected(old('type') === 'adjustment')>Physical count (set exact level)</option>
                </select>
            </div>
            <div class="field">
                <label for="quantity" id="qty-label">Quantity</label>
                <input type="number" id="quantity" name="quantity" min="0.01" step="0.01" value="{{ old('quantity', 1) }}" required>
                @error('quantity')<div class="error">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label for="note">Note</label>
                <input type="text" id="note" name="note" value="{{ old('note') }}" placeholder="e.g. Supplier delivery #123">
            </div>
            <button type="submit" class="btn btn-primary">Apply Adjustment</button>
            <a class="btn" href="{{ route('products.index') }}">Back</a>
        </form>
    </div>

    <div class="card">
        <h2>Recent Movements</h2>
        <table class="data">
            <thead>
                <tr><th>Type</th><th class="text-right">Qty ({{ $product->stock_unit }})</th><th class="text-right">Unit cost</th><th class="text-right">After</th><th>Ref / Note</th><th>Date</th></tr>
            </thead>
            <tbody>
                @forelse ($movements as $m)
                    <tr>
                        <td>
                            <span class="badge {{ $m->quantity > 0 ? 'badge-green' : 'badge-red' }}">{{ $m->type }}</span>
                        </td>
                        <td class="text-right">{{ $m->quantity > 0 ? '+' : '' }}{{ rtrim(rtrim(number_format((float) $m->quantity, 2), '0'), '.') }}</td>
                        <td class="text-right">{{ $m->unit_cost !== null ? number_format((float) $m->unit_cost, 2) : '—' }}</td>
                        <td class="text-right">{{ rtrim(rtrim(number_format((float) $m->quantity_after, 2), '0'), '.') }}</td>
                        <td class="muted">{{ $m->reference ?? '' }} {{ $m->note ?? '' }}</td>
                        <td class="muted">{{ $m->created_at->format('M d, H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="muted">No movements recorded.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    const typeSel = document.getElementById('type');
    const qtyLabel = document.getElementById('qty-label');
    typeSel.addEventListener('change', () => {
        qtyLabel.textContent = typeSel.value === 'adjustment'
            ? 'Counted quantity (new stock level)'
            : 'Quantity';
    });
</script>
@endsection
