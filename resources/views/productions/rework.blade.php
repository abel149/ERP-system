@extends('layouts.app')
@section('title', 'Rework '.$production->production_number)
@section('content')
@php $product = $production->product; @endphp

<div class="page-header">
    <div>
        <p class="eyebrow">Production · Step 3 of 3 · Issue {{ $production->issue_numbers }}</p>
        <h1 class="page-title">Submit rework results</h1>
        <p class="page-subtitle">{{ $production->production_number }} · {{ number_format($production->non_conforming, 2) }} kg NC was reworked. Enter what you recovered and what is left.</p>
    </div>
    <a href="{{ route('productions.show', $production) }}" class="btn btn-secondary">&larr; Back to batch</a>
</div>

@include('productions.partials.steps', ['current' => 3])

<form method="POST" action="{{ route('productions.rework.store', $production) }}" id="reworkForm"
      data-input="{{ $production->non_conforming }}" data-value="{{ $production->nc_value }}">
@csrf
<div class="work-layout">
    <div class="stack">
        <section class="card">
            <div class="card-header"><div class="section-title"><span class="section-number">1</span><div><h2>What the rework produced</h2><p class="card-subtitle">Input: {{ number_format($production->non_conforming, 2) }} kg NC from this batch.</p></div></div></div>
            <div class="card-body">
                <div class="fields">
                    <div class="form-group measure measure-clean">
                        <label class="form-label" for="reworkOutput">Recovered purity (kg) *</label>
                        <input class="form-control form-control-lg" id="reworkOutput" name="rework_output" type="number" min="0" step="0.01" placeholder="0.00" value="{{ old('rework_output') }}" required>
                        <p class="form-hint">Goes to <strong>{{ $product?->name }}</strong>.</p>
                        @error('rework_output')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-group measure measure-nc">
                        <label class="form-label" for="reworkNc">Final NC (kg) *</label>
                        <input class="form-control form-control-lg" id="reworkNc" name="rework_nc" type="number" min="0" step="0.01" placeholder="0.00" value="{{ old('rework_nc') }}" required>
                        <p class="form-hint">Goes to NC stock and is sold at the NC price.</p>
                        @error('rework_nc')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="form-group" style="margin-top:6px">
                    <label class="form-label" for="reworkPacking">Rework packing &amp; label cost (ETB) *</label>
                    <input class="form-control" id="reworkPacking" name="rework_packing_cost" type="number" min="0" step="0.01" value="{{ old('rework_packing_cost', 0) }}" required>
                </div>
                <div class="notice" id="balanceMessage" role="status">Enter both weights.</div>
            </div>
        </section>

        <section class="card" id="ncSection">
            <div class="card-header"><div class="section-title"><span class="section-number">2</span><div><h2>NC product</h2><p class="card-subtitle">Where the final NC is stocked, and its selling price.</p></div></div></div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label" for="ncProduct">NC product</label>
                    <select class="form-control" id="ncProduct" name="nc_product_id">
                        <option value="">+ Create a new NC product</option>
                        @foreach($ncProducts as $nc)
                        <option value="{{ $nc->id }}" data-price="{{ $nc->sell_price }}" @selected(old('nc_product_id') == $nc->id)>{{ $nc->name }} · ETB {{ number_format($nc->sell_price, 2) }}/kg · {{ number_format($nc->quantity, 2) }} kg in stock</option>
                        @endforeach
                    </select>
                    @error('nc_product_id')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="fields">
                    <div class="form-group" id="ncNameField">
                        <label class="form-label" for="ncName">New NC product name</label>
                        <input class="form-control" id="ncName" name="nc_product_name" maxlength="200" value="{{ old('nc_product_name', $product ? $product->name.' — NC' : '') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="ncPrice">NC selling price per kg (ETB)</label>
                        <input class="form-control" id="ncPrice" name="nc_sell_price" type="number" min="0" step="0.01" value="{{ old('nc_sell_price') }}" placeholder="Set price">
                        @error('nc_sell_price')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card-header"><div class="section-title"><span class="section-number">3</span><h2>Notes</h2></div></div>
            <div class="card-body">
                <textarea class="form-control" name="rework_notes" maxlength="2000" placeholder="How the NC was reworked, reasons for loss…">{{ old('rework_notes') }}</textarea>
            </div>
        </section>
    </div>

    <aside class="card summary-card">
        <div class="card-body">
            <p class="eyebrow">Live preview</p>
            <h2>Rework summary</h2>
            <div class="summary-line"><span class="muted">NC input</span><strong>{{ number_format($production->non_conforming, 2) }} kg</strong></div>
            <div class="yield-track" aria-hidden="true"><span class="yield-clean" id="barClean"></span><span class="yield-nc" id="barNc"></span></div>
            <div class="summary-line"><span><i class="dot dot-clean"></i>Recovered → FG</span><strong id="sRecovered">—</strong></div>
            <div class="summary-line"><span><i class="dot dot-nc"></i>Final NC → NC stock</span><strong id="sNc">—</strong></div>
            <div class="summary-line"><span class="muted">Loss</span><strong id="sLoss">—</strong></div>
            <div class="summary-total">
                <div class="summary-line"><span class="muted">Cost carried by NC</span><span>{{ number_format($production->nc_value, 2) }}</span></div>
                <div class="summary-line"><span class="muted">Rework packing</span><span id="sPacking">0.00</span></div>
                <div class="summary-line"><span>Total · ETB</span><strong id="sTotal">0.00</strong></div>
                <div class="summary-line"><span class="muted">Cost per kg</span><span id="sPerKg">—</span></div>
            </div>
            <div class="notice" id="stockPreview">—</div>
        </div>
    </aside>
</div>

<div class="form-footer">
    <p>Posting adds recovered output to finished goods and final NC to NC stock. This completes the batch.</p>
    <a href="{{ route('productions.show', $production) }}" class="btn btn-secondary">Cancel</a>
    <button class="btn btn-primary btn-lg" type="submit" onclick="return confirm('Post the rework results to stock?')">Post rework results &rarr;</button>
</div>
</form>
@endsection

@push('scripts')
<script>
(() => {
    const form = document.getElementById('reworkForm');
    const $ = id => document.getElementById(id);
    const n = v => Number(v) || 0;
    const fmt = v => v.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    const input = n(form.dataset.input), carried = n(form.dataset.value);

    function update() {
        const rec = n($('reworkOutput').value), nc = n($('reworkNc').value), pack = n($('reworkPacking').value);
        const measured = $('reworkOutput').value !== '' && $('reworkNc').value !== '';
        const loss = input - rec - nc, over = measured && loss < -0.0001;
        $('reworkOutput').setCustomValidity(over ? 'Recovered + final NC is more than the NC sent to rework.' : '');

        $('sRecovered').textContent = measured ? fmt(rec) + ' kg' : '—';
        $('sNc').textContent = measured ? fmt(nc) + ' kg' : '—';
        $('sLoss').textContent = measured ? fmt(Math.max(0, loss)) + ' kg' : '—';
        $('barClean').style.width = Math.min(100, 100 * rec / input) + '%';
        $('barNc').style.width = Math.min(100, 100 * nc / input) + '%';
        $('sPacking').textContent = fmt(pack);
        $('sTotal').textContent = fmt(carried + pack);
        $('sPerKg').textContent = rec + nc > 0 ? 'ETB ' + fmt((carried + pack) / (rec + nc)) : '—';

        const msg = $('balanceMessage');
        if (!measured) { msg.className = 'notice'; msg.textContent = 'Enter both weights. Input is ' + fmt(input) + ' kg.'; }
        else if (over) { msg.className = 'notice error'; msg.textContent = 'Recovered + final NC is ' + fmt(-loss) + ' kg more than the input.'; }
        else if (loss > 0.0001) { msg.className = 'notice warning'; msg.textContent = fmt(loss) + ' kg lost during rework.'; }
        else { msg.className = 'notice success'; msg.textContent = 'Balanced with the NC input.'; }

        const sp = $('stockPreview');
        if (!rec) { sp.className = 'notice'; sp.textContent = 'Nothing recovered for finished goods.'; }
        else { sp.className = 'notice success'; sp.textContent = fmt(rec) + ' kg will be added to ' + @json($product?->name ?? 'finished goods') + ' stock in kg.'; }

        $('ncSection').style.opacity = nc > 0 ? 1 : .55;
        const existing = $('ncProduct').value !== '';
        $('ncNameField').hidden = existing;
        if (existing && !$('ncPrice').dataset.touched) $('ncPrice').value = $('ncProduct').selectedOptions[0].dataset.price;
    }
    $('ncPrice').addEventListener('input', e => e.target.dataset.touched = 1);
    form.addEventListener('input', update);
    form.addEventListener('change', update);
    update();
})();
</script>
@endpush
