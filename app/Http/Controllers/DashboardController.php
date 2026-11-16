<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today        = now()->toDateString();
        $currentMonth = now()->format('F Y');

        // KPI stats
        $stats = [
            'products'        => Product::count(),
            'customers'       => Customer::count(),
            'suppliers'       => Supplier::where('status','active')->count(),
            'employees'       => Employee::where('status','active')->count(),
            'low_stock'       => Product::whereColumn('quantity','<=','low_stock_threshold')->count(),
            'inventory_value' => Product::sum(DB::raw('quantity * cost_price')),
            'sales_today'     => Sale::whereDate('created_at',$today)->count(),
            'revenue_today'   => Sale::whereDate('created_at',$today)->where('status','completed')->sum('total'),
            'revenue_month'   => Sale::whereMonth('created_at',now()->month)->whereYear('created_at',now()->year)->where('status','completed')->sum('total'),
            'total_sales'     => Sale::where('status','completed')->sum('total'),
            'attendance_today'=> Attendance::whereDate('date',$today)->where('status','present')->count(),
            'payroll_pending' => Payroll::where('status','pending')->count(),
            'payroll_month'   => Payroll::where('payroll_period',$currentMonth)->sum('net_salary'),
            'po_pending'      => PurchaseOrder::whereIn('status',['draft','sent'])->count(),
        ];

        // Recent items
        $recentSales      = Sale::with(['customer','user'])->latest()->limit(6)->get();
        $lowStockProducts = Product::with('category')->whereColumn('quantity','<=','low_stock_threshold')->orderBy('quantity')->limit(6)->get();
        $recentAttendance = Attendance::with('employee')->whereDate('date',$today)->latest()->limit(6)->get();
        $pendingPayroll   = Payroll::with('employee')->where('status','pending')->latest()->limit(5)->get();

        // Last 6 months revenue chart data
        $revenueChart = collect(range(5,0))->map(function($i) {
            $date  = now()->subMonths($i);
            $total = Sale::whereMonth('created_at',$date->month)
                ->whereYear('created_at',$date->year)
                ->where('status','completed')
                ->sum('total');
            return ['month' => $date->format('M'), 'total' => (float) $total];
        });

        // Top 5 categories by sales value
        $categoryChart = DB::table('sale_items')
            ->join('products','sale_items.product_id','products.id')
            ->join('categories','products.category_id','categories.id')
            ->select('categories.name', DB::raw('SUM(sale_items.total) as total'))
            ->groupBy('categories.id','categories.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'stats','recentSales','lowStockProducts','recentAttendance',
            'pendingPayroll','revenueChart','categoryChart'
        ));
    }
}
