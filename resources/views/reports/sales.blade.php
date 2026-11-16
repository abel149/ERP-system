@extends('layouts.app')
@section('title','Sales Report')
@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>Sales Report</h1>
        <p>Revenue analysis and sales performance</p>
    </div>
    <div class="page-header-actions no-print">
        <a href="{{ route('reports.inventory') }}" class="btn">Inventory Report</a>
        <a href="{{ route('reports.hr') }}" class="btn">HR Report</a>
        <button onclick="window.print()" class="btn btn-primary">Print</button>
    </div>
</div>

<form method="GET" class="filter-bar mb-2 no-print">
    <div class="field">
        <label>From</label>
        <input type="date" name="from" value="{{ $from }}">
    </div>
    <div class="field">
        <label>To</label>
        <input type="date" name="to" value="{{ $to }}">
    </div>
    <div style="margin-top:18px;">
        <button type="submit" class="btn btn-primary">Apply</button>
    </div>
</form>

{{-- Summary KPIs --}}
<div class="kpi-grid" style="grid-template-columns:repeat(4,1fr);">
    <div class="kpi-card">
        <div class="kpi-top"><div class="kpi-icon green">&#128176;</div></div>
        <div class="kpi-label">Total Revenue</div>
        <div class="kpi-value">${{ number_format($summary->revenue ?? 0,2) }}</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-top"><div class="kpi-icon blue">&#128203;</div></div>
        <div class="kpi-label">Orders</div>
        <div class="kpi-value">{{ $summary->count ?? 0 }}</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-top"><div class="kpi-icon amber">&#127383;</div></div>
        <div class="kpi-label">Discounts Given</div>
        <div class="kpi-value">${{ number_format($summary->discounts ?? 0,2) }}</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-top"><div class="kpi-icon purple">&#128200;</div></div>
        <div class="kpi-label">Avg Order Value</div>
        <div class="kpi-value">${{ $summary->count > 0 ? number_format($summary->revenue / $summary->count,2) : '0.00' }}</div>
    </div>
</div>

<div class="dash-grid mb-3">
    {{-- Daily Revenue Chart --}}
    <div class="chart-card">
        <h3>Daily Revenue</h3>
        <div class="chart-sub">{{ \Carbon\Carbon::parse($from)->format('M d') }} – {{ \Carbon\Carbon::parse($to)->format('M d, Y') }}</div>
        <div class="chart-wrap">
            <canvas id="dailyChart"></canvas>
        </div>
    </div>

    {{-- Top Products --}}
    <div class="card">
        <div class="card-header"><h3>Top Products</h3></div>
        <div class="table-wrap">
            <table class="tbl">
                <thead><tr><th>Product</th><th class="text-right">Qty</th><th class="text-right">Revenue</th></tr></thead>
                <tbody>
                @forelse($topProducts as $p)
                <tr>
                    <td><div class="strong">{{ $p->name }}</div><div class="muted text-sm">{{ $p->sku }}</div></td>
                    <td class="text-right">{{ $p->qty_sold }}</td>
                    <td class="text-right strong">${{ number_format($p->revenue,2) }}</td>
                </tr>
                @empty
                <tr><td colspan="3" class="muted">No data.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Sales List --}}
<div class="card">
    <div class="card-header"><h3>Sales Transactions</h3></div>
    <div class="table-wrap">
        <table class="tbl">
            <thead>
                <tr><th>Invoice</th><th>Customer</th><th>Cashier</th>
                    <th class="text-right">Total</th><th>Payment</th><th>Date</th></tr>
            </thead>
            <tbody>
            @forelse($sales as $sale)
            <tr>
                <td><a href="{{ route('sales.show',$sale) }}" class="strong">{{ $sale->invoice_no }}</a></td>
                <td>{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                <td class="muted">{{ $sale->user->name }}</td>
                <td class="text-right strong">${{ number_format($sale->total,2) }}</td>
                <td><span class="badge badge-gray">{{ ucfirst(str_replace('_',' ',$sale->payment_method)) }}</span></td>
                <td class="muted">{{ $sale->created_at->format('M d, Y') }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="muted" style="padding:20px;text-align:center;">No sales in this period.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $sales->appends(request()->query())->links() }}</div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const dailyCtx = document.getElementById('dailyChart');
if (dailyCtx) {
    new Chart(dailyCtx, {
        type: 'line',
        data: {
            labels: @json($daily->pluck('date')),
            datasets: [{
                label: 'Revenue',
                data: @json($daily->pluck('revenue')),
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37,99,235,0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.3,
                pointRadius: 3,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { callback: v => '$'+v.toLocaleString() }, grid: { color: '#f1f5f9' } },
                x: { grid: { display: false } }
            }
        }
    });
}
</script>
@endpush
@endsection
