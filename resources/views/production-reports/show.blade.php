@extends('layouts.app')
@section('title', 'Daily production report')
@section('content')
@php
    $fmt = fn ($v) => rtrim(rtrim(number_format((float) $v, 2), '0'), '.');
    $fmtYield = fn ($v) => rtrim(rtrim(number_format((float) $v, 4), '0'), '.');
    $previousGroup = null;
@endphp

<div class="page-header pd-actions">
    <div>
        <p class="eyebrow">Production · OF-BIP-PD-002</p>
        <h1 class="page-title">Daily production report</h1>
        <p class="page-subtitle">{{ $report->report_date->format('l, d M Y') }} · week {{ $report->week_no ?: '—' }}</p>
    </div>
    <div class="action-group">
        <a href="{{ route('production-reports.index') }}" class="btn btn-secondary">&larr; All reports</a>
        @if(auth()->user()->hasRole('admin', 'production'))
        <a href="{{ route('production-reports.edit', $report) }}" class="btn">Edit</a>
        @endif
        <button type="button" class="btn btn-primary" onclick="window.print()">Print</button>
    </div>
</div>

<div class="pd-sheet">
    <div class="pd-band">
        <div></div>
        <div class="pd-band-center">Company Name: Brundo International PLC</div>
        <div>OF-BIP-PD-002</div>
    </div>
    <div class="pd-band">
        <div></div>
        <div class="pd-band-center">Revision No. 01</div>
        <div>Page 1 of 1</div>
    </div>
    <div class="pd-band pd-band-title">
        <div></div>
        <div class="pd-band-center">Title: {{ $report->report_title }}</div>
        <div></div>
    </div>

    <div class="pd-info">
        <div class="pd-info-left">
            <div><span>Name:</span> {{ $report->coordinator_name ?: '—' }}</div>
            <div><span>Position:</span> {{ $report->coordinator_position ?: '—' }}</div>
            <div><span>Date:</span> {{ $report->report_date->format('j-n-Y') }}</div>
        </div>
        <div class="pd-info-right">
            <table>
                <tr><td>Total Worker</td><td>{{ $report->total_workers }}</td></tr>
                <tr><td>Total Working Hour</td><td>{{ $fmt($report->working_hours) }}</td></tr>
                <tr><td>Total Present Worker</td><td>{{ $report->present_workers }}</td></tr>
                <tr><td>Total Absent</td><td>{{ $report->absent_workers }}</td></tr>
            </table>
        </div>
    </div>

    <div class="pd-table-wrap">
        <table class="pd-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Group</th>
                    <th>ID CODE</th>
                    <th></th>
                    <th>Raw Material #</th>
                    <th>Amharic Name</th>
                    <th>English Name</th>
                    <th>Types of work</th>
                    <th class="num">TOTAL AMOUNT</th>
                    <th class="num">working Target/hr</th>
                    <th class="num">Impurity</th>
                    <th class="num">Remaining</th>
                    <th class="num">Clean</th>
                    <th class="num">Working hours</th>
                    <th class="num">Processed (Work Done)</th>
                    <th class="num">Speed</th>
                    <th class="num">Average Yield in %</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report->lines as $line)
                <tr>
                    <td>{{ $report->report_date->format('D M d Y') }}</td>
                    <td>{{ $line->group_code !== $previousGroup ? $line->group_code : '' }}</td>
                    <td>{{ $line->employee_code }}</td>
                    <td>{{ $line->employee_name }}</td>
                    <td>{{ $line->material_code }}</td>
                    <td>{{ $line->material_name_am }}</td>
                    <td>{{ $line->material_name_en }}</td>
                    <td>{{ $line->work_type }}</td>
                    <td class="num">{{ $line->has_output ? $fmt($line->total_amount) : '' }}</td>
                    <td class="num">{{ $line->has_output ? $fmt($line->target_per_hour) : '' }}</td>
                    <td class="num">{{ $line->has_output ? $fmt($line->impurity) : '' }}</td>
                    <td class="num">{{ $line->has_output ? $fmt($line->remaining) : '' }}</td>
                    <td class="num">{{ $line->has_output ? $fmt($line->clean) : '' }}</td>
                    <td class="num">{{ $fmt($line->working_hours) }}</td>
                    <td class="num">{{ $line->has_output ? $fmt($line->processed) : '' }}</td>
                    <td class="num">{{ $line->has_output ? $fmt($line->speed) : '' }}</td>
                    <td class="num">{{ $line->has_output ? $fmtYield($line->yield_rate) : '' }}</td>
                </tr>
                @php $previousGroup = $line->group_code; @endphp
                @endforeach
                <tr class="pd-grand">
                    <td colspan="15">Grand Total</td>
                    <td colspan="2" class="num">{{ $fmtYield($report->average_yield) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="pd-footer">
        <div class="pd-sign">
            <div><span>Prepared By:</span> {{ $report->prepared_by ?: '—' }}</div>
            <div><span>Date :</span> {{ optional($report->prepared_date)->format('F j, Y') ?? '—' }}</div>
            <div class="pd-sign-line">Signature----------------------------</div>
            <div><span>Checked By:</span> {{ $report->checked_by ?: '—' }}</div>
            <div><span>Date :</span> {{ optional($report->checked_date)->format('F j, Y') ?? '—' }}</div>
            <div class="pd-sign-line">Signature----------------------------</div>
        </div>

        <table class="pd-totals">
            <tr><th colspan="3">Production Totals</th></tr>
            <tr><th>Functions</th><th>Net / Kg</th><th>Impurity</th></tr>
            @foreach($report->group_totals as $index => $group)
            <tr>
                <td>{{ $index + 1 }}. {{ $group['label'] }}</td>
                <td class="num">{{ $fmt($group['net']) }}</td>
                <td class="num">{{ $fmt($group['impurity']) }}</td>
            </tr>
            @endforeach
            <tr class="strong">
                <td>Total</td>
                <td class="num">{{ $fmt($report->total_amount) }}</td>
                <td class="num">{{ $fmt($report->total_impurity) }}</td>
            </tr>
            <tr>
                <td>To store</td>
                <td class="num">{{ $fmt($report->total_clean) }}</td>
                <td></td>
            </tr>
            <tr>
                <td>Rework</td>
                <td></td>
                <td class="num">{{ $fmt($report->total_impurity) }}</td>
            </tr>
        </table>
    </div>

    @if($report->notes)
    <div class="pd-notes"><strong>Notes:</strong> {{ $report->notes }}</div>
    @endif
</div>
@endsection
