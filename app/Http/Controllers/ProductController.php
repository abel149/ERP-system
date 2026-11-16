<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category')->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        $products = $query->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();

        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($data) {
            $product = Product::create($data);

            if ($product->quantity > 0) {
                $product->stockMovements()->create([
                    'user_id' => Auth::id(),
                    'type' => 'in',
                    'quantity' => $product->quantity,
                    'quantity_after' => $product->quantity,
                    'note' => 'Opening stock',
                ]);
            }
        });

        return redirect()->route('products.index')->with('success', 'Product created.');
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();

        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validated($request, $product);
        // Quantity changes go through the stock adjustment screen so movements stay auditable.
        unset($data['quantity']);
        $product->update($data);

        return redirect()->route('products.index')->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return back()->with('success', 'Product deleted.');
    }

    public function adjust(Product $product)
    {
        $movements = $product->stockMovements()->with('user')->latest()->limit(20)->get();

        return view('products.adjust', compact('product', 'movements'));
    }

    public function storeAdjustment(Request $request, Product $product)
    {
        $data = $request->validate([
            'type' => ['required', 'in:in,out,adjustment'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $delta = match ($data['type']) {
            'in' => $data['quantity'],
            'out' => -$data['quantity'],
            'adjustment' => $data['quantity'] - $product->quantity, // quantity = new counted level
        };

        try {
            DB::transaction(fn () => $product->adjustStock(
                $delta,
                $data['type'],
                $data['note'] ?? null,
                null,
                Auth::id()
            ));
        } catch (\DomainException $e) {
            return back()->withErrors(['quantity' => $e->getMessage()])->withInput();
        }

        return redirect()->route('products.index')->with('success', 'Stock adjusted.');
    }

    private function validated(Request $request, ?Product $product = null): array
    {
        return $request->validate([
            'category_id' => ['nullable', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku,'.($product->id ?? 'NULL')],
            'description' => ['nullable', 'string'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'sell_price' => ['required', 'numeric', 'min:0'],
            'quantity' => [$product ? 'nullable' : 'required', 'numeric', 'min:0'],
            'low_stock_threshold' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);
    }
}
