<?php

namespace App\Http\Controllers;

use App\Support\Costing;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function editCosting()
    {
        return view('settings.costing', [
            'values' => Costing::values(),
            'labels' => Costing::LABELS,
        ]);
    }

    public function updateCosting(Request $request)
    {
        $rules = collect(Costing::DEFAULTS)->keys()
            ->mapWithKeys(fn ($key) => [$key => ['required', 'numeric', 'min:0']])
            ->all();

        Costing::put($request->validate($rules));

        return back()->with('success', 'Costing defaults updated. They will prefill every new sales order.');
    }
}
