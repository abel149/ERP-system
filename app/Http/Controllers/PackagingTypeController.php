<?php

namespace App\Http\Controllers;

use App\Models\PackagingType;
use Illuminate\Http\Request;

class PackagingTypeController extends Controller
{
    public function index()
    {
        $types = PackagingType::orderBy('capacity_kg')->paginate(15);

        return view('packaging.index', compact('types'));
    }

    public function store(Request $request)
    {
        PackagingType::create($this->validated($request));

        return back()->with('success', 'Packaging type created.');
    }

    public function edit(PackagingType $packagingType)
    {
        return view('packaging.edit', compact('packagingType'));
    }

    public function update(Request $request, PackagingType $packagingType)
    {
        $packagingType->update($this->validated($request));

        return redirect()->route('packaging-types.index')->with('success', 'Packaging type updated.');
    }

    public function destroy(PackagingType $packagingType)
    {
        $packagingType->update(['is_active' => false]);

        return back()->with('success', 'Packaging type deactivated.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name'        => ['required', 'string', 'max:150'],
            'capacity_kg' => ['required', 'numeric', 'min:0.01'],
            'unit_cost'   => ['required', 'numeric', 'min:0'],
            'is_active'   => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active', true)];
    }
}
