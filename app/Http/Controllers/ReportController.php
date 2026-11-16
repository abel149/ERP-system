<?php
namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function sales(Request $request)
    {
        $from  = $request->input('from', now()->startOfMonth()->toDateString());
        $to    = $request->input('to',   now()->toDateString());

        $sales = Sale::with(['customer','user'])
            ->where('status','completed')
            ->whereDate('created_at','>=',$from)
            ->whereDate('created_at','<=',$to)
            ->latest()->paginate(20);

        $summary = Sale::where('status','completed')
            ->whereDate('created_at','>=',$from)
            ->whereDate('created_at','<=',$to)
            ->selectRaw('COUNT(*) as count, SUM(total) as revenue, SUM(discount) as discounts, SUM(tax) as taxes')
            ->first();

        // Daily breakdown for chart
        $daily = Sale::where('status','completed')
            ->whereDate('created_at','>=',$from)
            ->whereDate('created_at','<=',$to)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(total) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top products
        $topProducts = DB::table('sale_items')
            ->join('sales','sale_items.sale_id','sales.id')
            ->join('products','sale_items.product_id','products.id')
            ->where('sales.status','completed')
            ->whereDate('sales.created_at','>=',$from)
            ->whereDate('sales.created_at','<=',$to)
            ->select('products.name','products.sku',
                DB::raw('SUM(sale_items.quantity) as qty_sold'),
                DB::raw('SUM(sale_items.total) as revenue'))
            ->groupBy('products.id','products.name','products.sku')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        return view('reports.sales', compact('sales','summary','daily','topProducts','from','to'));
    }

    public function inventory()
    {
        $products = Product::with('category')
            ->withCount('saleItems as times_sold')
            ->withSum('saleItems as units_sold', 'quantity')
            ->orderByDesc('quantity')
            ->paginate(20);

        $summary = [
            'total_products'   => Product::count(),
            'total_value'      => Product::sum(DB::raw('quantity * cost_price')),
            'sell_value'       => Product::sum(DB::raw('quantity * sell_price')),
            'low_stock'        => Product::whereColumn('quantity','<=','low_stock_threshold')->count(),
            'out_of_stock'     => Product::where('quantity',0)->count(),
            'categories'       => Product::distinct('category_id')->count('category_id'),
        ];

        $stockByCategory = DB::table('products')
            ->join('categories','products.category_id','categories.id')
            ->select('categories.name', DB::raw('COUNT(*) as product_count'), DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(quantity * cost_price) as value'))
            ->groupBy('categories.id','categories.name')
            ->orderByDesc('value')
            ->get();

        return view('reports.inventory', compact('products','summary','stockByCategory'));
    }

    public function hr(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));
        [$y,$m] = explode('-', $month);

        $attendance = Attendance::with('employee')
            ->whereYear('date', $y)->whereMonth('date', $m)
            ->get();

        $employees = Employee::where('status','active')->get();
        $payrolls  = Payroll::with('employee')
            ->where('payroll_period', \Carbon\Carbon::create($y,$m)->format('F Y'))
            ->get();

        $summary = [
            'working_days'    => $attendance->groupBy('date')->count(),
            'total_present'   => $attendance->where('status','present')->count(),
            'total_absent'    => $attendance->where('status','absent')->count(),
            'total_late'      => $attendance->where('status','late')->count(),
            'total_payroll'   => $payrolls->sum('net_salary'),
            'paid_payrolls'   => $payrolls->where('status','paid')->count(),
            'pending_payrolls'=> $payrolls->where('status','pending')->count(),
        ];

        // Attendance by employee
        $empAttendance = $employees->map(function($emp) use ($attendance) {
            $empAtt = $attendance->where('employee_id', $emp->id);
            return [
                'employee'  => $emp,
                'present'   => $empAtt->where('status','present')->count(),
                'absent'    => $empAtt->where('status','absent')->count(),
                'late'      => $empAtt->where('status','late')->count(),
                'hours'     => round($empAtt->sum('work_hours'), 1),
            ];
        });

        return view('reports.hr', compact('summary','empAttendance','payrolls','month'));
    }
}
