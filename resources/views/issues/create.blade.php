@extends('layouts.app')
@section('title', 'Issue materials')
@section('content')
<div class="page-header">
    <div><p class="eyebrow">Store / Material issue</p><h1 class="page-title">Issue materials to production</h1><p class="page-subtitle">Build the ingredient list. Quantities and costs stay attached to this issue.</p></div>
    <a href="{{ route('issues.index') }}" class="btn btn-secondary">&larr; All issues</a>
</div>
<form method="POST" action="{{ route('issues.store') }}" id="issueForm">
@csrf
<div class="work-layout">
    <div class="stack">
        <section class="card">
            <div class="card-header"><div class="section-title"><span class="section-number">01</span><div><h2>Ingredients from store</h2><p class="card-subtitle">Choose each material and the quantity to release.</p></div></div><button class="btn btn-secondary" id="addLine" type="button">+ Add material</button></div>
            <div class="card-body">
                <div id="issueLines" class="stack">
                    @foreach(old('items', [['raw_material_id' => $preselectedMaterialId, 'quantity' => '']]) as $index => $line)
                        @include('issues.material-line', ['index' => $index, 'line' => $line])
                    @endforeach
                </div>
            </div>
        </section>
        <section class="card">
            <div class="card-header"><div class="section-title"><span class="section-number">02</span><h2>Issue details</h2></div></div>
            <div class="card-body fields">
                <div class="form-group"><label class="form-label" for="padType">Pad type *</label><select id="padType" class="form-control" name="pad_type" required>@foreach(['GRV','GRH','GRS'] as $type)<option @selected(old('pad_type', 'GRV') === $type)>{{ $type }}</option>@endforeach</select></div>
                <div class="form-group"><label class="form-label" for="padNumber">Pad number *</label><input class="form-control" id="padNumber" name="pad_number" value="{{ old('pad_number') }}" required maxlength="100" placeholder="Your document reference"></div>
                <div class="form-group span-all"><label class="form-label" for="intendedProduct">Intended finished good <span class="muted">· optional</span></label><select class="form-control" id="intendedProduct" name="intended_product_id"><option value="">Production will choose the finished good</option>@foreach($products as $product)<option value="{{ $product->id }}" @selected(old('intended_product_id') == $product->id)>{{ $product->name }}</option>@endforeach</select></div>
                <div class="form-group span-all"><label class="form-label" for="issueNotes">Notes <span class="muted">· optional</span></label><textarea id="issueNotes" class="form-control" name="description" placeholder="Handling instructions or notes for production">{{ old('description') }}</textarea></div>
            </div>
        </section>
    </div>
    <aside class="card summary-card"><div class="card-body">
        <p class="eyebrow">Issue preview</p><h2>Materials to release</h2>
        <div class="summary-line"><span class="muted">Ingredient lines</span><strong id="lineCount">1</strong></div>
        <div class="summary-line summary-total"><span>Total · ETB</span><strong id="issueTotal">0.00</strong></div>
        <p class="notice">Submitting deducts these quantities from store and makes them available to production.</p>
        <p class="form-hint" style="margin-top:16px">Unit costs are saved when issued. Later price changes do not change this issue's value.</p>
    </div></aside>
</div>
<div class="form-footer"><p>* Required fields</p><a href="{{ route('issues.index') }}" class="btn btn-secondary">Cancel</a><button type="submit" class="btn btn-primary">Issue to production &rarr;</button></div>
</form>
<template id="lineTemplate">@include('issues.material-line', ['index' => '__INDEX__', 'line' => []])</template>
@push('scripts')
<script>
(() => {
    const lines = document.getElementById('issueLines');
    let nextIndex = Math.max(...[...lines.querySelectorAll('[data-index]')].map(row => Number(row.dataset.index))) + 1;
    const fmt = n => n.toLocaleString(undefined, {minimumFractionDigits:2,maximumFractionDigits:2});
    function update() {
        let total = 0;
        const rows = [...lines.querySelectorAll('.material-row')];
        rows.forEach(row => {
            const select = row.querySelector('select'), option = select.selectedOptions[0];
            const qty = row.querySelector('[data-quantity]'), quantity = Number(qty.value) || 0;
            const cost = Number(option.dataset.cost) || 0, stock = Number(option.dataset.stock) || 0;
            const duplicate = select.value && rows.some(other => other !== row && other.querySelector('select').value === select.value);
            select.setCustomValidity(duplicate ? 'This material is already on another line.' : '');
            qty.setCustomValidity(select.value && quantity > stock ? 'Quantity exceeds available stock.' : '');
            row.querySelector('[data-stock]').textContent = select.value ? fmt(stock) + ' ' + option.dataset.unit + ' available · ETB ' + fmt(cost) + ' per ' + option.dataset.unit : 'Choose a material to see availability.';
            row.querySelector('[data-unit]').textContent = option.dataset.unit || '';
            row.querySelector('[data-value]').textContent = 'ETB ' + fmt(quantity * cost);
            row.querySelector('[data-remove]').disabled = rows.length === 1;
            total += quantity * cost;
        });
        document.getElementById('lineCount').textContent = rows.length;
        document.getElementById('issueTotal').textContent = fmt(total);
    }
    document.getElementById('addLine').addEventListener('click', () => {
        // Options are escaped by Blade; no material names are interpolated as HTML in JavaScript.
        const fragment = document.getElementById('lineTemplate').content.cloneNode(true);
        fragment.querySelectorAll('*').forEach(node => {
            ['id', 'for', 'name', 'data-index'].forEach(attr => {
                if (node.hasAttribute(attr)) node.setAttribute(attr, node.getAttribute(attr).replaceAll('__INDEX__', nextIndex));
            });
        });
        nextIndex++; lines.appendChild(fragment); update();
        lines.lastElementChild.querySelector('select').focus();
    });
    lines.addEventListener('click', event => {
        const button = event.target.closest('[data-remove]');
        if (button && lines.children.length > 1) { button.closest('.material-row').remove(); update(); }
    });
    lines.addEventListener('change', update); lines.addEventListener('input', update); update();
})();
</script>
@endpush
@endsection
