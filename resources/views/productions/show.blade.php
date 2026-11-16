@extends('layouts.app')
@section('title', $production->production_number)
@section('content')
@php
    $status   = $production->status === 'in_progress' ? 'recorded' : $production->status;
    $legacy   = $production->input_unit === null;
    $unit     = $legacy ? '' : 'kg';
    $product  = $production->product;
    $canAct   = auth()->user()->hasRole('admin', 'production');
    $current  = ['recorded' => 2, 'in_rework' => 3][$status] ?? 4;
    $noNc     = (float) $production->non_conforming <= 0;
    $good     = (float) $production->output_quantity + (float) $production->non_conforming;
    $costKg   = $good > 0 ? $production->total_direct_cost / $good : 0;

    // Preview of the FG push (step 2) — finished goods are stocked in kg
    $pushUnits = $status === 'recorded' && $product && $production->output_quantity > 0 && ! $legacy
        ? $product->stockUnitsFor((float) $production->output_quantity)
        : null;
@endphp

<div class="page-header">
    <div>
        <p class="eyebrow">Production batch · Issue {{ $production->issue_numbers ?: '—' }}</p>
        <h1 class="page-title">{{ $production->production_number }}</h1>
        <p class="page-subtitle">{{ $product?->name ?? 'No finished good' }} · submitted {{ $production->created_at->format('d M Y, H:i') }}</p>
    </div>
    <div class="action-group">
        <span class="badge badge-{{ $production->status_color }} badge-lg">{{ $production->status_label }}</span>
        <a href="{{ route('productions.index') }}" class="btn btn-secondary">&larr; All batches</a>
    </div>
</div>

@include('productions.partials.steps', ['current' => $current, 'skipped' => $status === 'completed' && $noNc ? 3 : null])

@if($legacy)
<p class="notice warning" style="margin-bottom:20px">Historical batch recorded before weights and cost snapshots were tracked. Figures are shown as originally saved.</p>
@endif
@error('complete')<p class="notice error" style="margin-bottom:20px">{{ $message }}</p>@enderror

<div class="work-layout">
    <div class="stack">

        {{-- ================= NEXT ACTION ================= --}}
        @if($status === 'recorded' && $canAct)
        <section class="card action-card">
            <div class="card-body">
                <p class="eyebrow">Next step</p>
                <h2>Push the pure output to finished goods</h2>
                <div class="transfer">
                    <div class="transfer-box clean"><span>Pure output</span><strong>{{ number_format($production->output_quantity, 2) }} kg</strong></div>
                    <div class="transfer-arrow">&rarr;</div>
                    <div class="transfer-box"><span>{{ $product?->name }}</span>
                        <strong>{{ $pushUnits !== null ? number_format($pushUnits, 2).' kg' : '—' }}</strong>
                        <small>at ETB {{ number_format($costKg, 2) }} / kg</small>
                    </div>
                </div>
                @if(! $noNc)
                <p class="notice warning">The {{ number_format($production->non_conforming, 2) }} kg NC stays here for rework, carrying its share of cost (ETB {{ number_format($production->non_conforming * $costKg, 2) }}).</p>
                @endif
                <form method="POST" action="{{ route('productions.complete', $production) }}" onsubmit="return confirm('Add the clean output to finished goods stock? This can only be done once.')">
                    @csrf
                    <button class="btn btn-primary btn-lg" type="submit">Push pure output to FG &rarr;</button>
                </form>
            </div>
        </section>
        @elseif($status === 'in_rework')
        <section class="card action-card warning">
            <div class="card-body">
                <p class="eyebrow">Next step</p>
                <h2>{{ number_format($production->non_conforming, 2) }} kg NC is waiting for rework</h2>
                <p class="muted">When the rework is finished, submit the recovered purity and the final NC under issue {{ $production->issue_numbers }}. Recovered goes to <strong>{{ $product?->name }}</strong>, the rest goes to NC stock.</p>
                @if($canAct)
                <a href="{{ route('productions.rework', $production) }}" class="btn btn-primary btn-lg" style="margin-top:16px">Submit rework results &rarr;</a>
                @endif
            </div>
        </section>
        @endif

        {{-- ================= STAGE 1 RESULTS ================= --}}
        <section class="card">
            <div class="card-header">
                <div><h2>Production results</h2><p class="card-subtitle">Measured by the production manager</p></div>
                @if($production->fg_posted_at)<span class="badge badge-success">Pushed to FG {{ $production->fg_posted_at->format('d M, H:i') }}</span>@endif
            </div>
            <div class="card-body">
                <div class="result-grid">
                    <div class="result"><span>Input</span><strong>{{ number_format($production->total_input, 2) }} {{ $unit }}</strong></div>
                    <div class="result clean"><span>Purity</span><strong>{{ number_format($production->output_quantity, 2) }} {{ $unit }}</strong><small>{{ number_format($production->purity * 100, 1) }}%</small></div>
                    <div class="result nc"><span>Impurity (NC)</span><strong>{{ number_format($production->non_conforming, 2) }} {{ $unit }}</strong><small>{{ number_format($production->impurity * 100, 1) }}%</small></div>
                    <div class="result"><span>Unaccounted loss</span><strong>{{ number_format($production->loss_quantity, 2) }} {{ $unit }}</strong></div>
                </div>
                <div class="yield-track" aria-hidden="true">
                    <span class="yield-clean" style="width:{{ min(100, $production->purity * 100) }}%"></span>
                    <span class="yield-nc" style="width:{{ min(100, $production->impurity * 100) }}%"></span>
                </div>
            </div>
        </section>

        {{-- ================= REWORK RESULTS ================= --}}
        @if($production->rework_completed_at)
        <section class="card">
            <div class="card-header">
                <div><h2>Rework results</h2><p class="card-subtitle">From the {{ number_format($production->non_conforming, 2) }} kg NC</p></div>
                <span class="badge badge-success">Posted {{ $production->rework_completed_at->format('d M, H:i') }}</span>
            </div>
            <div class="card-body">
                <div class="result-grid">
                    <div class="result"><span>Reworked</span><strong>{{ number_format($production->non_conforming, 2) }} kg</strong></div>
                    <div class="result clean"><span>Recovered → FG</span><strong>{{ number_format($production->rework_output, 2) }} kg</strong>
                        @if($production->rework_stock_quantity)<small>{{ number_format($production->rework_stock_quantity, 2) }} kg</small>@endif</div>
                    <div class="result nc"><span>Final NC → NC stock</span><strong>{{ number_format($production->rework_nc, 2) }} kg</strong>
                        @if($production->ncProduct)<small>{{ $production->ncProduct->name }} · ETB {{ number_format($production->ncProduct->sell_price, 2) }}/kg</small>@endif</div>
                    <div class="result"><span>Rework loss</span><strong>{{ number_format($production->rework_loss, 2) }} kg</strong></div>
                </div>
                @if($production->rework_notes)<p class="muted" style="margin-top:16px;white-space:pre-wrap">{{ $production->rework_notes }}</p>@endif
            </div>
        </section>
        @endif

        {{-- ================= INGREDIENT COSTS ================= --}}
        <section class="card">
            <div class="card-header"><div><h2>Ingredients &amp; material cost</h2><p class="card-subtitle">Unit prices frozen when the store issued the materials</p></div></div>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Ingredient</th><th>Issue</th><th class="num">Quantity</th><th class="num">Unit price</th><th class="num">Total cost</th></tr></thead>
                    <tbody>
                    @forelse($production->items as $link)
                        @foreach($link->issue?->items ?? [] as $item)
                        <tr>
                            <td><strong>{{ $item->rawMaterial?->name }}</strong></td>
                            <td><a class="mono" href="{{ route('issues.show', $link->issue) }}">{{ $link->issue->issue_number }}</a></td>
                            <td class="num">{{ number_format($item->quantity, 2) }} {{ $item->unit }}</td>
                            <td class="num">{{ number_format($item->unit_cost, 2) }}</td>
                            <td class="num"><strong>{{ number_format($item->total, 2) }}</strong></td>
                        </tr>
                        @endforeach
                    @empty
                        <tr><td colspan="5" class="muted">No ingredient lines.</td></tr>
                    @endforelse
                    </tbody>
                    <tfoot><tr><td colspan="4" class="num">Material cost · ETB</td><td class="num"><strong>{{ number_format($production->items->sum(fn ($l) => $l->issue?->total_value ?? 0), 2) }}</strong></td></tr></tfoot>
                </table>
            </div>
        </section>

        <section class="card">
            <div class="card-header"><h2>Reference &amp; notes</h2></div>
            <div class="card-body">
                <dl class="detail-list">
                    <div><dt>Document</dt><dd>{{ $production->pad_type }} · {{ $production->pad_number }}</dd></div>
                    <div><dt>Issue(s)</dt><dd>{{ $production->issue_numbers ?: '—' }}</dd></div>
                </dl>
                <p class="muted" style="margin-top:14px;white-space:pre-wrap">{{ $production->description ?: 'No notes.' }}</p>
            </div>
        </section>
    </div>

    {{-- ================= COST SIDEBAR ================= --}}
    <aside class="card summary-card">
        <div class="card-body">
            <p class="eyebrow">Batch costing</p>
            <h2>Cost breakdown</h2>
            <div class="summary-line"><span class="muted">Material</span><strong>{{ number_format($production->material_cost, 2) }}</strong></div>
            <div class="summary-line"><span class="muted">Packing &amp; label</span><strong>{{ number_format($production->packing_cost, 2) }}</strong></div>
            @if($production->rework_completed_at)
            <div class="summary-line"><span class="muted">Rework packing</span><strong>{{ number_format($production->rework_packing_cost, 2) }}</strong></div>
            @endif
            <div class="summary-line summary-total"><span>Total · ETB</span><strong>{{ number_format($production->total_batch_cost, 2) }}</strong></div>

            <div class="cost-block">
                <div class="summary-line"><span>Cost per good kg</span><strong>{{ $legacy ? '—' : number_format($costKg, 2) }}</strong></div>
                <p class="form-hint">Total ÷ (pure + NC). Loss is absorbed by the good material.</p>
            </div>

            @if($production->fg_posted_at)
            <div class="cost-block">
                <p class="small muted">Received in finished goods</p>
                <div class="summary-line"><span>Stage 1</span><strong>{{ number_format($production->stock_quantity, 2) }} kg @ {{ number_format($production->cost_per_unit, 2) }}</strong></div>
                @if($production->rework_stock_quantity)
                <div class="summary-line"><span>Rework</span><strong>{{ number_format($production->rework_stock_quantity, 2) }} kg @ {{ number_format($production->rework_cost_per_unit, 2) }}</strong></div>
                @endif
                @if($status === 'in_rework')
                <div class="summary-line"><span class="muted">Held in NC</span><strong>ETB {{ number_format($production->nc_value, 2) }}</strong></div>
                @endif
            </div>
            @endif

            @if($product)
            <div class="notice" style="margin-top:16px">
                <strong>{{ $product->name }}</strong><br>
                Stock now {{ number_format($product->quantity, 2) }} kg · avg cost ETB {{ number_format($product->cost_price, 2) }}/kg
            </div>
            @endif
        </div>
    </aside>
</div>
@endsection
