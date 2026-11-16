<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use App\Models\IssueItem;
use App\Models\Product;
use App\Models\RawMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IssueController extends Controller
{
    public function index()
    {
        $issues = Issue::with(['items.rawMaterial', 'intendedProduct', 'productionItem.production'])
            ->latest()
            ->paginate(20);

        $counts = Issue::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        return view('issues.index', compact('issues', 'counts'));
    }

    public function create(Request $request)
    {
        $materials = RawMaterial::where('status', 'active')->orderBy('name')->get();
        $products  = Product::where('is_active', true)->where('is_nc', false)->orderBy('name')->get();

        // Pre-fill material if coming from a raw material page
        $preselectedMaterialId = $request->input('material_id');

        return view('issues.create', compact('materials', 'products', 'preselectedMaterialId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'items'                  => ['required', 'array', 'min:1'],
            'items.*.raw_material_id'=> ['required', 'distinct', 'exists:raw_materials,id'],
            'items.*.quantity'       => ['required', 'numeric', 'decimal:0,2', 'min:0.01'],
            'pad_type'               => ['required', 'in:GRV,GRH,GRS'],
            'pad_number'             => ['required', 'string', 'max:100'],
            'intended_product_id'    => ['nullable', 'exists:products,id'],
            'description'            => ['nullable', 'string'],
        ]);

        // Load all materials for validation and stock snapshot
        $materialIds  = collect($data['items'])->pluck('raw_material_id')->unique();
        $materials    = RawMaterial::whereIn('id', $materialIds)->get()->keyBy('id');

        // Check all materials exist and have enough stock
        foreach ($data['items'] as $line) {
            $mat = $materials->get($line['raw_material_id']);
            if (! $mat) {
                return back()->withErrors(['items' => "Raw material #{$line['raw_material_id']} not found."])->withInput();
            }
        }

        try {
            DB::transaction(function () use ($data) {
                $lines = array_values($data['items']);
                $materials = RawMaterial::whereIn('id', array_column($lines, 'raw_material_id'))
                    ->orderBy('id')->lockForUpdate()->get()->keyBy('id');
                $issue = Issue::create([
                    'issue_number'        => Issue::nextIssueNumber(),
                    'status'              => 'issued',
                    'pad_type'            => $data['pad_type'],
                    'pad_number'          => $data['pad_number'],
                    'intended_product_id' => $data['intended_product_id'] ?? null,
                    'description'         => $data['description'] ?? null,
                    // legacy columns — keep for compatibility
                    'raw_material_id'     => $lines[0]['raw_material_id'],
                    'quantity'            => collect($data['items'])->sum('quantity'),
                ]);

                foreach ($data['items'] as $line) {
                    $mat = $materials->get($line['raw_material_id']);
                    if (! $mat || $mat->status !== 'active') {
                        throw new \DomainException('Select active materials only.');
                    }
                    $mat->adjustStock(-(float) $line['quantity']);

                    IssueItem::create([
                        'issue_id'        => $issue->id,
                        'raw_material_id' => $line['raw_material_id'],
                        'quantity'        => $line['quantity'],
                        'unit_cost'       => $mat->cost_price, // snapshot at issue time
                        'unit'            => $mat->unit,
                    ]);
                }
            });
        } catch (\DomainException $e) {
            return back()->withErrors(['items' => $e->getMessage()])->withInput();
        }

        return redirect()->route('issues.index')
            ->with('success', 'Materials issued to production successfully.');
    }

    public function show(Issue $issue)
    {
        $issue->load(['items.rawMaterial', 'intendedProduct', 'productionItem.production.product']);

        return view('issues.show', compact('issue'));
    }

    public function destroy(Issue $issue)
    {
        if ($issue->productionItem()->exists()) {
            return back()->with('error', 'Cannot cancel: a production batch has already consumed this issue.');
        }

        DB::transaction(function () use ($issue) {
            $issue = Issue::lockForUpdate()->findOrFail($issue->id);
            abort_if($issue->status !== 'issued' || $issue->productionItem()->exists(), 409, 'This issue is already consumed.');
            $items = $issue->items()->with('rawMaterial')->get();

            if ($items->isNotEmpty()) {
                // Multi-item path: return each ingredient's stock
                foreach ($items as $item) {
                    RawMaterial::lockForUpdate()->findOrFail($item->raw_material_id)->adjustStock((float) $item->quantity);
                }
            } elseif ($issue->rawMaterial) {
                // Legacy single-material path
                $issue->rawMaterial->adjustStock((float) $issue->quantity);
            }

            $issue->delete();
        });

        return redirect()->route('issues.index')
            ->with('success', 'Issue cancelled and stock returned to inventory.');
    }
}
