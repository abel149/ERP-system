<?php

namespace App\Http\Controllers;

use App\Models\NonConformity;
use App\Models\Production;

class NonConformityController extends Controller
{
    public function index()
    {
        $inRework = Production::where('status', 'in_rework')->with(['items.issue', 'product'])->latest()->get();
        $lots = NonConformity::with(['production.items.issue', 'product'])->latest()->paginate(20);

        return view('nc.index', compact('inRework', 'lots'));
    }
}
