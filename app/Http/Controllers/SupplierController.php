<?php
namespace App\Http\Controllers;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller {
    public function index() {
        $q = request('search');
        $suppliers = Supplier::when($q, fn($query) => $query->where('name','like',"%$q%")->orWhere('email','like',"%$q%"))
            ->withCount('purchaseOrders')->latest()->paginate(15);
        return view('suppliers.index', compact('suppliers'));
    }
    public function create() { return view('suppliers.create'); }
    public function store(Request $request) {
        $data = $request->validate([
            'name'=>['required','string','max:255'],
            'email'=>['nullable','email'],
            'phone'=>['nullable','string','max:30'],
            'company'=>['nullable','string','max:255'],
            'address'=>['nullable','string'],
            'tax_number'=>['nullable','string','max:50'],
            'payment_terms'=>['required','string','max:50'],
            'status'=>['required','in:active,inactive'],
            'notes'=>['nullable','string'],
        ]);
        Supplier::create($data);
        return redirect()->route('suppliers.index')->with('success','Supplier created.');
    }
    public function show(Supplier $supplier) {
        $supplier->load(['purchaseOrders.user']);
        return view('suppliers.show', compact('supplier'));
    }
    public function edit(Supplier $supplier) { return view('suppliers.edit', compact('supplier')); }
    public function update(Request $request, Supplier $supplier) {
        $data = $request->validate([
            'name'=>['required','string','max:255'],
            'email'=>['nullable','email'],
            'phone'=>['nullable','string','max:30'],
            'company'=>['nullable','string','max:255'],
            'address'=>['nullable','string'],
            'tax_number'=>['nullable','string','max:50'],
            'payment_terms'=>['required','string','max:50'],
            'status'=>['required','in:active,inactive'],
            'notes'=>['nullable','string'],
        ]);
        $supplier->update($data);
        return redirect()->route('suppliers.index')->with('success','Supplier updated.');
    }
    public function destroy(Supplier $supplier) {
        $supplier->delete();
        return back()->with('success','Supplier deleted.');
    }
}
