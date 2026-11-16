@extends('layouts.app')
@section('title', 'Submit production results')
@section('content')
@php $selected = array_map('intval', old('issue_ids', $preselected)); @endphp

<div class="page-header">
    <div>
        <p class="eyebrow">Production · Step 1 of 3</p>
        <h1 class="page-title">Submit purity &amp; impurity</h1>
        <p class="page-subtitle">Pick the issue you processed and enter what you weighed: the clean (pure) material and the NC (impurity).</p>
    </div>
    <a href="{{ route('productions.index') }}" class="btn btn-secondary">&larr; Production</a>
</div>

@include('productions.partials.steps', ['current' => 1])

@if($available->isEmpty())
<section class="card">
    <div class="empty-state">
        <p class="empty-title">Nothing waiting to be processed</p>
        <p>When the store issues materials they appear here.</p>
        <a class="btn btn-secondary" style="margin-top:18px" href="{{ route('issues.index') }}">View material issues</a>
    </div>
</section>
@else
<form method="POST" action="{{ route('productions.store') }}" id="resultsForm">
@csrf
<div class="work-layout">
    <div class="stack">

        {{-- 1. Issue --}}
        <section class="card">
            <div class="card-header"><div class="section-title"><span class="section-number">1</span><div><h2>Issued materials</h2><p class="card-subtitle">Ingredients and costs exactly as the store issued them.</p></div></div></div>
            <div class="card-body">
                @foreach($available as $issue)
                @php
                    $weight = $issue->items->sum(fn ($l) => $l->weight_kg ?? 0);
                    $supported = $issue->items->isNotEmpty() && ! $issue->items->contains(fn ($l) => $l->weight_kg === null);
                @endphp
                <label class="selection">
                    <span class="selection-head">
                        <input type="checkbox" name="issue_ids[]" value="{{ $issue->id }}"
                               data-weight="{{ $weight }}" data-cost="{{ $issue->total_value }}" data-product="{{ $issue->intended_product_id }}"
                               @checked(in_array($issue->id, $selected)) @disabled(! $supported)>
                        <strong class="mono">{{ $issue->issue_number }}</strong>
                        @if($issue->intendedProduct)<span class="badge badge-info">For {{ $issue->intendedProduct->name }}</span>@endif
                        <span class="selection-meta">{{ $issue->created_at->format('d M Y') }}</span>
                    </span>
                    <span class="table-wrap" style="display:block">
                    <table class="table mini-table">
                        <thead><tr><th>Ingredient</th><th class="num">Qty</th><th class="num">Unit price</th><th class="num">Total cost</th></tr></thead>
                        <tbody>
                        @foreach($issue->items as $item)
                            <tr>
                                <td>{{ $item->rawMaterial?->name }}</td>
                                <td class="num">{{ number_format($item->quantity, 2) }} {{ $item->unit }}</td>
                                <td class="num">{{ number_format($item->unit_cost, 2) }}</td>
                                <td class="num">{{ number_format($item->total, 2) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    </span>
                    <span class="selection-footer">
                        @if($supported)
                            <strong>{{ number_format($weight, 2) }} kg</strong> input · Material cost <strong>ETB {{ number_format($issue->total_value, 2) }}</strong>
                        @else
                            This issue has materials not measured in kg/g and cannot be processed by weight.
                        @endif
                    </span>
                </label>
                @endforeach
                @error('issue_ids')<p class="form-error">{{ $message }}</p>@enderror
            </div>
        </section>

        {{-- 2. Measured results --}}
        <section class="card">
            <div class="card-header"><div class="section-title"><span class="section-number">2</span><div><h2>What you measured</h2><p class="card-subtitle">Weigh both parts. Purity and impurity % are calculated for you.</p></div></div></div>
            <div class="card-body">
                <div class="fields">
                    <div class="form-group measure measure-clean">
                        <label class="form-label" for="outputQuantity">Pure / clean output (kg) *</label>
                        <input class="form-control form-control-lg" id="outputQuantity" name="output_quantity" type="number" min="0" step="0.01" placeholder="0.00" value="{{ old('output_quantity') }}" required>
                        <p class="form-hint">Goes to finished goods in step 2.</p>
                        @error('output_quantity')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-group measure measure-nc">
                        <label class="form-label" for="ncQuantity">Impurity / NC (kg) *</label>
                        <input class="form-control form-control-lg" id="ncQuantity" name="non_conforming" type="number" min="0" step="0.01" placeholder="0.00" value="{{ old('non_conforming') }}" required>
                        <p class="form-hint">Stays for rework in step 3.</p>
                        @error('non_conforming')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="notice" id="balanceMessage" role="status" style="margin-top:18px">Select an issue and enter both weights.</div>
            </div>
        </section>

        {{-- 3. Finished good --}}
        <section class="card">
            <div class="card-header"><div class="section-title"><span class="section-number">3</span><div><h2>Finished good</h2><p class="card-subtitle">The product that receives the clean output. Pick one or create it.</p></div></div></div>
            <div class="card-body">
                <div class="form-group">
                    <label for="productId" class="form-label">Finished-good product</label>
                    <select class="form-control" name="product_id" id="productId">
                        <option value="">+ Create a new product</option>
                        @foreach($products as $product)
                        <option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>
                            {{ $product->name }} — {{ number_format($product->quantity, 2) }} kg in stock
                        </option>
                        @endforeach
                    </select>
                    @error('product_id')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div id="newProductFields" class="fields">
                    <div class="form-group">
                        <label class="form-label" for="productName">New product name</label>
                        <input class="form-control" id="productName" name="new_product_name" value="{{ old('new_product_name') }}" maxlength="200" placeholder="e.g. Hibiscus">
                        @error('new_product_name')<p class="form-error">{{ $message }}</p>@enderror
                        <p class="form-hint">Stock is always received and counted in kg. The sell price is set automatically from the costing defaults when the batch is posted.</p>
                    </div>
                </div>
                <div class="form-group" style="margin-top:6px">
                    <label class="form-label" for="packingCost">Packing &amp; label cost for this batch (ETB) *</label>
                    <input class="form-control" id="packingCost" name="packing_cost" type="number" min="0" step="0.01" value="{{ old('packing_cost', 0) }}" required>
                </div>
            </div>
        </section>

        {{-- 4. Reference --}}
        <section class="card">
            <div class="card-header"><div class="section-title"><span class="section-number">4</span><h2>Document reference</h2></div></div>
            <div class="card-body fields">
                <div class="form-group">
                    <label class="form-label" for="padType">Pad type *</label>
                    <select id="padType" name="pad_type" class="form-control" required>
                        @foreach(['GRV','GRH','GRS'] as $type)<option @selected(old('pad_type', 'GRV') === $type)>{{ $type }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="padNumber">Pad number *</label>
                    <input id="padNumber" name="pad_number" class="form-control" value="{{ old('pad_number') }}" required maxlength="100">
                </div>
                <div class="form-group span-all">
                    <label class="form-label" for="notes">Notes <span class="muted">· optional</span></label>
                    <textarea id="notes" name="description" class="form-control" maxlength="2000" placeholder="Quality observations, reasons for loss…">{{ old('description') }}</textarea>
                </div>
            </div>
        </section>
    </div>

    {{-- Live summary --}}
    <aside class="card summary-card">
        <div class="card-body">
            <p class="eyebrow">Live preview</p>
            <h2>Batch summary</h2>
            <div class="summary-line"><span class="muted">Input</span><strong id="sInput">0.00 kg</strong></div>
            <div class="yield-track" aria-hidden="true"><span class="yield-clean" id="barClean"></span><span class="yield-nc" id="barNc"></span></div>
            <div class="summary-line"><span><i class="dot dot-clean"></i>Purity</span><strong id="sPurity">—</strong></div>
            <div class="summary-line"><span><i class="dot dot-nc"></i>Impurity (NC)</span><strong id="sImpurity">—</strong></div>
            <div class="summary-line"><span class="muted">Unaccounted loss</span><strong id="sLoss">—</strong></div>
            <div class="summary-total">
                <div class="summary-line"><span class="muted">Material</span><span id="sMaterial">0.00</span></div>
                <div class="summary-line"><span class="muted">Packing</span><span id="sPacking">0.00</span></div>
                <div class="summary-line"><span>Total cost · ETB</span><strong id="sTotal">0.00</strong></div>
                <div class="summary-line"><span class="muted">Cost per good kg</span><span id="sPerKg">—</span></div>
            </div>
            <div class="notice" id="stockPreview">Choose a product to preview the stock receipt.</div>
            <p class="form-hint" style="margin-top:14px">Nothing goes into stock yet. You will review and push in step 2.</p>
        </div>
    </aside>
</div>

<div class="form-footer">
    <p>* Required</p>
    <a href="{{ route('productions.index') }}" class="btn btn-secondary">Cancel</a>
    <button class="btn btn-primary btn-lg" type="submit">Submit results &rarr;</button>
</div>
</form>
@endif
@endsection

@push('scripts')
<script>
(() => {
    const form = document.getElementById('resultsForm');
    if (!form) return;
    const $ = id => document.getElementById(id);
    const n = v => Number(v) || 0;
    const fmt = v => v.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    const pct = (a, b) => b > 0 ? (100 * a / b).toFixed(1) + '%' : '—';

    function update() {
        let input = 0, material = 0;
        form.querySelectorAll('[name="issue_ids[]"]:checked').forEach(cb => { input += n(cb.dataset.weight); material += n(cb.dataset.cost); });
        const out = n($('outputQuantity').value), nc = n($('ncQuantity').value), pack = n($('packingCost').value);
        const measured = $('outputQuantity').value !== '' && $('ncQuantity').value !== '';
        const loss = input - out - nc, over = measured && loss < -0.0001;

        $('outputQuantity').setCustomValidity(over ? 'Clean output + NC is more than the issued weight.' : '');
        $('sInput').textContent = fmt(input) + ' kg';
        $('sPurity').textContent = measured ? fmt(out) + ' kg · ' + pct(out, input) : '—';
        $('sImpurity').textContent = measured ? fmt(nc) + ' kg · ' + pct(nc, input) : '—';
        $('sLoss').textContent = measured ? fmt(Math.max(0, loss)) + ' kg' : '—';
        $('barClean').style.width = (input ? Math.min(100, 100 * out / input) : 0) + '%';
        $('barNc').style.width = (input ? Math.min(100, 100 * nc / input) : 0) + '%';
        $('sMaterial').textContent = fmt(material);
        $('sPacking').textContent = fmt(pack);
        $('sTotal').textContent = fmt(material + pack);
        $('sPerKg').textContent = out + nc > 0 ? 'ETB ' + fmt((material + pack) / (out + nc)) : '—';

        const msg = $('balanceMessage');
        if (!input) { msg.className = 'notice'; msg.textContent = 'Select the issue you processed.'; }
        else if (!measured) { msg.className = 'notice'; msg.textContent = 'Enter both weights. Input is ' + fmt(input) + ' kg.'; }
        else if (over) { msg.className = 'notice error'; msg.textContent = 'Clean + NC is ' + fmt(-loss) + ' kg more than the input. Check the scale readings.'; }
        else if (loss > 0.0001) { msg.className = 'notice warning'; msg.textContent = fmt(loss) + ' kg is missing (moisture, dust…). Explain it in the notes.'; }
        else { msg.className = 'notice success'; msg.textContent = 'Balanced: clean + NC equals the input weight.'; }

        // Suggest the product the store intended
        const checked = form.querySelector('[name="issue_ids[]"]:checked');
        if (checked && checked.dataset.product && !$('productId').dataset.touched) $('productId').value = checked.dataset.product;

        const existing = $('productId').value !== '';
        $('newProductFields').hidden = existing;
        const sp = $('stockPreview');
        if (!out) { sp.className = 'notice'; sp.textContent = 'No clean output to push.'; }
        else { sp.className = 'notice success'; sp.textContent = fmt(out) + ' kg will be received into stock (finished goods are counted in kg).'; }
    }
    $('productId').addEventListener('change', e => e.target.dataset.touched = 1);
    form.addEventListener('input', update);
    form.addEventListener('change', update);
    update();
})();
</script>
@endpush
