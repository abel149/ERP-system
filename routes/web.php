<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\IssueController;
use App\Http\Controllers\NonConformityController;
use App\Http\Controllers\PackagingTypeController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\ProductionReportController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\RawMaterialController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Models\Category;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    try {
        $categories = Category::withCount(['products' => fn ($q) => $q->where('is_active', 1)])
            ->orderBy('name')
            ->get();
        $productCount = \App\Models\Product::where('is_active', 1)->count();
        $productNames = \App\Models\Product::where('is_active', 1)->orderBy('name')->pluck('name');
        $featured = \App\Models\Product::where('is_active', 1)
            ->whereNotNull('sell_price')->where('sell_price', '>', 0)
            ->orderBy('id')->first();
        $costs = \App\Support\Costing::values();
        $extras = $costs['rent_cost'] + $costs['utility_cost'] + $costs['machine_cost'] + $costs['other_cost'];
        $featuredExtras = $extras;
        $featuredMargin = $featured
            ? round(($featured->sell_price - $featured->cost_price - $extras) / max($featured->cost_price + $extras, 1) * 100)
            : null;
        $vatPercent = $costs['vat_percent'];
    } catch (\Throwable) {
        $categories = collect();
        $productCount = $featuredMargin = $featuredExtras = $vatPercent = null;
        $productNames = collect();
        $featured = null;
    }

    return view('welcome', compact(
        'categories', 'productCount', 'productNames', 'featured', 'featuredExtras', 'featuredMargin', 'vatPercent'
    ));
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ---------------------------------------------------------------- Sales
    Route::middleware('role:admin,cashier')->group(function () {
        Route::resource('customers', CustomerController::class)->except(['show']);
        Route::resource('sales', SaleController::class)->only(['index', 'create', 'store', 'show']);
    });

    // ---------------------------------------------------------------- Inventory / Products
    Route::middleware('role:admin,inventory')->group(function () {
        Route::resource('categories', CategoryController::class)->except(['create', 'show']);
        Route::resource('packaging-types', PackagingTypeController::class)->only(['index', 'store', 'edit', 'update', 'destroy']);
        Route::resource('products', ProductController::class)->except(['show']);
        Route::get('products/{product}/adjust', [ProductController::class, 'adjust'])->name('products.adjust');
        Route::post('products/{product}/adjust', [ProductController::class, 'storeAdjustment'])->name('products.adjust.store');
        Route::resource('raw-materials', RawMaterialController::class);

        // Issues — store keeper creates/cancels
        Route::get('issues/create', [IssueController::class, 'create'])->name('issues.create');
        Route::post('issues', [IssueController::class, 'store'])->name('issues.store');
        Route::delete('issues/{issue}', [IssueController::class, 'destroy'])->name('issues.destroy');
    });

    // Issues — all production-chain roles can view
    Route::middleware('role:admin,inventory,production')->group(function () {
        Route::get('issues', [IssueController::class, 'index'])->name('issues.index');
        Route::get('issues/{issue}', [IssueController::class, 'show'])->name('issues.show');
    });

    // ---------------------------------------------------------------- Productions
    // Queue/list — all production-chain roles
    Route::middleware('role:admin,inventory,production')->group(function () {
        Route::get('productions', [ProductionController::class, 'index'])->name('productions.index');
    });

    // Create/process — production manager + admin only
    // Register static paths before the batch binding.
    Route::middleware('role:admin,production')->group(function () {
        Route::get('productions/create', [ProductionController::class, 'create'])->name('productions.create');
        Route::post('productions', [ProductionController::class, 'store'])->name('productions.store');
        // Step 2: push clean output to FG (NC stays for rework)
        Route::post('productions/{production}/complete', [ProductionController::class, 'complete'])->name('productions.complete');
        // Step 3: rework results — recovered to FG, final NC to NC stock
        Route::get('productions/{production}/rework', [ProductionController::class, 'rework'])->name('productions.rework');
        Route::post('productions/{production}/rework', [ProductionController::class, 'storeRework'])->name('productions.rework.store');
    });

    // NC lots list — all production-chain roles
    Route::middleware('role:admin,inventory,production')->group(function () {
        Route::get('productions/{production}', [ProductionController::class, 'show'])->whereNumber('production')->name('productions.show');
        Route::get('nc', [NonConformityController::class, 'index'])->name('nc.index');
    });

    // Daily production reports (PD report OF-BIP-PD-002)
    Route::middleware('role:admin,production')->group(function () {
        Route::get('production-reports/create', [ProductionReportController::class, 'create'])->name('production-reports.create');
        Route::post('production-reports', [ProductionReportController::class, 'store'])->name('production-reports.store');
        Route::get('production-reports/{productionReport}/edit', [ProductionReportController::class, 'edit'])->name('production-reports.edit');
        Route::put('production-reports/{productionReport}', [ProductionReportController::class, 'update'])->name('production-reports.update');
        Route::delete('production-reports/{productionReport}', [ProductionReportController::class, 'destroy'])->name('production-reports.destroy');
    });

    Route::middleware('role:admin,inventory,production')->group(function () {
        Route::get('production-reports', [ProductionReportController::class, 'index'])->name('production-reports.index');
        Route::get('production-reports/{productionReport}', [ProductionReportController::class, 'show'])
            ->whereNumber('productionReport')->name('production-reports.show');
    });

    // ---------------------------------------------------------------- Procurement
    Route::middleware('role:admin,inventory,cashier')->group(function () {
        Route::resource('suppliers', SupplierController::class);
        Route::resource('purchase-orders', PurchaseOrderController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
        Route::post('purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');
    });

    // ---------------------------------------------------------------- HR & Payroll
    Route::middleware('role:admin,hr')->group(function () {
        Route::resource('employees', EmployeeController::class);
        Route::get('employees/{employee}/attendances', [AttendanceController::class, 'employeeReport'])->name('employees.attendances');
        Route::resource('payrolls', PayrollController::class);
        Route::post('payrolls/{payroll}/mark-paid', [PayrollController::class, 'markAsPaid'])->name('payrolls.mark-paid');
        Route::post('payrolls/generate', [PayrollController::class, 'generate'])->name('payrolls.generate');
    });

    // Attendance — all authenticated users
    Route::resource('attendances', AttendanceController::class)->except(['show']);
    Route::post('attendances/check-in', [AttendanceController::class, 'checkIn'])->name('attendances.check-in');
    Route::post('attendances/check-out', [AttendanceController::class, 'checkOut'])->name('attendances.check-out');

    // ---------------------------------------------------------------- Reports
    Route::middleware('role:admin')->prefix('reports')->name('reports.')->group(function () {
        Route::get('sales', [ReportController::class, 'sales'])->name('sales');
        Route::get('inventory', [ReportController::class, 'inventory'])->name('inventory');
        Route::get('hr', [ReportController::class, 'hr'])->name('hr');
    });

    // ---------------------------------------------------------------- Users & Settings
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
        Route::get('settings/costing', [SettingController::class, 'editCosting'])->name('settings.costing.edit');
        Route::put('settings/costing', [SettingController::class, 'updateCosting'])->name('settings.costing.update');
    });
});
