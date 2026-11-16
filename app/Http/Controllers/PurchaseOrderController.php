<?php
namespace App\Http\Controllers;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller {
    public function index() {
        $status = request('status');
        $orders = PurchaseOrder::with('supplier')
            ->when($status, fn($q) => $q->where('status',$status))
            ->latest()->paginate(15);
        return view('purchase-orders.index', compact('orders'));
    }
    public function create() {
        $suppliers = Supplier::where('status','active')->orderBy('name')->get();
        $products  = Product::where('is_active',true)->orderBy('name')->get();
        return view('purchase-orders.create', compact('suppliers','products'));
    }
    public function store(Request $request) {
        $data = $request->validate([
            'supplier_id'   => ['required','exists:suppliers,id'],
            'order_date'    => ['required','date'],
            'expected_date' => ['nullable','date'],
            'notes'         => ['nullable','string'],
            'shipping'      => ['nullable','numeric','min:0'],
            'tax'           => ['nullable','numeric','min:0'],
            'items'         => ['required','array','min:1'],
            'items.*.description' => ['required','string'],
            'items.*.product_id'  => ['nullable','exists:products,id'],
            'items.*.quantity'    => ['required','integer','min:1'],
            'items.*.unit_cost'   => ['required','numeric','min:0'],
        ]);

        DB::transaction(function() use ($data) {
            $subtotal = collect($data['items'])->sum(fn($i) => $i['quantity'] * $i['unit_cost']);
            $tax      = (float)($data['tax'] ?? 0);
            $shipping = (float)($data['shipping'] ?? 0);
            $total    = $subtotal + $tax + $shipping;

            $po = PurchaseOrder::create([
                'po_number'     => PurchaseOrder::nextPoNumber(),
                'supplier_id'   => $data['supplier_id'],
                'user_id'       => Auth::id(),
                'subtotal'      => $subtotal,
                'tax'           => $tax,
                'shipping'      => $shipping,
                'total'         => $total,
                'status'        => 'draft',
                'payment_status'=> 'unpaid',
                'order_date'    => $data['order_date'],
                'expected_date' => $data['expected_date'] ?? null,
                'notes'         => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $po->items()->create([
                    'product_id'  => $item['product_id'] ?? null,
                    'description' => $item['description'],
                    'quantity'    => $item['quantity'],
                    'unit_cost'   => $item['unit_cost'],
                    'total'       => $item['quantity'] * $item['unit_cost'],
                ]);
            }
        });

        return redirect()->route('purchase-orders.index')->with('success','Purchase order created.');
    }
    public function show(PurchaseOrder $purchaseOrder) {
        $purchaseOrder->load(['supplier','items.product','user']);
        return view('purchase-orders.show', compact('purchaseOrder'));
    }
    public function receive(PurchaseOrder $purchaseOrder) {
        DB::transaction(function() use ($purchaseOrder) {
            foreach ($purchaseOrder->items as $item) {
                $qty = $item->quantity - $item->received_qty;
                if ($qty > 0 && $item->product) {
                    $item->product->adjustStock($qty,'purchase','PO received',$purchaseOrder->po_number, Auth::id());
                }
                $item->update(['received_qty' => $item->quantity]);
            }
            $purchaseOrder->update(['status'=>'received','received_date'=>now()->toDateString()]);
        });
        return back()->with('success','Purchase order marked as received. Stock updated.');
    }
    public function destroy(PurchaseOrder $purchaseOrder) {
        if ($purchaseOrder->status !== 'draft') return back()->withErrors(['error'=>'Only draft orders can be deleted.']);
        $purchaseOrder->delete();
        return redirect()->route('purchase-orders.index')->with('success','Purchase order deleted.');
    }
}
