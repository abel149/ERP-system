@extends('layouts.app')
@section('title','New Purchase Order')
@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>New Purchase Order</h1>
        <p>Create a procurement order for a supplier</p>
    </div>
    <div class="page-header-actions">
        <a href="{{ route('purchase-orders.index') }}" class="btn">Cancel</a>
    </div>
</div>

<form method="POST" action="{{ route('purchase-orders.store') }}" id="poForm">
@csrf

<div class="form-section mb-2">
    <div class="form-section-header"><h3>Order Details</h3></div>
    <div class="form-section-body">
        <div class="form-grid">
            <div class="field">
                <label class="required">Supplier</label>
                <select name="supplier_id" required>
                    <option value="">Select supplier...</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}" @selected(old('supplier_id')==$s->id)>{{ $s->name }} @if($s->company)({{ $s->company }})@endif</option>
                    @endforeach
                </select>
                @error('supplier_id')<div class="error">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label class="required">Order Date</label>
                <input type="date" name="order_date" value="{{ old('order_date', now()->toDateString()) }}" required>
            </div>
            <div class="field">
                <label>Expected Delivery</label>
                <input type="date" name="expected_date" value="{{ old('expected_date') }}">
            </div>
            <div class="field">
                <label>Notes</label>
                <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Special instructions...">
            </div>
        </div>
    </div>
</div>

<div class="form-section mb-2">
    <div class="form-section-header">
        <h3>Order Items</h3>
        <button type="button" class="btn btn-sm btn-primary" onclick="addRow()">+ Add Item</button>
    </div>
    <div class="form-section-body" style="padding:0;">
        <div class="table-wrap">
            <table class="tbl" id="itemsTable">
                <thead>
                    <tr>
                        <th>Product (optional)</th>
                        <th>Description</th>
                        <th style="width:90px;">Qty</th>
                        <th style="width:120px;">Unit Cost</th>
                        <th style="width:110px;">Total</th>
                        <th style="width:40px;"></th>
                    </tr>
                </thead>
                <tbody id="itemsBody">
                    <tr data-row="0">
                        <td>
                            <select name="items[0][product_id]" style="width:100%;" onchange="fillDescription(this, 0)">
                                <option value="">— None —</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}" data-name="{{ $p->name }}" data-cost="{{ $p->cost_price }}">{{ $p->name }} ({{ $p->sku }})</option>
                                @endforeach
                            </select>
                        </td>
                        <td><input type="text" name="items[0][description]" placeholder="Item description" required style="width:100%;"></td>
                        <td><input type="number" name="items[0][quantity]" value="1" min="1" required style="width:100%;" onchange="calcRow(0)"></td>
                        <td><input type="number" name="items[0][unit_cost]" value="0" min="0" step="0.01" required style="width:100%;" onchange="calcRow(0)"></td>
                        <td><input type="text" id="row_total_0" value="0.00" readonly style="width:100%;background:#f8fafc;"></td>
                        <td><button type="button" class="btn btn-xs btn-outline-danger" onclick="removeRow(this)">x</button></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="form-section">
    <div class="form-section-header"><h3>Totals</h3></div>
    <div class="form-section-body">
        <div class="form-grid" style="max-width:500px;">
            <div class="field">
                <label>Tax ($)</label>
                <input type="number" name="tax" value="{{ old('tax',0) }}" min="0" step="0.01" id="taxInput" onchange="calcTotal()">
            </div>
            <div class="field">
                <label>Shipping ($)</label>
                <input type="number" name="shipping" value="{{ old('shipping',0) }}" min="0" step="0.01" id="shippingInput" onchange="calcTotal()">
            </div>
        </div>
        <div style="margin-top:12px;font-size:16px;font-weight:700;">
            Total: $<span id="grandTotal">0.00</span>
        </div>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Create Purchase Order</button>
        <a href="{{ route('purchase-orders.index') }}" class="btn">Cancel</a>
    </div>
</div>
</form>

@push('scripts')
<script>
let rowCount = 1;
const products = {!! json_encode($products->keyBy('id')->map(fn($p) => ['name'=>$p->name,'cost'=>(float)$p->cost_price])) !!};

function addRow() {
    const idx = rowCount++;
    const row = document.querySelector('[data-row="0"]').cloneNode(true);
    row.setAttribute('data-row', idx);
    row.querySelector('[name="items[0][product_id]"]').name = `items[${idx}][product_id]`;
    row.querySelector('[name="items[0][description]"]').name = `items[${idx}][description]`;
    row.querySelector('[name="items[0][quantity]"]').name = `items[${idx}][quantity]`;
    row.querySelector('[name="items[0][unit_cost]"]').name = `items[${idx}][unit_cost]`;
    row.querySelector('[id^="row_total_"]').id = `row_total_${idx}`;
    const sel = row.querySelector('select');
    sel.setAttribute('onchange', `fillDescription(this, ${idx})`);
    sel.selectedIndex = 0;
    row.querySelector('input[type="number"][name*="quantity"]').value = 1;
    row.querySelector('input[type="number"][name*="unit_cost"]').value = 0;
    row.querySelector(`#row_total_${idx}`).value = '0.00';
    row.querySelector('[name*="description"]').value = '';
    const qtyInput = row.querySelector('[name*="quantity"]');
    const costInput = row.querySelector('[name*="unit_cost"]');
    qtyInput.setAttribute('onchange', `calcRow(${idx})`);
    costInput.setAttribute('onchange', `calcRow(${idx})`);
    document.getElementById('itemsBody').appendChild(row);
    calcTotal();
}

function removeRow(btn) {
    const rows = document.getElementById('itemsBody').querySelectorAll('tr');
    if (rows.length <= 1) return;
    btn.closest('tr').remove();
    calcTotal();
}

function fillDescription(sel, idx) {
    const pid = sel.value;
    if (pid && products[pid]) {
        document.querySelector(`[name="items[${idx}][description]"]`).value = products[pid].name;
        document.querySelector(`[name="items[${idx}][unit_cost]"]`).value = products[pid].cost;
        calcRow(idx);
    }
}

function calcRow(idx) {
    const qty  = parseFloat(document.querySelector(`[name="items[${idx}][quantity]"]`)?.value) || 0;
    const cost = parseFloat(document.querySelector(`[name="items[${idx}][unit_cost]"]`)?.value) || 0;
    const el = document.getElementById(`row_total_${idx}`);
    if (el) el.value = (qty * cost).toFixed(2);
    calcTotal();
}

function calcTotal() {
    let sub = 0;
    document.querySelectorAll('[id^="row_total_"]').forEach(el => sub += parseFloat(el.value) || 0);
    const tax  = parseFloat(document.getElementById('taxInput')?.value) || 0;
    const ship = parseFloat(document.getElementById('shippingInput')?.value) || 0;
    document.getElementById('grandTotal').textContent = (sub + tax + ship).toFixed(2);
}
</script>
@endpush
@endsection
