@extends('layouts.app')
@section('title', 'Production')
@section('content')
<div class="page-header">
    <div>
        <p class="eyebrow">Manufacturing</p>
        <h1 class="page-title">Production</h1>
        <p class="page-subtitle">Process issued materials, push pure output to finished goods and rework the NC.</p>
    </div>
    @if(auth()->user()->hasRole('admin','production') && $pending->isNotEmpty())
    <a href="{{ route('productions.create') }}" class="btn btn-primary">+ Submit results</a>
    @endif
</div>

<div class="metrics">
    <div class="metric amber"><span>Waiting to process</span><strong>{{ $pending->count() }}</strong><small>Issues from the store</small></div>
    <a class="metric blue" href="{{ route('productions.index', ['status' => 'recorded']) }}"><span>Ready to push to FG</span><strong>{{ $counts['recorded'] ?? 0 }}</strong><small>Results submitted</small></a>
    <a class="metric" href="{{ route('productions.index', ['status' => 'in_rework']) }}"><span>NC in rework</span><strong>{{ $counts['in_rework'] ?? 0 }}</strong><small>Waiting for rework results</small></a>
    <a class="metric teal" href="{{ route('productions.index', ['status' => 'completed']) }}"><span>Completed</span><strong>{{ $counts['completed'] ?? 0 }}</strong><small>Fully posted to stock</small></a>
</div>

@include('productions.partials.steps', ['current' => 0])

<div class="stack">
    <section class="card">
        <div class="card-header">
            <div><h2>Waiting to process <span class="badge badge-warning">{{ $pending->count() }}</span></h2><p class="card-subtitle">Materials issued by the store. Process one to submit its purity and impurity.</p></div>
            <a href="{{ route('issues.index') }}" class="btn btn-secondary btn-sm">All issues</a>
        </div>
        @if($pending->isEmpty())
            <div class="empty-state"><p class="empty-title">Nothing waiting</p><p>New issues appear here as soon as the store releases them.</p></div>
        @else
        <div class="queue-grid">
            @foreach($pending as $issue)
            <article class="queue-card">
                <div class="queue-top"><a class="mono" href="{{ route('issues.show', $issue) }}">{{ $issue->issue_number }}</a><span class="muted small">{{ $issue->created_at->format('d M Y') }}</span></div>
                <h3>{{ $issue->intendedProduct?->name ?? 'Ingredients for production' }}</h3>
                @foreach($issue->items as $item)
                <div class="ingredient-line"><span>{{ $item->rawMaterial?->name }}</span><span><strong>{{ number_format($item->quantity, 2) }} {{ $item->unit }}</strong> <span class="muted">× {{ number_format($item->unit_cost, 2) }}</span></span></div>
                @endforeach
                <div class="queue-bottom">
                    <div><span class="muted small">Material cost · ETB</span><br><strong>{{ number_format($issue->total_value, 2) }}</strong></div>
                    @if(auth()->user()->hasRole('admin','production'))
                    <a href="{{ route('productions.create', ['issue_id' => $issue->id]) }}" class="btn btn-primary">Process &rarr;</a>
                    @endif
                </div>
            </article>
            @endforeach
        </div>
        @endif
    </section>

    <section class="card">
        <div class="card-header">
            <div><h2>Batches</h2><p class="card-subtitle">Every batch keeps its issue number from start to finish.</p></div>
            <form method="GET" class="filter-form" action="{{ route('productions.index') }}">
                <input aria-label="Search" class="form-control" type="search" name="search" placeholder="Batch, issue or product…" value="{{ request('search') }}">
                <select aria-label="Status" class="form-control" name="status">
                    <option value="">All statuses</option>
                    <option value="recorded" @selected(request('status') === 'recorded')>Ready to push to FG</option>
                    <option value="in_rework" @selected(request('status') === 'in_rework')>NC in rework</option>
                    <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                </select>
                <button class="btn btn-secondary" type="submit">Filter</button>
                @if(request()->hasAny(['status', 'search']))<a class="btn btn-ghost" href="{{ route('productions.index') }}">Clear</a>@endif
            </form>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Batch</th><th>Issue</th><th>Finished good</th><th class="num">Purity</th><th class="num">NC</th><th class="num">Recovered</th><th>Status</th><th></th></tr></thead>
                <tbody>
                @forelse($productions as $batch)
                    @php $legacy = $batch->input_unit === null; @endphp
                    <tr>
                        <td><a class="mono" href="{{ route('productions.show', $batch) }}">{{ $batch->production_number }}</a><div class="muted small">{{ $batch->created_at->format('d M Y') }}</div></td>
                        <td class="mono">{{ $batch->issue_numbers ?: '—' }}</td>
                        <td>{{ $batch->product?->name ?? '—' }}</td>
                        <td class="num"><strong>{{ number_format($batch->output_quantity, 2) }}</strong>{{ $legacy ? '' : ' kg' }}<div class="muted small">{{ number_format($batch->purity * 100, 1) }}%</div></td>
                        <td class="num">{{ number_format($batch->non_conforming, 2) }}{{ $legacy ? '' : ' kg' }}<div class="muted small">{{ number_format($batch->impurity * 100, 1) }}%</div></td>
                        <td class="num">{{ $batch->rework_output !== null ? number_format($batch->rework_output, 2).' kg' : '—' }}</td>
                        <td><span class="badge badge-{{ $batch->status_color }}">{{ $batch->status_label }}</span></td>
                        <td class="num">
                            @if(in_array($batch->status, ['recorded','in_progress']) && ! $legacy)
                                <a class="btn btn-primary btn-sm" href="{{ route('productions.show', $batch) }}">Push to FG</a>
                            @elseif($batch->status === 'in_rework')
                                <a class="btn btn-primary btn-sm" href="{{ route('productions.rework', $batch) }}">Submit rework</a>
                            @else
                                <a class="btn btn-secondary btn-sm" href="{{ route('productions.show', $batch) }}">View</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8"><div class="empty-state"><p class="empty-title">No batches found</p></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($productions->hasPages())<div class="pagination-wrap">{{ $productions->links() }}</div>@endif
    </section>
</div>
@endsection
