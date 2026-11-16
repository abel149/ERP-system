@extends('layouts.app')
@section('title', 'Material Issues')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Material Issues</h1>
        <p class="page-subtitle">Track materials issued from store to production</p>
    </div>
    @if(auth()->user()->hasRole('admin','inventory'))
    <a href="{{ route('issues.create') }}" class="btn btn-primary">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        New Issue
    </a>
    @endif
</div>

{{-- Stats strip --}}
<div class="stats-row">
    <div class="stat-card">
        <div class="stat-value">{{ $issues->total() }}</div>
        <div class="stat-label">Total Issues</div>
    </div>
    <div class="stat-card">
        <div class="stat-value text-warning">{{ $counts['issued'] ?? 0 }}</div>
        <div class="stat-label">Awaiting Production</div>
    </div>
    <div class="stat-card">
        <div class="stat-value text-success">{{ $counts['consumed'] ?? 0 }}</div>
        <div class="stat-label">Consumed</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Issues</h3>
    </div>
    <div class="card-body p-0">
        @if($issues->isEmpty())
            <div class="empty-state">
                <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <p class="empty-title">No issues yet</p>
                <p class="empty-text">Issue materials from the store to start a production batch.</p>
                @if(auth()->user()->hasRole('admin','inventory'))
                <a href="{{ route('issues.create') }}" class="btn btn-primary mt-3">Create First Issue</a>
                @endif
            </div>
        @else
        <table class="table">
            <thead>
                <tr>
                    <th>Issue #</th>
                    <th>Materials</th>
                    <th>Lines</th>
                    <th>Total Value</th>
                    <th>Intended Product</th>
                    <th>Pad</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @foreach($issues as $issue)
            <tr>
                <td><span class="mono font-semibold">{{ $issue->issue_number }}</span></td>
                <td>
                    <div class="ingredients-list">
                    @forelse($issue->items as $item)
                        <span class="ingredient-pill">
                            {{ $item->rawMaterial->name ?? 'Unknown' }}
                            <span class="text-muted">{{ number_format($item->quantity, 2) }} {{ $item->unit }}</span>
                        </span>
                    @empty
                        {{-- legacy single-material --}}
                        @if($issue->rawMaterial)
                        <span class="ingredient-pill">{{ $issue->rawMaterial->name }}</span>
                        @else
                        <span class="text-muted">—</span>
                        @endif
                    @endforelse
                    </div>
                </td>
                <td class="text-right font-semibold">{{ $issue->items->count() }}</td>
                <td class="text-right">{{ number_format($issue->total_value, 2) }}</td>
                <td>{{ $issue->intendedProduct->name ?? '—' }}</td>
                <td><span class="mono text-sm">{{ $issue->pad_type }}-{{ $issue->pad_number }}</span></td>
                <td>
                    @if($issue->status === 'issued')
                        <span class="badge badge-warning">Awaiting Production</span>
                    @elseif($issue->status === 'consumed')
                        <span class="badge badge-success">Consumed</span>
                    @else
                        <span class="badge badge-secondary">{{ ucfirst($issue->status) }}</span>
                    @endif
                </td>
                <td class="text-muted text-sm">{{ $issue->created_at->format('d M Y') }}</td>
                <td>
                    <div class="action-group">
                        <a href="{{ route('issues.show', $issue) }}" class="btn btn-xs btn-secondary">View</a>
                        @if($issue->status === 'issued' && auth()->user()->hasRole('admin','inventory'))
                        <form method="POST" action="{{ route('issues.destroy', $issue) }}" onsubmit="return confirm('Cancel this issue and return stock?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-xs btn-danger">Cancel</button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
        <div class="pagination-wrap">{{ $issues->links() }}</div>
        @endif
    </div>
</div>

@push('head')
<style>
.stats-row { display:flex; gap:1rem; margin-bottom:1.5rem; flex-wrap:wrap; }
.stat-card { background:var(--card); border-radius:var(--radius); padding:1.25rem 1.5rem; box-shadow:var(--shadow); min-width:140px; flex:1; }
.stat-value { font-size:1.75rem; font-weight:700; line-height:1; margin-bottom:.25rem; }
.stat-label { font-size:.75rem; color:var(--text-2); text-transform:uppercase; letter-spacing:.05em; }
.ingredients-list { display:flex; flex-wrap:wrap; gap:.25rem; }
.ingredient-pill { display:inline-flex; align-items:center; gap:.35rem; background:var(--accent-lt); color:var(--accent); border-radius:999px; padding:.15rem .6rem; font-size:.75rem; font-weight:500; }
.ingredient-pill .text-muted { color:var(--text-2); }
.text-right { text-align:right; }
.text-warning { color:var(--warning); }
.text-success { color:var(--success); }
.font-semibold { font-weight:600; }
.mono { font-family:monospace; }
.text-sm { font-size:.8rem; }
.mt-3 { margin-top:.75rem; }
.pagination-wrap { padding:1rem 1.5rem; }
.action-group { display:flex; gap:.35rem; }
</style>
@endpush
@endsection
