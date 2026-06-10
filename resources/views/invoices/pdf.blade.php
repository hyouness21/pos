@php $isAr = app()->getLocale() === 'ar'; @endphp
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: 'DejaVu Sans', sans-serif;
        font-size: 13px;
        color: #111;
    }

    /* ── Header ── */
    .header {
        text-align: center;
        padding-bottom: 16px;
        margin-bottom: 22px;
        border-bottom: 2px solid #222;
    }
    .org            { font-size: 20px; font-weight: bold; }
    .customer-name  { font-size: 15px; font-weight: bold; margin-top: 9px; }
    .customer-phone { font-size: 12px; color: #555; margin-top: 4px; direction: ltr; unicode-bidi: embed; }
    .num { direction: ltr; unicode-bidi: bidi-override; display: inline-block; }

    /* ── Meta table ── */
    table.meta { width: 100%; margin-bottom: 22px; border-collapse: collapse; }
    table.meta td { padding: 5px 4px; font-size: 13px; vertical-align: top; }
    table.meta td.lbl { color: #555; width: 38%; }
    table.meta td.val { font-weight: 500; }
    table.meta td.val-r { font-weight: 500; text-align: right; }

    /* ── Section title ── */
    .section-title {
        font-size: 11px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #777;
        margin-bottom: 6px;
        @if($isAr) text-align: right; @endif
    }

    /* ── Items table ── */
    table.items { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
    table.items thead td {
        background: #f0f4f8;
        font-weight: bold;
        font-size: 11px;
        padding: 8px 10px;
        border-bottom: 2px solid #ccc;
    }
    table.items tbody td {
        padding: 8px 10px;
        font-size: 13px;
        border-bottom: 1px solid #eee;
    }
    .r { text-align: right; }
    .l { text-align: left;  }
    .bold { font-weight: bold; }

    /* ── Total ── */
    .total-wrap {
        text-align: right;
        margin-top: 8px;
        padding-top: 8px;
        border-top: 2px solid #222;
    }
    .total-label  { font-size: 12px; color: #666; margin-bottom: 2px; }
    .total-amount { font-size: 24px; font-weight: bold; }

    /* ── Badges ── */
    .badge { padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; }
    .badge-paid    { background: #d1fae5; color: #065f46; }
    .badge-pending { background: #ffedd5; color: #9a3412; }

    /* ── Footer ── */
    .footer {
        margin-top: 30px;
        text-align: center;
        font-size: 10px;
        color: #aaa;
        border-top: 1px solid #ddd;
        padding-top: 12px;
    }
</style>
</head>
<body>

{{-- Header --}}
<div class="header">
    <div class="org">{{ __('Hassan Haidar Foundation') }}</div>
    <div class="customer-name">{{ $invoice->customer->name }}</div>
    @if($invoice->customer->phone)
        <div class="customer-phone">{{ $invoice->customer->phone }}</div>
    @endif
</div>

{{-- Meta --}}
<table class="meta">
    @if($isAr)
    <tr>
        <td class="val">{{ '#' . $invoice->id }}</td>
        <td class="lbl r">{{ __('Invoice') }}</td>
    </tr>
    <tr>
        <td class="val">{{ $invoice->created_at->format('d M Y, H:i') }}</td>
        <td class="lbl r">{{ __('Date') }}</td>
    </tr>
    <tr>
        <td class="val">{{ $invoice->payment_method === 'cash' ? __('Cash') : __('Pay Later') }}</td>
        <td class="lbl r">{{ __('Payment') }}</td>
    </tr>
    <tr>
        <td class="val">
            <span class="badge {{ $invoice->status === 'paid' ? 'badge-paid' : 'badge-pending' }}">
                {{ $invoice->status === 'paid' ? __('Paid') : __('Pending') }}
            </span>
        </td>
        <td class="lbl r">{{ __('Status') }}</td>
    </tr>
    @if($invoice->notes)
    <tr>
        <td class="val">{{ $invoice->notes }}</td>
        <td class="lbl r">{{ __('Notes') }}</td>
    </tr>
    @endif
    @else
    <tr>
        <td class="lbl">{{ __('Invoice') }}</td>
        <td class="val-r">{{ '#' . $invoice->id }}</td>
    </tr>
    <tr>
        <td class="lbl">{{ __('Date') }}</td>
        <td class="val-r">{{ $invoice->created_at->format('d M Y, H:i') }}</td>
    </tr>
    <tr>
        <td class="lbl">{{ __('Payment') }}</td>
        <td class="val-r">{{ $invoice->payment_method === 'cash' ? __('Cash') : __('Pay Later') }}</td>
    </tr>
    <tr>
        <td class="lbl">{{ __('Status') }}</td>
        <td class="val-r">
            <span class="badge {{ $invoice->status === 'paid' ? 'badge-paid' : 'badge-pending' }}">
                {{ $invoice->status === 'paid' ? __('Paid') : __('Pending') }}
            </span>
        </td>
    </tr>
    @if($invoice->notes)
    <tr>
        <td class="lbl">{{ __('Notes') }}</td>
        <td class="val-r">{{ $invoice->notes }}</td>
    </tr>
    @endif
    @endif
</table>

{{-- Items --}}
<div class="section-title">{{ __('Items') }}</div>
<table class="items">
    <thead>
        <tr>
            @if($isAr)
                <td class="r bold">{{ __('Subtotal') }}</td>
                <td class="r">{{ __('Unit Price') }}</td>
                <td class="r">{{ __('Qty') }}</td>
                <td class="r">{{ __('Item') }}</td>
            @else
                <td class="l bold">{{ __('Item') }}</td>
                <td class="r">{{ __('Qty') }}</td>
                <td class="r">{{ __('Unit Price') }}</td>
                <td class="r bold">{{ __('Subtotal') }}</td>
            @endif
        </tr>
    </thead>
    <tbody>
        @foreach($invoice->items as $line)
        @php $isFree = $line->unit_price == 0; @endphp
        <tr>
            @if($isAr)
                <td class="r bold">@if($isFree){{ __('FREE') }}@else<span class="num">${{ number_format($line->subtotal, 2) }}</span>@endif</td>
                <td class="r">@if($isFree)—@else<span class="num">${{ number_format($line->unit_price, 2) }}</span>@endif</td>
                <td class="r">{{ $line->quantity }}</td>
                <td class="r">{{ $line->item->name }}</td>
            @else
                <td class="l">{{ $line->item->name }}</td>
                <td class="r">{{ $line->quantity }}</td>
                <td class="r">@if($isFree)—@else<span class="num">${{ number_format($line->unit_price, 2) }}</span>@endif</td>
                <td class="r bold">@if($isFree){{ __('FREE') }}@else<span class="num">${{ number_format($line->subtotal, 2) }}</span>@endif</td>
            @endif
        </tr>
        @endforeach
    </tbody>
</table>

{{-- Total --}}
<div class="total-wrap">
    @if ($invoice->discount > 0)
    @php
        $subtotal = $invoice->total_amount + $invoice->discount;
        $pct = $subtotal > 0 ? round($invoice->discount / $subtotal * 100, 1) : 0;
    @endphp
    <div style="font-size:12px; color:#666; margin-bottom:2px;">
        {{ __('Subtotal') }}: <span class="num">${{ number_format($subtotal, 2) }}</span>
    </div>
    <div style="font-size:13px; color:#dc2626; margin-bottom:4px; font-weight:600;">
        {{ __('Discount') }} ({{ $pct }}%): − <span class="num">${{ number_format($invoice->discount, 2) }}</span>
    </div>
    @endif
    <div class="total-label">{{ __('Total') }}</div>
    <div class="total-amount"><span class="num">${{ number_format($invoice->total_amount, 2) }}</span></div>
    @if ($invoice->status === 'pending')
        @if ($invoice->amount_paid > 0)
        <div style="font-size:12px; color:#16a34a; margin-top:4px;">
            {{ __('Paid') }}: <span class="num">${{ number_format($invoice->amount_paid, 2) }}</span>
        </div>
        @endif
        <div style="font-size:14px; font-weight:bold; color:#ea580c; margin-top:4px; padding-top:4px; border-top:1px dashed #fed7aa;">
            {{ __('Remaining') }}: <span class="num">${{ number_format($invoice->remaining(), 2) }}</span>
        </div>
    @endif
</div>

{{-- Footer --}}
<div class="footer">
    {{ __('Hassan Haidar Foundation') }} &#8212; {{ __('Invoice') }} #{{ $invoice->id }}
</div>

</body>
</html>
