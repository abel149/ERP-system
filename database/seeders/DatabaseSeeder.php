<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Customer;
use App\Models\PackagingType;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\User;
use App\Support\Costing;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Users ──────────────────────────────────────────────
        $admin = User::updateOrCreate(['email' => 'admin@erp.local'], [
            'name' => 'Ahmed Hassan', 'password' => Hash::make('password'), 'role' => 'admin',
        ]);
        $cashier = User::updateOrCreate(['email' => 'cashier@erp.local'], [
            'name' => 'Sara Tesfaye', 'password' => Hash::make('password'), 'role' => 'cashier',
        ]);
        $stock = User::updateOrCreate(['email' => 'stock@erp.local'], [
            'name' => 'Kebede Alemu', 'password' => Hash::make('password'), 'role' => 'inventory',
        ]);
        $hr = User::updateOrCreate(['email' => 'hr@erp.local'], [
            'name' => 'Meron Tadesse', 'password' => Hash::make('password'), 'role' => 'hr',
        ]);
        $production = User::updateOrCreate(['email' => 'production@erp.local'], [
            'name' => 'Dawit Bekele', 'password' => Hash::make('password'), 'role' => 'production',
        ]);

        // ── Products & raw materials (July price list) ─────────
        $this->call(PriceListSeeder::class);
        $products = Product::where('is_nc', false)->orderBy('id')->get()->all();

        // ── Customers ──────────────────────────────────────────
        $customers = collect([
            ['Abebe Kebede',   '+251911000001', 'abebe@example.com'],
            ['Sara Tesfaye',   '+251911000002', 'sara@example.com'],
            ['Dawit Haile',    '+251911000003', null],
            ['Tigist Alemu',   '+251911000004', 'tigist@example.com'],
            ['Yonas Girma',    '+251911000005', null],
            ['Hana Bekele',    '+251911000006', 'hana@example.com'],
        ])->map(fn($c) => Customer::firstOrCreate(['name' => $c[0]], ['phone' => $c[1], 'email' => $c[2]]));

        // ── Suppliers ──────────────────────────────────────────
        $suppliers = [
            Supplier::firstOrCreate(['name' => 'TechWorld Distributors'], ['company' => 'TechWorld Ltd', 'email' => 'orders@techworld.com', 'phone' => '+1-555-100-2000', 'payment_terms' => 'Net 30', 'status' => 'active']),
            Supplier::firstOrCreate(['name' => 'Office Pro Supplies'],    ['company' => 'Office Pro Inc', 'email' => 'supply@officepro.com', 'phone' => '+1-555-200-3000', 'payment_terms' => 'Net 15', 'status' => 'active']),
            Supplier::firstOrCreate(['name' => 'Fresh Goods Co.'],        ['company' => 'Fresh Goods', 'email' => 'sales@freshgoods.com',   'phone' => '+1-555-300-4000', 'payment_terms' => 'COD',    'status' => 'active']),
            Supplier::firstOrCreate(['name' => 'Textile Masters'],        ['company' => 'Textile Masters Ltd', 'email' => 'info@textilemaster.com', 'payment_terms' => 'Net 45', 'status' => 'active']),
        ];

        // ── Purchase Orders ────────────────────────────────────
        if (PurchaseOrder::count() === 0) {
            $po = PurchaseOrder::create([
                'po_number' => 'PO-00001', 'supplier_id' => $suppliers[0]->id,
                'user_id' => $admin->id, 'subtotal' => 1240.00, 'tax' => 124.00,
                'shipping' => 50.00, 'total' => 1414.00,
                'status' => 'received', 'payment_status' => 'paid',
                'order_date' => now()->subDays(20)->toDateString(),
                'expected_date' => now()->subDays(10)->toDateString(),
                'received_date' => now()->subDays(10)->toDateString(),
            ]);
            $po->items()->createMany([
                ['product_id' => $products[0]->id, 'description' => 'Wireless Mouse', 'quantity' => 50, 'received_qty' => 50, 'unit_cost' => 8.50, 'total' => 425.00],
                ['product_id' => $products[2]->id, 'description' => 'Mechanical Keyboard', 'quantity' => 25, 'received_qty' => 25, 'unit_cost' => 32.00, 'total' => 800.00],
            ]);
            $po2 = PurchaseOrder::create([
                'po_number' => 'PO-00002', 'supplier_id' => $suppliers[1]->id,
                'user_id' => $admin->id, 'subtotal' => 400.00, 'tax' => 40.00,
                'shipping' => 20.00, 'total' => 460.00,
                'status' => 'sent', 'payment_status' => 'unpaid',
                'order_date' => now()->subDays(5)->toDateString(),
                'expected_date' => now()->addDays(5)->toDateString(),
            ]);
            $po2->items()->createMany([
                ['product_id' => $products[5]->id, 'description' => 'Notebook A5', 'quantity' => 100, 'received_qty' => 0, 'unit_cost' => 1.20, 'total' => 120.00],
                ['product_id' => $products[8]->id, 'description' => 'Stapler', 'quantity' => 40, 'received_qty' => 0, 'unit_cost' => 3.00, 'total' => 120.00],
                ['product_id' => $products[7]->id, 'description' => 'Printer Paper A4', 'quantity' => 40, 'received_qty' => 0, 'unit_cost' => 4.00, 'total' => 160.00],
            ]);
        }

        // ── Employees ──────────────────────────────────────────
        $employeeData = [
            ['EMP-001', 'Daniel',    'Bekele',  'daniel@erp.local',   '+251912001001', 'Engineering', 'Senior Developer',     85000, '2023-01-15', 'active'],
            ['EMP-002', 'Meron',     'Alemu',   'meron@erp.local',    '+251912001002', 'HR',          'HR Manager',           75000, '2022-06-01', 'active'],
            ['EMP-003', 'Yohannes',  'Girma',   'yohannes@erp.local', '+251912001003', 'Sales',       'Sales Representative', 55000, '2023-03-20', 'active'],
            ['EMP-004', 'Hiwot',     'Tadesse', 'hiwot@erp.local',    '+251912001004', 'Finance',     'Accountant',           65000, '2022-09-10', 'active'],
            ['EMP-005', 'Samuel',    'Hailu',   'samuel@erp.local',   '+251912001005', 'Operations',  'Operations Manager',   70000, '2021-11-05', 'active'],
            ['EMP-006', 'Bethlehem', 'Yosef',   'bethlehem@erp.local','+251912001006', 'Engineering', 'Junior Developer',     45000, '2024-02-01', 'active'],
        ];
        $employees = [];
        foreach ($employeeData as [$code, $first, $last, $email, $phone, $dept, $pos, $salary, $hire, $status]) {
            $employees[] = Employee::firstOrCreate(['employee_code' => $code], [
                'first_name' => $first, 'last_name' => $last,
                'email' => $email, 'phone' => $phone,
                'department' => $dept, 'position' => $pos, 'base_salary' => $salary,
                'hire_date' => $hire, 'status' => $status,
            ]);
        }

        // ── Sales (past 6 months) ──────────────────────────────
        if (Sale::count() === 0) {
            $paymentMethods = ['cash', 'card', 'bank_transfer'];
            $saleProducts   = array_slice($products, 0, 10);
            $cd = Costing::DEFAULTS;
            $vatPct = $cd['vat_percent'];
            foreach (range(1, 60) as $i) {
                $daysAgo   = rand(0, 180);
                $custId    = $customers->random()->id;
                $userId    = [$admin->id, $cashier->id][array_rand([$admin->id, $cashier->id])];
                $itemCount = rand(1, 4);
                $subtotal  = 0;
                $vatSum    = 0;
                $items     = [];
                for ($j = 0; $j < $itemCount; $j++) {
                    $p    = $saleProducts[array_rand($saleProducts)];
                    $qty  = rand(1, 5);
                    $line = round($qty * (float)$p->sell_price, 2);
                    $subtotal += $line;
                    $other = $cd['rent_cost'] + $cd['utility_cost'] + $cd['machine_cost'] + $cd['other_cost'];
                    $totalCost = (float) $p->cost_price + $other;
                    $marginPct = $totalCost > 0 ? round(((float) $p->sell_price / $totalCost - 1) * 100, 2) : 0;
                    $vat = round($line * $vatPct / 100, 2);
                    $vatSum += $vat;
                    $items[] = [
                        'product_id' => $p->id, 'quantity' => $qty,
                        'unit_price' => $p->sell_price, 'total' => $line,
                        'unit_cost' => (float) $p->cost_price,
                        'rent_cost' => $cd['rent_cost'], 'utility_cost' => $cd['utility_cost'],
                        'machine_cost' => $cd['machine_cost'], 'other_cost' => $cd['other_cost'],
                        'margin_percent' => $marginPct, 'vat_percent' => $vatPct, 'vat_amount' => $vat,
                    ];
                }
                $discount = rand(0, 1) ? round($subtotal * 0.05, 2) : 0;
                $tax      = $vatSum;
                $total    = round($subtotal - $discount + $tax, 2);
                $sale = Sale::create([
                    'invoice_no' => 'INV-'.str_pad($i, 5, '0', STR_PAD_LEFT),
                    'customer_id' => $custId, 'user_id' => $userId,
                    'subtotal' => $subtotal, 'discount' => $discount,
                    'tax' => $tax, 'total' => $total, 'paid' => $total,
                    'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                    'status' => 'completed',
                    'created_at' => now()->subDays($daysAgo),
                    'updated_at' => now()->subDays($daysAgo),
                ]);
                foreach ($items as $item) {
                    SaleItem::create(['sale_id' => $sale->id] + $item);
                }
            }
        }

        // ── Costing defaults (sales order pricing) ────────────
        foreach (Costing::DEFAULTS as $key => $value) {
            Setting::firstOrCreate(['key' => "costing.$key"], ['value' => $value]);
        }

        // ── Packaging types (workbook packing rate ≈ 50 ETB/kg) ──
        foreach ([5, 10, 25, 50, 100] as $kg) {
            PackagingType::firstOrCreate(
                ['capacity_kg' => $kg],
                ['name' => "{$kg} kg bag", 'unit_cost' => $kg * 50]
            );
        }

        // ── Attendance (this month) ────────────────────────────
        if (Attendance::count() === 0) {
            $today   = now()->toDateString();
            $statuses= ['present','present','present','late','absent'];
            foreach ($employees as $emp) {
                for ($day = 1; $day <= now()->day; $day++) {
                    $date = now()->startOfMonth()->addDays($day - 1);
                    if ($date->isWeekend()) continue;
                    if ($date->toDateString() > $today) continue;
                    $status   = $statuses[array_rand($statuses)];
                    $checkIn  = $status !== 'absent' ? $date->copy()->setHour(rand(8,10))->setMinute(rand(0,59))->format('H:i') : null;
                    $checkOut = ($status !== 'absent' && rand(0,1)) ? $date->copy()->setHour(rand(17,19))->setMinute(rand(0,59))->format('H:i') : null;
                    Attendance::firstOrCreate(['employee_id' => $emp->id, 'date' => $date->toDateString()], [
                        'check_in' => $checkIn, 'check_out' => $checkOut,
                        'status' => $status,
                    ]);
                }
            }
        }

        // ── Payroll (current month) ────────────────────────────
        if (Payroll::count() === 0) {
            $period = now()->format('F Y');
            foreach ($employees as $emp) {
                Payroll::firstOrCreate(['employee_id' => $emp->id, 'payroll_period' => $period], [
                    'base_salary'    => $emp->base_salary,
                    'overtime_hours' => rand(0, 10),
                    'overtime_pay'   => rand(0, 10) * round($emp->base_salary / 160, 2),
                    'bonus'          => rand(0, 1) ? round($emp->base_salary * 0.05, 2) : 0,
                    'deductions'     => round($emp->base_salary * 0.02, 2),
                    'tax'            => round($emp->base_salary * 0.10, 2),
                    'net_salary'     => round($emp->base_salary * 0.88, 2),
                    'status'         => ['pending', 'paid'][array_rand(['pending','paid'])],
                ]);
            }
        }
    }
}
