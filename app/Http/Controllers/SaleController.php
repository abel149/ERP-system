<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Support\Costing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index()
    {
        $sales = Sale::with(['customer', 'user'])->latest()->paginate(15);

        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $products = Product::where('is_active', true)->where('quantity', '>', 0)->orderBy('name')->get();
        $costing = Costing::values();

        $productsPayload = $products->map(fn ($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'sku' => $p->sku,
            'price' => (float) $p->sell_price,
            'cost' => (float) $p->cost_price,
            'stock' => (float) $p->quantity,
            'unit' => $p->stock_unit,
        ])->values();

        return view('sales.create', compact('customers', 'products', 'productsPayload', 'costing'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.rent_cost' => ['nullable', 'numeric', 'min:0'],
            'items.*.utility_cost' => ['nullable', 'numeric', 'min:0'],
            'items.*.machine_cost' => ['nullable', 'numeric', 'min:0'],
            'items.*.other_cost' => ['nullable', 'numeric', 'min:0'],
            'items.*.vat' => ['nullable', 'boolean'],
            'costs.rent_cost' => ['nullable', 'numeric', 'min:0'],
            'costs.utility_cost' => ['nullable', 'numeric', 'min:0'],
            'costs.machine_cost' => ['nullable', 'numeric', 'min:0'],
            'costs.other_cost' => ['nullable', 'numeric', 'min:0'],
            'margin_percent' => ['nullable', 'numeric', 'min:0'],
            'vat_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'paid' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:cash,card,bank_transfer,credit'],
            'pad_type' => ['required', 'in:GRV,GRH,GRS'],
            'pad_number' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $sale = DB::transaction(function () use ($data) {
                // Per-order overrides fall back to the company costing defaults.
                $defaults = Costing::values();
                $orderCosts = collect(Costing::COST_KEYS)
                    ->mapWithKeys(fn ($k) => [$k => (float) ($data['costs'][$k] ?? $defaults[$k])])
                    ->all();
                $vatPercent = (float) ($data['vat_percent'] ?? $defaults['vat_percent']);

                $subtotal = 0;
                $items = [];

                foreach ($data['items'] as $item) {
                    $product = Product::lockForUpdate()->findOrFail($item['product_id']);

                    $qty = (float) $item['quantity'];
                    $price = (float) $item['unit_price'];
                    $costs = collect(Costing::COST_KEYS)
                        ->mapWithKeys(fn ($k) => [$k => (float) ($item[$k] ?? $orderCosts[$k])])
                        ->all();

                    $totalCost = (float) $product->cost_price + array_sum($costs);
                    $marginPercent = $totalCost > 0
                        ? round(($price / $totalCost - 1) * 100, 2)
                        : 0;
                    $itemVatPercent = ! empty($item['vat']) ? $vatPercent : 0;
                    $lineTotal = round($qty * $price, 2);
                    $vatAmount = round($lineTotal * $itemVatPercent / 100, 2);
                    $subtotal += $lineTotal;

                    $items[] = compact('product', 'totalCost', 'marginPercent', 'itemVatPercent', 'vatAmount') + [
                        'quantity' => $qty,
                        'unit_price' => $price,
                        'total' => $lineTotal,
                    ] + $costs;
                }

                $discount = (float) ($data['discount'] ?? 0);
                $tax = (float) ($data['tax'] ?? 0);
                $total = max(0, round($subtotal - $discount + $tax, 2));
                $paid = min((float) ($data['paid'] ?? $total), $total);

                $sale = Sale::create([
                    'invoice_no' => Sale::nextInvoiceNo(),
                    'customer_id' => $data['customer_id'] ?? null,
                    'user_id' => Auth::id(),
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'tax' => $tax,
                    'total' => $total,
                    'paid' => $paid,
                    'payment_method' => $data['payment_method'],
                    'pad_type' => $data['pad_type'],
                    'pad_number' => $data['pad_number'],
                    'status' => 'completed',
                    'notes' => $data['notes'] ?? null,
                ]);

                foreach ($items as $item) {
                    $sale->items()->create([
                        'product_id' => $item['product']->id,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'total' => $item['total'],
                        'unit_cost' => (float) $item['product']->cost_price,
                        'rent_cost' => $item['rent_cost'],
                        'utility_cost' => $item['utility_cost'],
                        'machine_cost' => $item['machine_cost'],
                        'other_cost' => $item['other_cost'],
                        'margin_percent' => $item['marginPercent'],
                        'vat_percent' => $item['itemVatPercent'],
                        'vat_amount' => $item['vatAmount'],
                    ]);

                    $item['product']->adjustStock(
                        -$item['quantity'],
                        'sale',
                        null,
                        $sale->invoice_no,
                        Auth::id()
                    );
                }

                return $sale;
            });
        } catch (\DomainException $e) {
            return back()->withErrors(['items' => $e->getMessage()])->withInput();
        }

        return redirect()->route('sales.show', $sale)->with('success', "Sale {$sale->invoice_no} recorded.");
    }

    public function show(Sale $sale)
    {
        $sale->load(['items.product', 'customer', 'user']);

        return view('sales.show', compact('sale'));
    }
}
