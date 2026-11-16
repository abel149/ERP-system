@extends('layouts.app')
@section('title','Inventory Report')
@section('content')

<div class="page-header">
    <div class="page-header-left">
        <h1>Inventory Report</h1>
        <p>Stock levels, valuation, and category breakdown</p>
    </div>
    <div class="page-header-actions no-print">
        <a href="{{ route('reports.sales') }}" class="btn">Sales Report</a>
        <a href="{{ route('reports.hr') }}" class="btn">HR Report</a>
        <button onclick="window.print()" class="btn btn-primary">Print</button>
    </div>
</div>

<div class="kpi-grid" style="grid-template-columns:repeat(4,1fr);">
    <div class="kpi-card">
        <div class="kpi-top"><div class="kpi-icon blue">&#128230;</div></div>
        <div class="kpi-label">Total Products</div>
        <div class="kpi-value">{{ $summary['total_products'] }}</div>
        <div class="kpi-sub">{{ $summary['categories'] }} categories</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-top"><div class="kpi-icon green">&#128176;</div></div>
        <div class="kpi-label">Cost Value</div>
        <div class="kpi-value">${{ number_format($summary['total_value'],0) }}</div>
        <div class="kpi-sub">At purchase cost</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-top"><div class="kpi-icon purple">&#128200;</div></div>
        <div class="kpi-label">Sell Value</div>
        <div class="kpi-value">${{ number_format($summary['sell_value'],0) }}</div>
        <div class="kpi-sub">At selling price</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-top"><div class="kpi-icon {{ $summary['low_stock'] > 0 ? 'amber' : 'green' }}">&#9888;</div></div>
        <div class="kpi-label">Low / Out of Stock</div>
        <div class="kpi-value">{{ $summary['low_stock'] }} / {{ $summary['out_of_stock'] }}</div>
        <div class="kpi-sub">Items needing restock</div>
    </div>
</div>

<div class="dash-grid mb-3">
    {{-- Category Breakdown --}}
    <div class="card">
        <div class="card-header"><h3>Stock by Category</h3></div>
        <div class="table-wrap">
            <table class="tbl">
                <thead><tr><th>Category</th><th class="text-right">Products</th><th class="text-right">Total Qty</th><th class="text-right">Value</th></tr></thead>
                <tbody>
                @foreach($stockByCategory as $cat)
                <tr>
                    <td class="strong">{{ $cat->name }}</td>
                    <td class="text-right">{{ $cat->product_count }}</td>
                    <td class="text-right">{{ $cat->total_qty }}</td>
                    <td class="text-right strong">${{ number_format($cat->value,2) }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Chart --}}
    <div class="chart-card">
        <h3>Value by Category</h3>
        <div class="chart-sub">Cost value distribution</div>
        <div class="chart-wrap">
            <canvas id="catChart"></canvas>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3>All Products</h3></div>
    <div class="table-wrap">
        <table class="tbl">
            <thead>
                <tr><th>Product</th><th>SKU</th><th>Category</th>
                    <th class="text-right">Qty</th><th class="text-right">Cost</th>
                    <th class="text-right">Sell</th><th class="text-right">Value</th><th>Status</th></tr>
            </thead>
            <tbody>
            @foreach($products as $p)
            @php $isLow = $p->quantity <= $p->low_stock_threshold; @endphp
            <tr>
                <td class="strong">{{ $p->name }}</td>
                <td class="muted">{{ $p->sku }}</td>
                <td class="muted">{{ $p->category?->name ?? '—' }}</td>
                <td class="text-right">
                    <span class="badge {{ $p->quantity == 0 ? 'badge-red' : ($isLow ? 'badge-amber' : 'badge-green') }}">{{ $p->quantity_display }}</span>
                </td>
                <td class="text-right">${{ number_format($p->cost_price,2) }}</td>
                <td class="text-right">${{ number_format($p->sell_price,2) }}</td>
                <td class="text-right strong">${{ number_format($p->quantity * $p->cost_price,2) }}</td>
                <td><span class="badge {{ $p->is_active ? 'badge-green' : 'badge-gray' }}">{{ $p->is_active ? 'Active' : 'Inactive' }}</span></td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $products->links() }}</div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const ctx = document.getElementById('catChart');
if (ctx) {
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: @json($stockByCategory->pluck('name')),
            datasets: [{ data: @json($stockByCategory->pluck('value')),
                backgroundColor: ['#2563eb','#16a34a','#d97706','#7c3aed','#0891b2','#ec4899'],
                borderWidth: 2, borderColor: '#fff' }]
        },
        options: { responsive: true, maintainAspectRatio: false, cutout: '60%',
            plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } } }
    });
}
</script>
@endpush
@endsection
