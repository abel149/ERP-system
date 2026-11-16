@extends('layouts.app')
@section('title', $issue->issue_number)

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">{{ $issue->issue_number }}</h1>
        <p class="page-subtitle">Material Issue — {{ $issue->created_at->format('d M Y, H:i') }}</p>
    </div>
    <div style="display:flex;gap:.5rem">
        <a href="{{ route('issues.index') }}" class="btn btn-secondary">← Back</a>
        @if($issue->status === 'issued' && auth()->user()->hasRole('admin','production'))
        <a href="{{ route('productions.create', ['issue_id' => $issue->id]) }}" class="btn btn-primary">▶ Start Production Batch</a>
        @endif
    </div>
</div>

<div class="detail-grid">

    {{-- Status banner --}}
    <div class="card full-width
        @if($issue->status==='issued') border-warning @elseif($issue->status==='consumed') border-success @endif">
        <div class="card-body status-banner">
            <div class="status-info">
                @if($issue->status === 'issued')
                    <span class="status-dot dot-warning"></span>
                    <div>
                        <div class="status-label">Awaiting Production</div>
                        <div class="status-sub">Materials are ready to be consumed by a production batch</div>
                    </div>
                @elseif($issue->status === 'consumed')
                    <span class="status-dot dot-success"></span>
                    <div>
                        <div class="status-label">Consumed by Production</div>
                        @if($issue->productionItem?->production)
                        <div class="status-sub">
                            Linked to batch
                            <a href="{{ route('productions.show', $issue->productionItem->production) }}" class="mono">
                                {{ $issue->productionItem->production->production_number }}
                            </a>
                        </div>
                        @endif
                    </div>
                @endif
            </div>
            <div class="summary-chips">
                <div class="chip">
                    <span class="chip-label">Material lines</span>
                    <span class="chip-value">{{ $issue->items->count() }}</span>
                </div>
                <div class="chip">
                    <span class="chip-label">Total Value</span>
                    <span class="chip-value">{{ number_format($issue->total_value, 2) }}</span>
                </div>
                <div class="chip">
                    <span class="chip-label">Pad</span>
                    <span class="chip-value mono">{{ $issue->pad_type }}-{{ $issue->pad_number }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Ingredient lines --}}
    <div class="card full-width">
        <div class="card-header"><h3 class="card-title">Ingredients Issued</h3></div>
        <div class="card-body p-0">
            @if($issue->items->isNotEmpty())
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Material</th>
                        <th>SKU</th>
                        <th class="text-right">Quantity</th>
                        <th class="text-right">Unit Cost (snapshot)</th>
                        <th class="text-right">Line Total</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($issue->items as $i => $item)
                <tr>
                    <td class="text-muted">{{ $i+1 }}</td>
                    <td>
                        <div class="font-semibold">{{ $item->rawMaterial->name ?? 'Unknown' }}</div>
                    </td>
                    <td><span class="mono text-sm">{{ $item->rawMaterial->sku ?? '—' }}</span></td>
                    <td class="text-right">{{ number_format($item->quantity, 2) }} {{ $item->unit }}</td>
                    <td class="text-right">{{ number_format($item->unit_cost, 2) }}</td>
                    <td class="text-right font-semibold">{{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr class="tfoot-total">
                        <td colspan="5" class="text-right font-semibold">Total Value</td>
                        <td class="text-right font-semibold text-accent">{{ number_format($issue->total_value, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
            @else
            {{-- Legacy single-material fallback --}}
            @if($issue->rawMaterial)
            <div class="p-4">
                <span class="ingredient-pill">{{ $issue->rawMaterial->name }} — {{ number_format($issue->quantity, 2) }}</span>
            </div>
            @else
            <div class="empty-state"><p class="empty-text">No ingredient lines recorded.</p></div>
            @endif
            @endif
        </div>
    </div>

    {{-- Notes --}}
    @if($issue->description || $issue->intended_product_id)
    <div class="card half-width">
        <div class="card-header"><h3 class="card-title">Details</h3></div>
        <div class="card-body">
            @if($issue->intendedProduct)
            <div class="detail-row">
                <span class="detail-key">Intended Product</span>
                <span class="detail-val">{{ $issue->intendedProduct->name }}</span>
            </div>
            @endif
            @if($issue->description)
            <div class="detail-row">
                <span class="detail-key">Notes</span>
                <span class="detail-val">{{ $issue->description }}</span>
            </div>
            @endif
        </div>
    </div>
    @endif

    @if($issue->status === 'issued' && auth()->user()->hasRole('admin','inventory'))
    <div class="card half-width border-danger-subtle">
        <div class="card-header"><h3 class="card-title text-danger">Cancel Issue</h3></div>
        <div class="card-body">
            <p class="text-muted text-sm mb-3">Cancelling returns all ingredient quantities to raw-material stock. This cannot be done after a production batch consumes it.</p>
            <form method="POST" action="{{ route('issues.destroy', $issue) }}"
                  onsubmit="return confirm('Cancel issue {{ $issue->issue_number }} and return all stock?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger">Cancel Issue &amp; Return Stock</button>
            </form>
        </div>
    </div>
    @endif

</div>

@push('head')
<style>
.detail-grid { display:grid; grid-template-columns:1fr 1fr; gap:1.25rem; }
.full-width { grid-column:1/-1; }
.half-width { grid-column:span 1; }
@media(max-width:700px){ .detail-grid{grid-template-columns:1fr;} .half-width{grid-column:1/-1;} }
.status-banner { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; }
.status-info { display:flex; align-items:center; gap:.75rem; }
.status-dot { width:12px; height:12px; border-radius:50%; flex-shrink:0; }
.dot-warning { background:var(--warning); }
.dot-success { background:var(--success); }
.status-label { font-weight:600; font-size:1rem; }
.status-sub { font-size:.8rem; color:var(--text-2); margin-top:.15rem; }
.summary-chips { display:flex; gap:.5rem; flex-wrap:wrap; }
.chip { background:var(--bg); border:1px solid var(--border); border-radius:var(--radius); padding:.4rem .75rem; text-align:center; min-width:80px; }
.chip-label { display:block; font-size:.65rem; text-transform:uppercase; letter-spacing:.05em; color:var(--text-2); }
.chip-value { font-size:.9rem; font-weight:600; }
.border-warning { border-left:4px solid var(--warning); }
.border-success { border-left:4px solid var(--success); }
.border-danger-subtle { border-left:4px solid var(--danger); }
.text-right { text-align:right; }
.text-accent { color:var(--accent); }
.text-danger { color:var(--danger); }
.font-semibold { font-weight:600; }
.mono { font-family:monospace; }
.text-sm { font-size:.8rem; }
.text-muted { color:var(--text-2); }
.mb-3 { margin-bottom:.75rem; }
.p-4 { padding:1rem; }
.tfoot-total td { background:var(--bg); border-top:2px solid var(--border); }
.detail-row { display:flex; justify-content:space-between; padding:.5rem 0; border-bottom:1px solid var(--border-2); }
.detail-row:last-child { border-bottom:none; }
.detail-key { color:var(--text-2); font-size:.85rem; }
.detail-val { font-weight:500; text-align:right; }
.ingredient-pill { display:inline-flex; align-items:center; gap:.35rem; background:var(--accent-lt); color:var(--accent); border-radius:999px; padding:.3rem .8rem; font-size:.85rem; font-weight:500; }
</style>
@endpush
@endsection
