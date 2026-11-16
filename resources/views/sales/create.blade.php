@extends('layouts.app')

@section('title', 'New Sale')

@section('content')
<form method="POST" action="{{ route('sales.store') }}" id="sale-form">
    @csrf

    <div class="card">
        <h2>Customer &amp; Payment</h2>
        <div class="form-row">
            <div class="field">
                <label for="customer_id">Customer</label>
                <select id="customer_id" name="customer_id">
                    <option value="">Walk-in customer</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>{{ $customer->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="payment_method">Payment method</label>
                <select id="payment_method" name="payment_method">
                    <option value="cash" @selected(old('payment_method') === 'cash')>Cash</option>
                    <option value="card" @selected(old('payment_method') === 'card')>Card</option>
                    <option value="bank_transfer" @selected(old('payment_method') === 'bank_transfer')>Bank transfer</option>
                    <option value="credit" @selected(old('payment_method') === 'credit')>Credit</option>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="field">
                <label for="pad_type">Pad/FS type *</label>
                <select id="pad_type" name="pad_type" required>
                    @foreach (['GRV', 'GIV', 'RMI', 'RMR', 'SMI','SMR','FGI','FGR'] as $type)
                        <option value="{{ $type }}" @selected(old('pad_type', 'GRV') === $type)>{{ $type }}</option>
                    @endforeach
                </select>
                @error('pad_type')<div class="error">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label for="pad_number">Pad/FS number *</label>
                <input type="text" id="pad_number" name="pad_number" maxlength="100" value="{{ old('pad_number') }}" placeholder="e.g. OF/BIP/CD/026" required>
                @error('pad_number')<div class="error">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    <div class="card">
        <h2>Order costing &amp; pricing</h2>
        <p class="hint" style="margin-bottom:14px">
            Overheads charged per kg.
            @if (auth()->user()->isAdmin())
                Defaults come from <a href="{{ route('settings.costing.edit') }}">Costing Defaults</a> —
            @else
                Defaults come from the company costing settings —
            @endif
            adjust them here for this order only, or per item below.
        </p>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:12px" id="order-costs">
            <div class="field">
                <label for="oc-rent">Rent / unit</label>
                <input type="number" id="oc-rent" name="costs[rent_cost]" step="0.01" min="0" value="{{ old('costs.rent_cost', $costing['rent_cost']) }}">
            </div>
            <div class="field">
                <label for="oc-util">Utility / unit</label>
                <input type="number" id="oc-util" name="costs[utility_cost]" step="0.01" min="0" value="{{ old('costs.utility_cost', $costing['utility_cost']) }}">
            </div>
            <div class="field">
                <label for="oc-mt">Machine time / unit</label>
                <input type="number" id="oc-mt" name="costs[machine_cost]" step="0.01" min="0" value="{{ old('costs.machine_cost', $costing['machine_cost']) }}">
            </div>
            <div class="field">
                <label for="oc-oc">Other cost / unit</label>
                <input type="number" id="oc-oc" name="costs[other_cost]" step="0.01" min="0" value="{{ old('costs.other_cost', $costing['other_cost']) }}">
            </div>
            <div class="field">
                <label for="order-margin">Margin %</label>
                <input type="number" id="order-margin" name="margin_percent" step="0.01" min="0" value="{{ old('margin_percent', $costing['margin_percent']) }}">
            </div>
            <div class="field">
                <label for="order-vat">VAT %</label>
                <input type="number" id="order-vat" name="vat_percent" step="0.01" min="0" max="100" value="{{ old('vat_percent', $costing['vat_percent']) }}">
            </div>
        </div>
        <button type="button" class="btn btn-sm mt-1" id="apply-costs">Apply to all item rows</button>
        @error('costs.*')<div class="error">{{ $message }}</div>@enderror
    </div>

    <div class="card">
        <h2>Items</h2>
        <div class="table-wrap">
        <table class="data items-table" id="items-table">
            <thead>
                <tr>
                    <th style="min-width:220px">Product</th>
                    <th>Amount</th>
                    <th class="text-right">Base cost</th>
                    <th>Other costs / unit <span class="muted" style="font-weight:400">(R · U · MT · OC)</span></th>
                    <th class="text-right">Total cost</th>
                    <th>Margin %</th>
                    <th>Unit price</th>
                    <th class="text-center">VAT</th>
                    <th class="text-right">Line total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
        </div>
        <button type="button" class="btn mt" id="add-item">+ Add item</button>
        @error('items')<div class="error">{{ $message }}</div>@enderror
        @error('items.*')<div class="error">{{ $message }}</div>@enderror
    </div>

    <div class="card" style="max-width:420px; margin-left:auto">
        <div class="form-row">
            <div class="field">
                <label for="discount">Discount ($)</label>
                <input type="number" id="discount" name="discount" step="0.01" min="0" value="{{ old('discount', 0) }}" class="recalc">
            </div>
            <div class="field">
                <label for="tax">VAT / Tax ($) <button type="button" class="btn btn-sm" id="tax-auto" style="padding:1px 8px;font-size:11px" title="Reset to the VAT calculated per item">auto</button></label>
                <input type="number" id="tax" name="tax" step="0.01" min="0" value="{{ old('tax', 0) }}" class="recalc">
            </div>
        </div>
        <table style="width:100%; font-size:15px">
            <tr><td class="muted" style="padding:6px 0">Subtotal</td><td class="text-right" id="subtotal-display">$0.00</td></tr>
            <tr><td class="muted" style="padding:6px 0">Discount</td><td class="text-right" id="discount-display">-$0.00</td></tr>
            <tr><td class="muted" style="padding:6px 0">VAT / Tax</td><td class="text-right" id="tax-display">+$0.00</td></tr>
            <tr><td style="padding:10px 0; font-weight:700; font-size:18px">Total</td><td class="text-right" style="font-weight:700; font-size:18px" id="total-display">$0.00</td></tr>
        </table>
        <div class="field mt">
            <label for="paid">Amount paid</label>
            <input type="number" id="paid" name="paid" step="0.01" min="0" value="{{ old('paid') }}" placeholder="Leave empty = full payment">
        </div>
        <div class="field">
            <label for="notes">Notes</label>
            <input type="text" id="notes" name="notes" value="{{ old('notes') }}">
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%; padding:12px; font-size:16px">Complete Sale</button>
    </div>
</form>

<script>
const products = @json($productsPayload);

const tbody = document.querySelector('#items-table tbody');
const fmt = n => '$' + (Number(n) || 0).toFixed(2);
const n = v => Number(v) || 0;
const $ = id => document.getElementById(id);

const orderCostIds = {rent_cost: 'oc-rent', utility_cost: 'oc-util', machine_cost: 'oc-mt', other_cost: 'oc-oc'};
const discountEl = $('discount'), taxEl = $('tax');
let taxAuto = true;

const orderVal = key => $(orderCostIds[key]).value;

function rowHtml(i) {
    const opts = products.map(p =>
        `<option value="${p.id}" data-price="${p.price}" data-cost="${p.cost}" data-stock="${p.stock}" data-unit="${p.unit}">${p.name} (${p.sku}) — ${fmt(p.price)} — ${p.stock} ${p.unit} in stock</option>`
    ).join('');
    const costIn = (key, ph) =>
        `<input type="number" name="items[${i}][${key}]" class="item-cost" data-key="${key}" step="0.01" min="0" value="${orderVal(key)}" placeholder="${ph}" title="${ph}">`;
    return `<tr>
        <td><select name="items[${i}][product_id]" class="item-product" required>
            <option value="">— Select product —</option>${opts}
        </select><span class="line-sub item-stock"></span></td>
        <td><input type="number" name="items[${i}][quantity]" class="item-qty" step="0.01" min="0.01" value="1" required style="width:78px"></td>
        <td class="cell-num item-base">—</td>
        <td><div class="cost-grid">
            ${costIn('rent_cost', 'Rent')}${costIn('utility_cost', 'Utility')}${costIn('machine_cost', 'MT')}${costIn('other_cost', 'OC')}
        </div></td>
        <td class="cell-num item-total-cost">—</td>
        <td><input type="number" class="item-margin" step="0.01" min="0" style="width:66px" title="Margin % on total cost" value="${$('order-margin').value}"></td>
        <td><input type="number" name="items[${i}][unit_price]" class="item-price" step="0.01" min="0" value="0" required style="width:96px"></td>
        <td class="text-center"><input type="checkbox" name="items[${i}][vat]" value="1" class="item-vat" style="width:auto" checked title="Add VAT to this line"></td>
        <td class="cell-num"><span class="line-total strong">$0.00</span><span class="line-sub line-vat"></span></td>
        <td><button type="button" class="btn btn-sm btn-danger remove-row">&times;</button></td>
    </tr>`;
}

// One row: total cost, margin <-> unit price, VAT, line total.
function recalcRow(tr) {
    const base = n(tr.dataset.cost);
    let others = 0;
    tr.querySelectorAll('.item-cost').forEach(el => others += n(el.value));
    const totalCost = base + others;
    tr.querySelector('.item-total-cost').textContent = totalCost ? fmt(totalCost) : '—';

    const marginEl = tr.querySelector('.item-margin');
    const priceEl = tr.querySelector('.item-price');
    if (tr.dataset.src !== 'price') {
        priceEl.value = (totalCost * (1 + n(marginEl.value) / 100)).toFixed(2);
    }
    const price = n(priceEl.value);
    const margin = totalCost > 0 ? (price / totalCost - 1) * 100 : 0;
    if (tr.dataset.src === 'price') {
        marginEl.value = margin.toFixed(2);
    }
    marginEl.classList.toggle('margin-neg', margin < 0);
    marginEl.classList.toggle('margin-pos', margin >= 0);

    const qty = n(tr.querySelector('.item-qty').value);
    const vatPct = tr.querySelector('.item-vat').checked ? n($('order-vat').value) : 0;
    const line = qty * price;
    const vat = line * vatPct / 100;
    tr.querySelector('.line-total').textContent = fmt(line);
    tr.querySelector('.line-vat').textContent = vat ? '+VAT ' + fmt(vat) : '';
    return {line, vat};
}

function recalc() {
    let subtotal = 0, vatSum = 0;
    tbody.querySelectorAll('tr').forEach(tr => {
        const r = recalcRow(tr);
        subtotal += r.line;
        vatSum += r.vat;
    });
    if (taxAuto) taxEl.value = vatSum.toFixed(2);
    const discount = n(discountEl.value), tax = n(taxEl.value);
    $('subtotal-display').textContent = fmt(subtotal);
    $('discount-display').textContent = '-' + fmt(discount);
    $('tax-display').textContent = '+' + fmt(tax);
    $('total-display').textContent = fmt(Math.max(0, subtotal - discount + tax));
}

let rowIndex = 0;
function addRow() {
    tbody.insertAdjacentHTML('beforeend', rowHtml(rowIndex++));
    const tr = tbody.lastElementChild;
    tr.dataset.src = 'margin';
    tr.dataset.cost = 0;

    tr.querySelector('.item-product').addEventListener('change', e => {
        const opt = e.target.selectedOptions[0];
        tr.dataset.cost = opt && opt.dataset.cost ? opt.dataset.cost : 0;
        tr.querySelector('.item-base').textContent = tr.dataset.cost > 0 ? fmt(tr.dataset.cost) : '—';
        const qtyInput = tr.querySelector('.item-qty');
        if (opt && opt.dataset.stock) {
            qtyInput.max = opt.dataset.stock;
            tr.querySelector('.item-stock').textContent = opt.dataset.stock + ' ' + opt.dataset.unit + ' in stock';
        } else {
            qtyInput.removeAttribute('max');
            tr.querySelector('.item-stock').textContent = '';
        }
        // Use the price tracked with the product when it exists; otherwise
        // suggest total cost + margin.
        const tracked = n(opt && opt.dataset.price);
        if (tracked > 0) {
            tr.querySelector('.item-price').value = tracked.toFixed(2);
            tr.dataset.src = 'price';
        } else {
            tr.dataset.src = 'margin';
        }
        recalc();
    });
    tr.querySelector('.item-price').addEventListener('input', () => { tr.dataset.src = 'price'; recalc(); });
    tr.querySelector('.item-margin').addEventListener('input', () => { tr.dataset.src = 'margin'; recalc(); });
    tr.querySelectorAll('.item-qty, .item-cost, .item-vat').forEach(el =>
        el.addEventListener('input', recalc));
    tr.querySelector('.item-vat').addEventListener('change', recalc);
    tr.querySelector('.remove-row').addEventListener('click', () => { tr.remove(); recalc(); });
    recalc();
}

document.getElementById('add-item').addEventListener('click', addRow);

// Order-level costing: VAT rate applies live; costs/margin apply to every row on demand.
// Rows priced from the tracked product price keep that price — margin is recomputed.
$('order-vat').addEventListener('input', recalc);
$('apply-costs').addEventListener('click', () => {
    tbody.querySelectorAll('tr').forEach(tr => {
        tr.querySelectorAll('.item-cost').forEach(el => el.value = orderVal(el.dataset.key));
        if (tr.dataset.src !== 'price') {
            tr.querySelector('.item-margin').value = $('order-margin').value;
        }
    });
    recalc();
});

document.querySelectorAll('.recalc').forEach(el => el.addEventListener('input', recalc));
taxEl.addEventListener('input', () => { taxAuto = false; recalc(); });
$('tax-auto').addEventListener('click', () => { taxAuto = true; recalc(); });
addRow();
</script>
@endsection
