@php
    $initialRows = old('lines', $lines->map(fn ($line) => [
        'employee_id'      => $line->employee_id,
        'group_code'       => $line->group_code,
        'raw_material_id'  => $line->raw_material_id,
        'material_code'    => $line->material_code,
        'material_name_am' => $line->material_name_am,
        'material_name_en' => $line->material_name_en,
        'work_type'        => $line->work_type,
        'total_amount'     => (float) $line->total_amount,
        'impurity'         => (float) $line->impurity,
        'remaining'        => (float) $line->remaining,
        'working_hours'    => (float) $line->working_hours,
    ])->values()->all());
@endphp

<form method="POST" action="{{ $action }}" id="pdForm">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <div class="stack">
        <section class="card">
            <div class="card-header">
                <div><h2>Report header</h2><p class="card-subtitle">Company form OF-BIP-PD-002 · daily production report.</p></div>
            </div>
            <div class="card-body">
                <div class="fields">
                    <div class="form-group">
                        <label class="form-label" for="report_date">Report date *</label>
                        <input class="form-control" type="date" id="report_date" name="report_date"
                               value="{{ old('report_date', optional($report->report_date)->toDateString()) }}" required>
                        @error('report_date')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="week_no">Week no.</label>
                        <input class="form-control" type="text" id="week_no" name="week_no" maxlength="20"
                               value="{{ old('week_no', $report->week_no) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="coordinator_name">Name (coordinator)</label>
                        <input class="form-control" type="text" id="coordinator_name" name="coordinator_name" maxlength="200"
                               value="{{ old('coordinator_name', $report->coordinator_name) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="coordinator_position">Position</label>
                        <input class="form-control" type="text" id="coordinator_position" name="coordinator_position" maxlength="200"
                               value="{{ old('coordinator_position', $report->coordinator_position) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="total_workers">Total workers *</label>
                        <input class="form-control" type="number" min="0" id="total_workers" name="total_workers"
                               value="{{ old('total_workers', $report->total_workers) }}" required>
                        <p class="form-hint" id="presentHint">Present 0 · Absent 0</p>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="working_hours">Total working hour *</label>
                        <input class="form-control" type="number" min="0" step="0.01" id="working_hours" name="working_hours"
                               value="{{ old('working_hours', $report->working_hours) }}" required>
                        <p class="form-hint">Shift length in the header; each row carries its own hours.</p>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="prepared_by">Prepared by</label>
                        <input class="form-control" type="text" id="prepared_by" name="prepared_by" maxlength="200"
                               value="{{ old('prepared_by', $report->prepared_by) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="prepared_date">Prepared date</label>
                        <input class="form-control" type="date" id="prepared_date" name="prepared_date"
                               value="{{ old('prepared_date', optional($report->prepared_date)->toDateString()) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="checked_by">Checked by</label>
                        <input class="form-control" type="text" id="checked_by" name="checked_by" maxlength="200"
                               value="{{ old('checked_by', $report->checked_by) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="checked_date">Checked date</label>
                        <input class="form-control" type="date" id="checked_date" name="checked_date"
                               value="{{ old('checked_date', optional($report->checked_date)->toDateString()) }}">
                    </div>
                    <div class="form-group span-all">
                        <label class="form-label" for="notes">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" maxlength="2000" placeholder="Quality observations, loss reasons…">{{ old('notes', $report->notes) }}</textarea>
                    </div>
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card-header">
                <div>
                    <h2>Worker lines</h2>
                    <p class="card-subtitle">One row per worker. The ID code and name are taken from the employee record.</p>
                </div>
                <button type="button" class="btn btn-secondary btn-sm" id="addLine">+ Add row</button>
            </div>
            <div class="table-wrap">
                <table class="table pd-form-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Group</th>
                            <th>Employee (name)</th>
                            <th>Raw material</th>
                            <th>Material #</th>
                            <th>Amharic name</th>
                            <th>English name</th>
                            <th>Types of work</th>
                            <th class="num">TOTAL AMOUNT</th>
                            <th class="num">Target/hr</th>
                            <th class="num">Impurity</th>
                            <th class="num">Remaining</th>
                            <th class="num">Clean</th>
                            <th class="num">Working hours</th>
                            <th class="num">Processed</th>
                            <th class="num">Speed</th>
                            <th class="num">Yield</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="linesBody"></tbody>
                    <tfoot>
                        <tr>
                            <td colspan="8"><strong>Totals</strong></td>
                            <td class="num" id="totalAmount">0</td>
                            <td></td>
                            <td class="num" id="totalImpurity">0</td>
                            <td class="num" id="totalRemaining">0</td>
                            <td class="num" id="totalClean">0</td>
                            <td></td>
                            <td class="num" id="totalProcessed">0</td>
                            <td></td>
                            <td class="num" id="totalYield">0</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @error('lines')<p class="form-error" style="padding:0 24px 16px">{{ $message }}</p>@enderror
        </section>

        <div class="form-footer">
            <p>* Required · yield = clean ÷ total amount.</p>
            <a href="{{ route('production-reports.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary btn-lg">{{ $submitLabel }}</button>
        </div>
    </div>
</form>

{{-- Row source for the lines table. Kept outside the form so it never submits. --}}
<div id="lineTemplate" hidden>
    <table>
        <tbody>
            <tr>
                <td class="row-no"></td>
                <td><input type="text" name="lines[__INDEX__][group_code]" list="pdGroups" maxlength="20" placeholder="G-1"></td>
                <td>
                    <select name="lines[__INDEX__][employee_id]" class="line-employee">
                        <option value="">— Select —</option>
                        @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" data-code="{{ $employee->employee_code }}" data-name="{{ $employee->full_name }}">
                            {{ $employee->employee_code }} — {{ $employee->full_name }}
                        </option>
                        @endforeach
                    </select>
                    <span class="emp-code">—</span>
                </td>
                <td>
                    <select name="lines[__INDEX__][raw_material_id]" class="line-material">
                        <option value="">—</option>
                        @foreach($materials as $material)
                        <option value="{{ $material->id }}" data-code="{{ $material->sku }}"
                                data-am="{{ $material->description }}" data-en="{{ $material->name }}">
                            {{ $material->name }}
                        </option>
                        @endforeach
                    </select>
                </td>
                <td><input type="text" name="lines[__INDEX__][material_code]" maxlength="100"></td>
                <td><input type="text" name="lines[__INDEX__][material_name_am]" maxlength="200"></td>
                <td><input type="text" name="lines[__INDEX__][material_name_en]" maxlength="200"></td>
                <td><input type="text" name="lines[__INDEX__][work_type]" list="pdWorkTypes" maxlength="100" placeholder="Sorting"></td>
                <td><input type="number" name="lines[__INDEX__][total_amount]" min="0" step="0.01"></td>
                <td class="pd-calc calc-target">—</td>
                <td><input type="number" name="lines[__INDEX__][impurity]" min="0" step="0.01"></td>
                <td><input type="number" name="lines[__INDEX__][remaining]" min="0" step="0.01"></td>
                <td class="pd-calc calc-clean">—</td>
                <td><input type="number" name="lines[__INDEX__][working_hours]" min="0" step="0.01" value="7.5"></td>
                <td class="pd-calc calc-processed">—</td>
                <td class="pd-calc calc-speed">—</td>
                <td class="pd-calc calc-yield">—</td>
                <td><button type="button" class="btn btn-sm btn-outline-danger remove-line" title="Remove row">&times;</button></td>
            </tr>
        </tbody>
    </table>
    <datalist id="pdGroups">
        @foreach($groups as $group)
        <option value="{{ $group }}">
        @endforeach
    </datalist>
    <datalist id="pdWorkTypes">
        @foreach($workTypes as $type)
        <option value="{{ $type }}">
        @endforeach
    </datalist>
</div>

@push('scripts')
<script>
(() => {
    const form = document.getElementById('pdForm');
    if (!form) return;

    const tbody = document.getElementById('linesBody');
    const rowSource = document.querySelector('#lineTemplate tr');
    const totalWorkers = document.getElementById('total_workers');
    const presentHint = document.getElementById('presentHint');
    let rowIndex = 0;

    const num = el => Number(el && el.value) || 0;
    const fmt = (value, digits = 2) => {
        const text = Number(value || 0).toFixed(digits);
        return text.includes('.') ? text.replace(/0+$/, '').replace(/\.$/, '') : text;
    };
    const field = (tr, name) => tr.querySelector(`[name$="[${name}]"]`);

    function recalcRow(tr) {
        const amount = num(field(tr, 'total_amount'));
        const impurity = num(field(tr, 'impurity'));
        const remaining = num(field(tr, 'remaining'));
        const hours = num(field(tr, 'working_hours'));
        const clean = Math.max(0, amount - impurity - remaining);
        const target = hours > 0 ? amount / hours : 0;
        const speed = hours > 0 ? clean / hours : 0;
        const yieldRate = amount > 0 ? clean / amount : 0;
        const has = amount > 0 || impurity > 0;

        tr.querySelector('.calc-target').textContent = has ? fmt(target, 4) : '—';
        tr.querySelector('.calc-clean').textContent = has ? fmt(clean) : '—';
        tr.querySelector('.calc-processed').textContent = has ? fmt(amount) : '—';
        tr.querySelector('.calc-speed').textContent = has ? fmt(speed, 4) : '—';
        tr.querySelector('.calc-yield').textContent = has ? fmt(yieldRate, 4) : '—';
    }

    function recalcAll() {
        let amount = 0, impurity = 0, remaining = 0, clean = 0;
        const employees = new Set();

        tbody.querySelectorAll('tr').forEach(tr => {
            const a = num(field(tr, 'total_amount'));
            const i = num(field(tr, 'impurity'));
            const r = num(field(tr, 'remaining'));
            amount += a;
            impurity += i;
            remaining += r;
            clean += Math.max(0, a - i - r);
            const employee = field(tr, 'employee_id');
            if (employee && employee.value) employees.add(employee.value);
        });

        document.getElementById('totalAmount').textContent = fmt(amount);
        document.getElementById('totalImpurity').textContent = fmt(impurity);
        document.getElementById('totalRemaining').textContent = fmt(remaining);
        document.getElementById('totalClean').textContent = fmt(clean);
        document.getElementById('totalProcessed').textContent = fmt(amount);
        document.getElementById('totalYield').textContent = amount > 0 ? fmt(clean / amount, 4) : '0';

        const present = employees.size;
        const total = num(totalWorkers);
        presentHint.textContent = `Present ${present} · Absent ${Math.max(0, total - present)}`;
    }

    function renumber() {
        tbody.querySelectorAll('tr').forEach((tr, i) => {
            tr.querySelector('.row-no').textContent = i + 1;
        });
    }

    function addRow(data = {}) {
        const index = rowIndex++;
        const tr = rowSource.cloneNode(true);

        tr.querySelectorAll('[name]').forEach(el => {
            el.name = el.name.replaceAll('__INDEX__', index);
        });
        tr.querySelector('.row-no').textContent = tbody.querySelectorAll('tr').length + 1;

        const employee = field(tr, 'employee_id');
        const material = field(tr, 'raw_material_id');

        if (data.employee_id) employee.value = String(data.employee_id);
        if (data.raw_material_id) material.value = String(data.raw_material_id);

        ['group_code', 'material_code', 'material_name_am', 'material_name_en', 'work_type'].forEach(key => {
            const el = field(tr, key);
            if (el && data[key] != null) el.value = data[key];
        });
        ['total_amount', 'impurity', 'remaining', 'working_hours'].forEach(key => {
            const el = field(tr, key);
            if (el && data[key] != null) el.value = data[key];
        });

        const updateCode = () => {
            const option = employee.selectedOptions[0];
            tr.querySelector('.emp-code').textContent = option && option.value ? option.dataset.code : '—';
        };
        employee.addEventListener('change', () => { updateCode(); recalcAll(); });
        updateCode();

        material.addEventListener('change', () => {
            const option = material.selectedOptions[0];
            if (!option || !option.value) return;
            field(tr, 'material_code').value = option.dataset.code || '';
            field(tr, 'material_name_am').value = option.dataset.am || '';
            field(tr, 'material_name_en').value = option.dataset.en || '';
        });

        tr.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', () => { recalcRow(tr); recalcAll(); });
        });

        tr.querySelector('.remove-line').addEventListener('click', () => {
            tr.remove();
            renumber();
            if (!tbody.querySelector('tr')) addRow();
            recalcAll();
        });

        tbody.appendChild(tr);
        recalcRow(tr);
        recalcAll();
    }

    document.getElementById('addLine').addEventListener('click', () => addRow());
    totalWorkers.addEventListener('input', recalcAll);

    form.addEventListener('submit', event => {
        const selects = [...tbody.querySelectorAll('[name$="[employee_id]"]')];
        if (!selects.some(select => select.value)) {
            event.preventDefault();
            alert('Add at least one worker line and choose the employee.');
        }
    });

    const initialRows = @json($initialRows);
    (initialRows.length ? initialRows : [{}]).forEach(addRow);
})();
</script>
@endpush
