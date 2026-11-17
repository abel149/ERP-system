<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\ProductionReport;
use App\Models\RawMaterial;
use Illuminate\Http\Request;

class ProductionReportController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductionReport::with('lines')->latest('report_date')->latest('id');

        if ($from = $request->input('from')) {
            $query->whereDate('report_date', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->whereDate('report_date', '<=', $to);
        }
        if ($search = $request->input('search')) {
            $query->where(fn ($q) => $q->where('week_no', 'like', "%{$search}%")
                ->orWhere('coordinator_name', 'like', "%{$search}%")
                ->orWhere('prepared_by', 'like', "%{$search}%"));
        }

        $reports = $query->paginate(15)->withQueryString();

        return view('production-reports.index', compact('reports'));
    }

    public function create()
    {
        $report = new ProductionReport([
            'report_date'          => now()->toDateString(),
            'week_no'              => (string) now()->isoWeek(),
            'coordinator_name'     => auth()->user()->name,
            'coordinator_position' => auth()->user()->role === 'production' ? 'Production Co-ordinator' : 'Production Manager',
            'prepared_by'          => auth()->user()->name,
            'prepared_date'        => now()->toDateString(),
            'checked_date'         => now()->toDateString(),
            'total_workers'        => Employee::where('status', 'active')->count(),
            'working_hours'        => 8,
        ]);

        return view('production-reports.create', $this->formData($report, collect()));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $report = ProductionReport::create(collect($data)->except('lines')->all() + [
            'user_id' => $request->user()->id,
        ]);
        $this->syncLines($report, $data['lines']);

        return to_route('production-reports.show', $report)
            ->with('success', 'Daily production report saved.');
    }

    public function show(ProductionReport $productionReport)
    {
        $productionReport->load('lines');

        return view('production-reports.show', ['report' => $productionReport]);
    }

    public function edit(ProductionReport $productionReport)
    {
        $productionReport->load('lines');

        return view('production-reports.edit', $this->formData($productionReport, $productionReport->lines));
    }

    public function update(Request $request, ProductionReport $productionReport)
    {
        $data = $this->validated($request);

        $productionReport->update(collect($data)->except('lines')->all());
        $productionReport->lines()->delete();
        $this->syncLines($productionReport, $data['lines']);

        return to_route('production-reports.show', $productionReport)
            ->with('success', 'Daily production report updated.');
    }

    public function destroy(ProductionReport $productionReport)
    {
        $productionReport->delete();

        return to_route('production-reports.index')->with('success', 'Daily production report deleted.');
    }

    /** @return array<string, mixed> */
    private function formData(ProductionReport $report, $lines): array
    {
        return [
            'report' => $report,
            'lines' => $lines,
            'employees' => Employee::where('status', 'active')->orderBy('first_name')->get(),
            'materials' => RawMaterial::orderBy('name')->get(),
            'workTypes' => ['Sorting', 'Resorting', 'Roasting', 'Sifting', 'control', 'other work'],
            'groups' => ['G-1', 'G-2', 'G-3', 'G-4', 'G-5'],
        ];
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        return $request->validate([
            'report_date'              => ['required', 'date'],
            'week_no'                  => ['nullable', 'string', 'max:20'],
            'coordinator_name'         => ['nullable', 'string', 'max:200'],
            'coordinator_position'     => ['nullable', 'string', 'max:200'],
            'prepared_by'              => ['nullable', 'string', 'max:200'],
            'prepared_date'            => ['nullable', 'date'],
            'checked_by'               => ['nullable', 'string', 'max:200'],
            'checked_date'             => ['nullable', 'date'],
            'total_workers'            => ['required', 'integer', 'min:0'],
            'working_hours'            => ['required', 'numeric', 'min:0'],
            'notes'                    => ['nullable', 'string', 'max:2000'],
            'lines'                    => ['required', 'array', 'min:1'],
            'lines.*.employee_id'      => ['required', 'exists:employees,id'],
            'lines.*.group_code'       => ['nullable', 'string', 'max:20'],
            'lines.*.raw_material_id'  => ['nullable', 'exists:raw_materials,id'],
            'lines.*.material_code'    => ['nullable', 'string', 'max:100'],
            'lines.*.material_name_am' => ['nullable', 'string', 'max:200'],
            'lines.*.material_name_en' => ['nullable', 'string', 'max:200'],
            'lines.*.work_type'        => ['nullable', 'string', 'max:100'],
            'lines.*.total_amount'     => ['nullable', 'numeric', 'min:0'],
            'lines.*.impurity'         => ['nullable', 'numeric', 'min:0'],
            'lines.*.remaining'        => ['nullable', 'numeric', 'min:0'],
            'lines.*.working_hours'    => ['nullable', 'numeric', 'min:0'],
        ], [
            'lines.required' => 'Add at least one worker line.',
            'lines.*.employee_id.required' => 'Choose the employee (name) on every line.',
        ]);
    }

    /** @param array<int, array<string, mixed>> $lines */
    private function syncLines(ProductionReport $report, array $lines): void
    {
        $employees = Employee::whereIn('id', collect($lines)->pluck('employee_id'))->get()->keyBy('id');
        $materials = RawMaterial::whereIn('id', collect($lines)->pluck('raw_material_id')->filter())
            ->get()->keyBy('id');

        foreach (array_values($lines) as $position => $line) {
            $employee = $employees->get($line['employee_id']);
            $material = ! empty($line['raw_material_id']) ? $materials->get($line['raw_material_id']) : null;

            $report->lines()->create([
                'employee_id'      => $employee?->id,
                'group_code'       => $line['group_code'] ?? null,
                'employee_code'    => $employee?->employee_code,
                'employee_name'    => $employee?->full_name,
                'raw_material_id'  => $material?->id,
                'material_code'    => ($line['material_code'] ?? null) ?: $material?->sku,
                'material_name_am' => ($line['material_name_am'] ?? null) ?: $material?->description,
                'material_name_en' => ($line['material_name_en'] ?? null) ?: $material?->name,
                'work_type'        => $line['work_type'] ?? null,
                'total_amount'     => $line['total_amount'] ?? 0,
                'impurity'         => $line['impurity'] ?? 0,
                'remaining'        => $line['remaining'] ?? 0,
                'working_hours'    => $line['working_hours'] ?? 0,
                'position'         => $position,
            ]);
        }
    }
}
