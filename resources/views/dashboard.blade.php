@extends('layouts.app')
@section('title','Dashboard')

@section('content')

{{-- Welcome --}}
<div class="welcome">
    <div>
        <p class="eyebrow" style="color:#5eead4">{{ now()->format('l, d F Y') }}</p>
        <h1>Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ strtok(auth()->user()->name, ' ') }}</h1>
        <p>Here is what is happening across your business today.</p>
    </div>
    <div class="welcome-actions">
        @if(auth()->user()->hasRole('admin','cashier'))<a href="{{ route('sales.create') }}" class="btn btn-primary">+ New sale</a>@endif
        @if(auth()->user()->hasRole('admin','inventory'))<a href="{{ route('issues.create') }}" class="btn">Issue materials</a>@endif
        @if(auth()->user()->hasRole('admin','production'))<a href="{{ route('productions.index') }}" class="btn">Production</a>@endif
    </div>
</div>

{{-- KPI Grid --}}
<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-top">
            <div class="kpi-icon green"><svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/><path d="M6 12h.01M18 12h.01"/></svg></div>
            <span class="kpi-badge up">Today</span>
        </div>
        <div class="kpi-label">Revenue Today</div>
        <div class="kpi-value">${{ number_format($stats['revenue_today'],2) }}</div>
        <div class="kpi-sub">{{ $stats['sales_today'] }} order(s) processed</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-top">
            <div class="kpi-icon blue"><svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="M7 15l4-4 3 3 5-6"/></svg></div>
            <span class="kpi-badge flat">{{ now()->format('M Y') }}</span>
        </div>
        <div class="kpi-label">Monthly Revenue</div>
        <div class="kpi-value">${{ number_format($stats['revenue_month'],2) }}</div>
        <div class="kpi-sub">Total completed sales this month</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-top">
            <div class="kpi-icon purple"><svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg></div>
            <span class="kpi-badge flat">Total</span>
        </div>
        <div class="kpi-label">Customers</div>
        <div class="kpi-value">{{ $stats['customers'] }}</div>
        <div class="kpi-sub">{{ $stats['suppliers'] }} active suppliers</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-top">
            <div class="kpi-icon amber"><svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 8l-9-5-9 5v8l9 5 9-5V8z"/><path d="M3 8l9 5 9-5M12 13v8"/></svg></div>
            @if($stats['low_stock'] > 0)
                <span class="kpi-badge down">{{ $stats['low_stock'] }} low</span>
            @else
                <span class="kpi-badge up">OK</span>
            @endif
        </div>
        <div class="kpi-label">Products</div>
        <div class="kpi-value">{{ $stats['products'] }}</div>
        <div class="kpi-sub">Value: ${{ number_format($stats['inventory_value'],0) }}</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-top">
            <div class="kpi-icon cyan"><svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0116 0"/></svg></div>
            <span class="kpi-badge flat">Active</span>
        </div>
        <div class="kpi-label">Employees</div>
        <div class="kpi-value">{{ $stats['employees'] }}</div>
        <div class="kpi-sub">{{ $stats['attendance_today'] }} present today</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-top">
            <div class="kpi-icon pink"><svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg></div>
            @if($stats['payroll_pending'] > 0)
                <span class="kpi-badge down">{{ $stats['payroll_pending'] }} pending</span>
            @else
                <span class="kpi-badge up">Cleared</span>
            @endif
        </div>
        <div class="kpi-label">Payroll (Month)</div>
        <div class="kpi-value">${{ number_format($stats['payroll_month'],0) }}</div>
        <div class="kpi-sub">{{ $stats['payroll_pending'] }} records pending payment</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-top">
            <div class="kpi-icon indigo"><svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 12h6M9 16h4"/></svg></div>
            @if($stats['po_pending'] > 0)
                <span class="kpi-badge down">{{ $stats['po_pending'] }} open</span>
            @else
                <span class="kpi-badge up">None</span>
            @endif
        </div>
        <div class="kpi-label">Purchase Orders</div>
        <div class="kpi-value">{{ $stats['po_pending'] }}</div>
        <div class="kpi-sub">Draft / sent orders pending</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-top">
            <div class="kpi-icon green"><svg fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M23 6l-9.5 9.5-5-5L1 18"/><path d="M17 6h6v6"/></svg></div>
            <span class="kpi-badge flat">All time</span>
        </div>
        <div class="kpi-label">Total Revenue</div>
        <div class="kpi-value">${{ number_format($stats['total_sales'],0) }}</div>
        <div class="kpi-sub">From all completed sales</div>
    </div>
</div>

{{-- Charts Row --}}
<div class="charts-row">
    {{-- Revenue Trend --}}
    <div class="chart-card">
        <h3>Revenue Trend</h3>
        <div class="chart-sub">Last 6 months</div>
        <div class="chart-wrap">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>
    {{-- Sales by Category --}}
    <div class="chart-card">
        <h3>Sales by Category</h3>
        <div class="chart-sub">Top categories</div>
        <div class="chart-wrap">
            <canvas id="categoryChart"></canvas>
        </div>
    </div>
</div>

{{-- Activity Grid --}}
<div class="dash-grid">
    {{-- Recent Sales --}}
    <div class="card">
        <div class="card-header">
            <h3>Recent Sales</h3>
            <a href="{{ route('sales.index') }}" class="btn btn-sm">View All</a>
        </div>
        <div class="table-wrap">
            <table class="tbl">
                <thead><tr><th>Invoice</th><th>Customer</th><th>Total</th><th>Status</th></tr></thead>
                <tbody>
                @forelse($recentSales as $sale)
                <tr>
                    <td><a href="{{ route('sales.show',$sale) }}" class="strong">{{ $sale->invoice_no }}</a></td>
                    <td>{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                    <td class="strong">${{ number_format($sale->total,2) }}</td>
                    <td><span class="badge {{ $sale->balance <= 0 ? 'badge-green' : 'badge-amber' }}">{{ $sale->balance <= 0 ? 'Paid' : 'Partial' }}</span></td>
                </tr>
                @empty
                <tr><td colspan="4" class="muted">No sales yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Low Stock --}}
    <div class="card">
        <div class="card-header">
            <h3>Low Stock Alerts</h3>
            <a href="{{ route('products.index') }}" class="btn btn-sm">View All</a>
        </div>
        <div class="table-wrap">
            <table class="tbl">
                <thead><tr><th>Product</th><th>Qty</th><th>Min</th></tr></thead>
                <tbody>
                @forelse($lowStockProducts as $p)
                <tr>
                    <td>
                        <div class="strong">{{ $p->name }}</div>
                        <div class="muted text-sm">{{ $p->category?->name }}</div>
                    </td>
                    <td><span class="badge {{ $p->quantity == 0 ? 'badge-red' : 'badge-amber' }}">{{ $p->quantity_display }}</span></td>
                    <td class="muted">{{ $p->low_stock_threshold }}</td>
                </tr>
                @empty
                <tr><td colspan="3" class="muted">All items well-stocked.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Today's Attendance --}}
    <div class="card">
        <div class="card-header">
            <h3>Today's Attendance</h3>
            <a href="{{ route('attendances.index') }}" class="btn btn-sm">View All</a>
        </div>
        <div class="table-wrap">
            <table class="tbl">
                <thead><tr><th>Employee</th><th>Check-in</th><th>Status</th></tr></thead>
                <tbody>
                @forelse($recentAttendance as $att)
                <tr>
                    <td class="strong">{{ $att->employee?->full_name ?? '—' }}</td>
                    <td class="muted">{{ $att->check_in ? \Carbon\Carbon::parse($att->check_in)->format('H:i') : '—' }}</td>
                    <td>
                        @php
                            $badgeClass = match($att->status) {
                                'present' => 'badge-green',
                                'absent'  => 'badge-red',
                                'late'    => 'badge-amber',
                                default   => 'badge-gray',
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ ucfirst($att->status) }}</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" class="muted">No attendance recorded today.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pending Payroll --}}
    <div class="card">
        <div class="card-header">
            <h3>Pending Payroll</h3>
            <a href="{{ route('payrolls.index') }}" class="btn btn-sm">View All</a>
        </div>
        <div class="table-wrap">
            <table class="tbl">
                <thead><tr><th>Employee</th><th>Period</th><th>Net</th></tr></thead>
                <tbody>
                @forelse($pendingPayroll as $p)
                <tr>
                    <td class="strong">{{ $p->employee?->full_name ?? '—' }}</td>
                    <td class="muted">{{ $p->payroll_period }}</td>
                    <td class="strong">${{ number_format($p->net_salary,2) }}</td>
                </tr>
                @empty
                <tr><td colspan="3" class="muted">No pending payroll.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function() {
    // Revenue trend chart
    const rCtx = document.getElementById('revenueChart');
    if (rCtx) {
        const months = @json($revenueChart->pluck('month'));
        const totals = @json($revenueChart->pluck('total'));
        Chart.defaults.font.family = 'Inter, system-ui, sans-serif'; Chart.defaults.color = '#74859a';
        new Chart(rCtx, {
            type: 'bar',
            data: {
                labels: months,
                datasets: [{
                    label: 'Revenue ($)',
                    data: totals,
                    backgroundColor: 'rgba(11,127,120,0.18)',
                    borderColor: '#0b7f78',
                    borderWidth: 2,
                    borderRadius: 6,
                    fill: true,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { callback: v => '$'+v.toLocaleString() } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // Category pie chart
    const cCtx = document.getElementById('categoryChart');
    if (cCtx) {
        @php
            $catNames = $categoryChart->pluck('name');
            $catTotals = $categoryChart->pluck('total');
        @endphp
        const catNames  = @json($catNames);
        const catTotals = @json($catTotals);
        new Chart(cCtx, {
            type: 'doughnut',
            data: {
                labels: catNames.length ? catNames : ['No data'],
                datasets: [{
                    data: catTotals.length ? catTotals : [1],
                    backgroundColor: ['#0b7f78','#2dd4bf','#e0a23a','#6c4bd1','#2768ab'],
                    borderWidth: 2,
                    borderColor: '#fff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } },
                cutout: '65%',
            }
        });
    }
})();
</script>
@endpush
