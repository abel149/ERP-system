@extends('layouts.app')

@section('title', 'Invoice ' . $sale->invoice_no)

@section('content')
<div class="card" style="max-width:760px">
    <div class="invoice-head">
        <div>
            <h2>{{ config('app.name') }}</h2>
            <div class="muted">Sales Invoice</div>
        </div>
        <div class="text-right">
            <div><strong>{{ $sale->invoice_no }}</strong></div>
            <div class="muted">{{ $sale->created_at->format('F d, Y H:i') }}</div>
            <div class="muted">Cashier: {{ $sale->user->name }}</div>
            @if ($sale->pad_number)
                <div class="muted">Doc: {{ $sale->pad_type }}-{{ $sale->pad_number }}</div>
            @endif
        </div>
    </div>

    <p style="margin-bottom:16px">
        <strong>Bill to:</strong>
        {{ $sale->customer?->name ?? 'Walk-in customer' }}
        @if ($sale->customer?->phone)<span class="muted"> — {{ $sale->customer->phone }}</span>@endif
        @if ($sale->customer?->tin)<br><span class="muted">TIN: {{ $sale->customer->tin }}</span>@endif
        @if ($sale->customer?->vat_number)<span class="muted"> · VRN: {{ $sale->customer->vat_number }}</span>@endif
    </p>

    <table class="data">
        <thead>
            <tr><th>Product</th><th>SKU</th><th class="text-right">Amount</th><th class="text-right">Unit Price</th><th class="text-right">Total</th></tr>
        </thead>
        <tbody>
            @foreach ($sale->items as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td class="muted">{{ $item->product->sku }}</td>
                    <td class="text-right">{{ rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.') }} kg</td>
                    <td class="text-right">${{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">${{ number_format($item->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr><td colspan="4" class="text-right muted">Subtotal</td><td class="text-right">${{ number_format($sale->subtotal, 2) }}</td></tr>
            <tr><td colspan="4" class="text-right muted">Discount</td><td class="text-right">-${{ number_format($sale->discount, 2) }}</td></tr>
            <tr><td colspan="4" class="text-right muted">Tax</td><td class="text-right">+${{ number_format($sale->tax, 2) }}</td></tr>
            <tr><td colspan="4" class="text-right" style="font-weight:700">Total</td><td class="text-right" style="font-weight:700">${{ number_format($sale->total, 2) }}</td></tr>
            <tr><td colspan="4" class="text-right muted">Paid ({{ ucfirst(str_replace('_', ' ', $sale->payment_method)) }})</td><td class="text-right">${{ number_format($sale->paid, 2) }}</td></tr>
            @if ($sale->balance > 0)
                <tr><td colspan="4" class="text-right text-danger"><strong>Balance due</strong></td><td class="text-right text-danger"><strong>${{ number_format($sale->balance, 2) }}</strong></td></tr>
            @endif
        </tfoot>
    </table>

    @if ($sale->notes)
        <p class="muted mt"><strong>Notes:</strong> {{ $sale->notes }}</p>
    @endif

    <div class="mt no-print">
        <button class="btn btn-primary" onclick="window.print()">Print Invoice</button>
        <a class="btn" href="{{ route('sales.index') }}">Back to Sales</a>
        <a class="btn" href="{{ route('sales.create') }}">New Sale</a>
    </div>
</div>

<div class="card no-print mt" style="max-width:960px">
    <h2>Cost &amp; margin breakdown <span class="muted" style="font-weight:400; font-size:12px">(internal)</span></h2>
    <div class="table-wrap">
    <table class="data">
        <thead>
            <tr>
                <th>Product</th>
                <th class="text-right">Base cost</th>
                <th class="text-right" title="Rent · Utility · Machine time · Other">Other costs</th>
                <th class="text-right">Total cost</th>
                <th class="text-right">Unit price</th>
                <th class="text-right">Margin</th>
                <th class="text-right">VAT</th>
                <th class="text-right">Profit</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sale->items as $item)
                @php $margin = $item->margin_amount; @endphp
                <tr>
                    <td>{{ $item->product->name }} <span class="muted">&times;{{ rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.') }} kg</span></td>
                    <td class="text-right">${{ number_format($item->unit_cost, 2) }}</td>
                    <td class="text-right" title="Rent {{ number_format($item->rent_cost,2) }} · Utility {{ number_format($item->utility_cost,2) }} · MT {{ number_format($item->machine_cost,2) }} · OC {{ number_format($item->other_cost,2) }}">
                        ${{ number_format($item->other_costs, 2) }}
                    </td>
                    <td class="text-right">${{ number_format($item->total_cost, 2) }}</td>
                    <td class="text-right">${{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">
                        <span class="{{ $item->margin_percent < 0 ? 'margin-neg' : 'margin-pos' }}">{{ number_format($item->margin_percent, 1) }}%</span>
                    </td>
                    <td class="text-right">{{ $item->vat_percent > 0 ? '$'.number_format($item->vat_amount, 2) : '—' }}</td>
                    <td class="text-right {{ $margin < 0 ? 'margin-neg' : 'margin-pos' }}">${{ number_format($margin, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" class="text-right" style="font-weight:700">Expected gross profit</td>
                <td class="text-right" style="font-weight:700">
                    ${{ number_format($sale->items->sum(fn ($i) => $i->margin_amount), 2) }}
                </td>
            </tr>
        </tfoot>
    </table>
    </div>
</div>
@endsection
