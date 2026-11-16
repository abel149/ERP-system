@extends('layouts.app')
@section('title', 'Costing Defaults')
@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>Costing Defaults</h1>
        <p>Overheads &amp; pricing applied per stock unit on every new sales order</p>
    </div>
</div>

<form method="POST" action="{{ route('settings.costing.update') }}">
@csrf
@method('PUT')
<div class="form-section">
    <div class="form-section-header">
        <h3>Other costs per unit</h3>
    </div>
    <div class="form-section-body">
        <div class="form-grid">
            <div class="field">
                <label class="required">{{ $labels['rent_cost'] }}</label>
                <input type="number" name="rent_cost" step="0.01" min="0" value="{{ old('rent_cost', $values['rent_cost']) }}" required>
                @error('rent_cost')<div class="error">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label class="required">{{ $labels['utility_cost'] }}</label>
                <input type="number" name="utility_cost" step="0.01" min="0" value="{{ old('utility_cost', $values['utility_cost']) }}" required>
                @error('utility_cost')<div class="error">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label class="required">{{ $labels['machine_cost'] }}</label>
                <input type="number" name="machine_cost" step="0.01" min="0" value="{{ old('machine_cost', $values['machine_cost']) }}" required>
                @error('machine_cost')<div class="error">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label class="required">{{ $labels['other_cost'] }}</label>
                <input type="number" name="other_cost" step="0.01" min="0" value="{{ old('other_cost', $values['other_cost']) }}" required>
                @error('other_cost')<div class="error">{{ $message }}</div>@enderror
            </div>
        </div>
        <p class="hint" style="margin-top:12px">
            Charged per kg of product sold. Derive them from your monthly overheads
            (rent, utilities, admin, security, direct labour &divide; monthly output) — the four
            values prefill each item row on a sales order and can be adjusted per order or per item.
        </p>
    </div>
</div>

<div class="form-section">
    <div class="form-section-header">
        <h3>Pricing</h3>
    </div>
    <div class="form-section-body">
        <div class="form-grid form-grid-2">
            <div class="field">
                <label class="required">{{ $labels['margin_percent'] }} (%)</label>
                <input type="number" name="margin_percent" step="0.01" min="0" value="{{ old('margin_percent', $values['margin_percent']) }}" required>
                <div class="hint">Suggested unit price = total cost &times; (1 + margin%).</div>
                @error('margin_percent')<div class="error">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label class="required">{{ $labels['vat_percent'] }} (%)</label>
                <input type="number" name="vat_percent" step="0.01" min="0" max="100" value="{{ old('vat_percent', $values['vat_percent']) }}" required>
                <div class="hint">Added on top of the unit price where VAT applies.</div>
                @error('vat_percent')<div class="error">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Save Defaults</button>
    </div>
</div>
</form>
@endsection
