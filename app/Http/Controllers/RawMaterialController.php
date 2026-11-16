<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\RawMaterial;
use App\Models\Supplier;
use Illuminate\Http\Request;

class RawMaterialController extends Controller
{
    public function index(Request $request)
    {
        $query = RawMaterial::with(['category', 'supplier'])->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        $materials = $query->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();
        $stockValue = (float) RawMaterial::selectRaw('SUM(quantity * cost_price) as total')->value('total');

        return view('raw-materials.index', compact('materials', 'categories', 'stockValue'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('raw-materials.create', compact('categories', 'suppliers'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        RawMaterial::create($data);

        return redirect()->route('raw-materials.index')->with('success', 'Raw material created.');
    }

    public function show(RawMaterial $rawMaterial)
    {
        $rawMaterial->load(['category', 'supplier', 'issueItems' => fn ($q) => $q->latest()->with('issue')]);

        return view('raw-materials.show', compact('rawMaterial'));
    }

    public function edit(RawMaterial $rawMaterial)
    {
        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('raw-materials.edit', compact('rawMaterial', 'categories', 'suppliers'));
    }

    public function update(Request $request, RawMaterial $rawMaterial)
    {
        $data = $this->validated($request, $rawMaterial);
        // Stock only moves through issues/adjustments so the quantity stays trustworthy.
        unset($data['quantity']);
        $rawMaterial->update($data);

        return redirect()->route('raw-materials.index')->with('success', 'Raw material updated.');
    }

    public function destroy(RawMaterial $rawMaterial)
    {
        if ($rawMaterial->issues()->exists() || $rawMaterial->issueItems()->exists()) {
            return back()->withErrors(['delete' => 'Cannot delete: this material has issue records.']);
        }

        $rawMaterial->delete();

        return redirect()->route('raw-materials.index')->with('success', 'Raw material deleted.');
    }

    private function validated(Request $request, ?RawMaterial $material = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:100', 'unique:raw_materials,sku,'.($material->id ?? 'NULL')],
            'category_id' => ['nullable', 'exists:categories,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'unit' => ['required', 'string', 'max:50'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'min_stock' => ['required', 'numeric', 'min:0'],
            'quantity' => [$material ? 'nullable' : 'required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ]);
    }
}
