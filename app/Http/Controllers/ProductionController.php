<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use App\Models\NonConformity;
use App\Models\Product;
use App\Models\Production;
use App\Support\Costing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductionController extends Controller
{
    public function index(Request $request)
    {
        $pending = Issue::where('status', 'issued')->whereDoesntHave('productionItem')
            ->with(['items.rawMaterial', 'intendedProduct'])->latest()->get();

        $query = Production::with(['items.issue', 'product'])->latest();
        if ($status = $request->input('status')) {
            $query->whereIn('status', $status === 'recorded' ? ['recorded', 'in_progress'] : [$status]);
        }
        if ($search = $request->input('search')) {
            $query->where(fn ($q) => $q->where('production_number', 'like', "%{$search}%")
                ->orWhereHas('items.issue', fn ($i) => $i->where('issue_number', 'like', "%{$search}%"))
                ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$search}%")));
        }
        $productions = $query->paginate(15)->withQueryString();

        $counts = Production::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $counts['recorded'] = ($counts['recorded'] ?? 0) + ($counts['in_progress'] ?? 0);

        return view('productions.index', compact('pending', 'productions', 'counts'));
    }

    public function create(Request $request)
    {
        $available = Issue::where('status', 'issued')->whereDoesntHave('productionItem')
            ->with(['items.rawMaterial', 'intendedProduct'])->latest()->get();
        $products = Product::where('is_active', true)->where('is_nc', false)->orderBy('name')->get();
        $preselected = $request->filled('issue_id') ? [(int) $request->input('issue_id')] : [];

        return view('productions.create', compact('available', 'products', 'preselected'));
    }

    /** Step 1 — manager submits the measured clean output and NC. Nothing moves in stock yet. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'issue_ids'        => ['required', 'array', 'min:1'],
            'issue_ids.*'      => ['required', 'distinct', 'exists:issues,id'],
            'product_id'       => ['nullable', Rule::exists('products', 'id')->where('is_active', 1)->where('is_nc', 0)],
            'new_product_name' => ['nullable', 'required_without:product_id', 'string', 'max:200'],
            'output_quantity'  => ['required', 'numeric', 'min:0'],
            'non_conforming'   => ['required', 'numeric', 'min:0'],
            'packing_cost'     => ['required', 'numeric', 'min:0'],
            'pad_type'         => ['required', 'in:GRV,GRH,GRS'],
            'pad_number'       => ['required', 'string', 'max:100'],
            'description'      => ['nullable', 'string', 'max:2000'],
        ], [
            'new_product_name.required_without' => 'Choose the finished good or enter a new product name.',
        ]);

        $production = DB::transaction(function () use ($data) {
            $issues = Issue::with('items.rawMaterial')->whereIn('id', $data['issue_ids'])->lockForUpdate()->get();
            if ($issues->contains(fn ($i) => $i->status !== 'issued' || $i->productionItem()->exists())) {
                throw ValidationException::withMessages(['issue_ids' => 'One of the selected issues was already processed. Refresh the page.']);
            }

            $lines = $issues->flatMap->items;
            if ($lines->isEmpty() || $lines->contains(fn ($line) => $line->weight_kg === null)) {
                throw ValidationException::withMessages(['issue_ids' => 'Production needs materials issued in kg or g.']);
            }

            $input  = round($lines->sum(fn ($line) => $line->weight_kg), 4);
            $output = (float) $data['output_quantity'];
            $nc     = (float) $data['non_conforming'];

            if ($output + $nc <= 0) {
                throw ValidationException::withMessages(['output_quantity' => 'Enter the clean output and/or the NC you measured.']);
            }
            if ($output + $nc > $input + 0.0001) {
                throw ValidationException::withMessages(['output_quantity' => "Clean output + NC (".($output + $nc)." kg) cannot be more than the {$input} kg issued."]);
            }

            $productId = $data['product_id'] ?? Product::create([
                'name'            => $data['new_product_name'],
                'sku'             => Product::nextSku(),
                'cost_price'      => 0,
                'sell_price'      => 0,
                'quantity'        => 0,
                'is_active'       => true,
                'is_nc'           => false,
            ])->id;

            $production = Production::create([
                'product_id'        => $productId,
                'production_number' => Production::nextProductionNumber(),
                'status'            => 'recorded',
                'batch_type'        => 'normal',
                'input_quantity'    => $input,
                'input_unit'        => 'kg',
                'output_quantity'   => $output,
                'non_conforming'    => $nc,
                'purity'            => round($output / $input, 4),
                'impurity'          => round($nc / $input, 4),
                'material_cost'     => round($issues->sum(fn ($i) => $i->total_value), 2),
                'packing_cost'      => $data['packing_cost'],
                'pad_type'          => $data['pad_type'],
                'pad_number'        => $data['pad_number'],
                'description'       => $data['description'] ?? null,
            ]);

            foreach ($issues as $issue) {
                $production->items()->create(['issue_id' => $issue->id]);
                $issue->update(['status' => 'consumed']);
            }

            return $production;
        });

        return to_route('productions.show', $production)
            ->with('success', 'Results submitted. Check them and push the clean output to finished goods.');
    }

    public function show(Production $production)
    {
        $production->load(['items.issue.items.rawMaterial', 'product', 'ncProduct', 'ncLots.product']);

        return view('productions.show', compact('production'));
    }

    /**
     * Step 2 — push the clean output to FG.
     * Batch cost is shared by weight between clean output and NC, so the NC
     * carries its share into rework and nothing is charged twice.
     */
    public function complete(Production $production)
    {
        try {
            DB::transaction(function () use ($production) {
                $production = Production::lockForUpdate()->findOrFail($production->id);
                if (! in_array($production->status, ['recorded', 'in_progress'])) {
                    throw ValidationException::withMessages(['complete' => 'The clean output of this batch was already pushed to finished goods.']);
                }
                if ($production->input_unit !== 'kg') {
                    throw ValidationException::withMessages(['complete' => 'This batch was recorded before weight units were tracked and cannot be posted.']);
                }

                $output  = (float) $production->output_quantity;
                $nc      = (float) $production->non_conforming;
                $costKg  = $production->total_direct_cost / ($output + $nc);
                $fgValue = round($output * $costKg, 2);
                $units   = null;
                $unitCost = null;

                if ($output > 0) {
                    $product = Product::lockForUpdate()->findOrFail($production->product_id);
                    // Stock is always kept in kg, so the received quantity is the clean weight.
                    $units = $product->stockUnitsFor($output);
                    $unitCost = round($fgValue / $units, 4);
                    $product->receive($units, $unitCost, 'production',
                        "Clean output of {$production->production_number} ({$production->issue_numbers})",
                        $production->production_number);
                    $newPrice = $this->priceUnpricedProduct($product);
                }

                $production->update([
                    'status'         => $nc > 0 ? 'in_rework' : 'completed',
                    'stock_quantity' => $units,
                    'cost_per_unit'  => $unitCost,
                    'nc_value'       => round($production->total_direct_cost - $fgValue, 2),
                    'fg_posted_at'   => now(),
                ]);
            });
        } catch (\DomainException $e) {
            return back()->withErrors(['complete' => $e->getMessage()]);
        }

        $production->refresh();

        $message = $production->status === 'in_rework'
            ? 'Clean output added to finished goods. The NC is now waiting for rework.'
            : 'Clean output added to finished goods. Batch completed.';
        if (! empty($newPrice)) {
            $message .= ' Sell price set to ' . number_format($newPrice, 2) . ' from the costing defaults.';
        }

        return to_route('productions.show', $production)->with('success', $message);
    }

    public function rework(Production $production)
    {
        if ($production->status !== 'in_rework') {
            return to_route('productions.show', $production)->with('error', 'This batch has no NC waiting for rework.');
        }
        $production->load(['items.issue', 'product']);
        $ncProducts = Product::where('is_nc', true)->where('is_active', true)->orderBy('name')->get();

        return view('productions.rework', compact('production', 'ncProducts'));
    }

    /**
     * Step 3 — rework results: recovered clean output goes to FG,
     * the final NC goes to NC stock (sellable at its own price).
     */
    public function storeRework(Request $request, Production $production)
    {
        $data = $request->validate([
            'rework_output'       => ['required', 'numeric', 'min:0'],
            'rework_nc'           => ['required', 'numeric', 'min:0'],
            'rework_packing_cost' => ['required', 'numeric', 'min:0'],
            'nc_product_id'       => ['nullable', Rule::exists('products', 'id')->where('is_nc', 1)],
            'nc_product_name'     => ['nullable', 'string', 'max:200'],
            'nc_sell_price'       => ['nullable', 'numeric', 'min:0'],
            'rework_notes'        => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            DB::transaction(function () use ($data, $production) {
                $production = Production::lockForUpdate()->findOrFail($production->id);
                if ($production->status !== 'in_rework') {
                    throw ValidationException::withMessages(['rework_output' => 'The rework of this batch was already submitted.']);
                }

                $recovered = (float) $data['rework_output'];
                $finalNc   = (float) $data['rework_nc'];
                $available = (float) $production->non_conforming;

                if ($recovered + $finalNc > $available + 0.0001) {
                    throw ValidationException::withMessages(['rework_output' => "Recovered + remaining NC cannot be more than the {$available} kg sent to rework."]);
                }
                if ($finalNc > 0 && empty($data['nc_product_id']) && empty($data['nc_product_name'])) {
                    throw ValidationException::withMessages(['nc_product_id' => 'Choose the NC product or enter a name for a new one.']);
                }
                if ($finalNc > 0 && empty($data['nc_product_id']) && ! isset($data['nc_sell_price'])) {
                    throw ValidationException::withMessages(['nc_sell_price' => 'Set the selling price for the new NC product.']);
                }

                // Cost waiting in the NC + the rework's own packing, shared by weight.
                $pool   = (float) $production->nc_value + (float) $data['rework_packing_cost'];
                $costKg = $recovered + $finalNc > 0 ? $pool / ($recovered + $finalNc) : 0;
                $units = null;
                $unitCost = null;
                $ncProductId = null;

                if ($recovered > 0) {
                    $product = Product::lockForUpdate()->findOrFail($production->product_id);
                    $units = $product->stockUnitsFor($recovered);
                    $unitCost = round($recovered * $costKg / $units, 4);
                    $product->receive($units, $unitCost, 'production',
                        "Rework output of {$production->production_number} ({$production->issue_numbers})",
                        $production->production_number);
                    $newPrice = $this->priceUnpricedProduct($product);
                }

                if ($finalNc > 0) {
                    $ncProduct = ! empty($data['nc_product_id'])
                        ? Product::lockForUpdate()->findOrFail($data['nc_product_id'])
                        : Product::create([
                            'name' => $data['nc_product_name'], 'sku' => Product::nextSku(true),
                            'cost_price' => 0, 'sell_price' => $data['nc_sell_price'], 'quantity' => 0,
                            'is_active' => true, 'is_nc' => true,
                        ]);
                    $ncProduct->receive($finalNc, round($costKg, 4), 'nc_receipt',
                        "Final NC of {$production->production_number} ({$production->issue_numbers})",
                        $production->production_number);
                    if (isset($data['nc_sell_price'])) {
                        $ncProduct->update(['sell_price' => $data['nc_sell_price']]);
                    }
                    $ncProductId = $ncProduct->id;

                    $production->ncLots()->create([
                        'quantity'    => $finalNc,
                        'unit'        => 'kg',
                        'cost_value'  => round($finalNc * $costKg, 2),
                        'status'      => 'final',
                        'product_id'  => $ncProductId,
                        'description' => "Final NC after rework of {$production->production_number}",
                    ]);
                }

                $production->update([
                    'status'                => 'completed',
                    'rework_output'         => $recovered,
                    'rework_nc'             => $finalNc,
                    'rework_packing_cost'   => $data['rework_packing_cost'],
                    'rework_stock_quantity' => $units,
                    'rework_cost_per_unit'  => $unitCost,
                    'nc_product_id'         => $ncProductId,
                    'rework_notes'          => $data['rework_notes'] ?? null,
                    'rework_completed_at'   => now(),
                ]);
            });
        } catch (\DomainException $e) {
            return back()->withErrors(['rework_output' => $e->getMessage()])->withInput();
        }

        $message = 'Rework posted: recovered output added to finished goods and final NC added to NC stock.';
        if (! empty($newPrice)) {
            $message .= ' Sell price set to ' . number_format($newPrice, 2) . ' from the costing defaults.';
        }

        return to_route('productions.show', $production)->with('success', $message);
    }

    /**
     * New products arrive from production unpriced — set the sell price from
     * the blended cost plus the default other costs and margin, the same
     * build-up the price list uses. Never overrides an existing price.
     */
    private function priceUnpricedProduct(Product $product): ?float
    {
        if ((float) $product->sell_price > 0) {
            return null;
        }

        $costs  = Costing::values();
        $extras = $costs['rent_cost'] + $costs['utility_cost'] + $costs['machine_cost'] + $costs['other_cost'];
        $price  = round(((float) $product->cost_price + $extras) * (1 + $costs['margin_percent'] / 100), 2);
        $product->update(['sell_price' => $price]);

        return $price;
    }
}
