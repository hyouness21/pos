@extends('layouts.app')
@section('title', 'Invoice #' . $invoice->id)

@section('content')

{{-- Status banner --}}
<div class="rounded-2xl px-4 py-3 mb-4 flex items-center justify-between
    {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800' }}">
    <span class="font-semibold">{{ $invoice->status === 'paid' ? __('✓ Paid') : __('⏳ Pending Payment') }}</span>
    <form method="POST" action="{{ route('invoices.status', $invoice) }}">
        @csrf @method('PATCH')
        @if ($invoice->status === 'pending')
            <input type="hidden" name="status" value="paid">
            <button type="submit" class="text-sm font-medium bg-green-600 text-white px-3 py-1 rounded-lg">{{ __('Mark Paid') }}</button>
        @else
            <input type="hidden" name="status" value="pending">
            <button type="submit" class="text-sm font-medium bg-orange-500 text-white px-3 py-1 rounded-lg">{{ __('Mark Pending') }}</button>
        @endif
    </form>
</div>

{{-- Customer + meta --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-4 space-y-2 text-sm">
    <div class="flex justify-between">
        <span class="text-gray-500">{{ __('Customer') }}</span>
        <a href="{{ route('customers.show', $invoice->customer) }}" class="font-medium text-indigo-600">
            {{ $invoice->customer->name }}
        </a>
    </div>
    <div class="flex justify-between">
        <span class="text-gray-500">{{ __('Date') }}</span>
        <span class="font-medium">{{ $invoice->created_at->format('d M Y, H:i') }}</span>
    </div>
    <div class="flex justify-between">
        <span class="text-gray-500">{{ __('Payment') }}</span>
        <span class="font-medium">{{ $invoice->payment_method === 'cash' ? 'Cash' : 'Pay Later' }}</span>
    </div>
    @if ($invoice->notes)
    <div class="flex justify-between">
        <span class="text-gray-500">{{ __('Notes') }}</span>
        <span class="font-medium text-right">{{ $invoice->notes }}</span>
    </div>
    @endif
</div>

{{-- Partial payment card (pending only) --}}
@if ($invoice->status === 'pending')
<div class="bg-white rounded-2xl shadow-sm border border-orange-200 p-4 mb-4 space-y-3">
    <div class="flex items-center justify-between text-sm">
        <span class="text-gray-500 font-medium">{{ __('Amount Paid') }}</span>
        <span class="font-bold text-green-600" style="direction:ltr;unicode-bidi:bidi-override">${{ number_format($invoice->amount_paid, 2) }}</span>
    </div>
    <div class="flex items-center justify-between text-sm">
        <span class="text-gray-500 font-medium">{{ __('Remaining') }}</span>
        <span class="font-bold text-orange-600" style="direction:ltr;unicode-bidi:bidi-override">${{ number_format($invoice->remaining(), 2) }}</span>
    </div>
    {{-- Progress bar --}}
    @php $pct = $invoice->total_amount > 0 ? min(100, round($invoice->amount_paid / $invoice->total_amount * 100)) : 0; @endphp
    <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
        <div class="bg-green-500 h-2 rounded-full transition-all" style="width: {{ $pct }}%"></div>
    </div>
    {{-- Record payment form --}}
    @if ($invoice->remaining() > 0)
    <form method="POST" action="{{ route('invoices.payment', $invoice) }}" class="flex gap-2 pt-1">
        @csrf
        <div class="relative flex-1">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">$</span>
            <input type="number" name="amount" step="0.01" min="0.01"
                   max="{{ $invoice->remaining() }}"
                   placeholder="{{ number_format($invoice->remaining(), 2) }}"
                   class="w-full border rounded-xl pl-7 pr-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none
                          {{ $errors->has('amount') ? 'border-red-400' : 'border-gray-300' }}">
        </div>
        <button type="submit"
                class="bg-indigo-600 text-white text-sm font-semibold px-4 py-2.5 rounded-xl active:scale-95 transition-transform whitespace-nowrap">
            {{ __('Record Payment') }}
        </button>
    </form>
    @error('amount')
        <p class="text-xs text-red-500">{{ $message }}</p>
    @enderror
    @endif
</div>
@endif

{{-- Line items --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-4">
    <h2 class="font-semibold text-gray-700 mb-3 text-sm uppercase tracking-wide">{{ __('Items') }}</h2>
    <div class="space-y-3">
        @foreach ($invoice->items as $line)
            <div class="flex items-center gap-3">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <p class="font-medium text-gray-900 text-sm">{{ $line->item->name }}</p>
                        @if ($line->unit_price == 0)
                            <span class="text-xs bg-green-100 text-green-700 font-semibold px-2 py-0.5 rounded-full">🎁 {{ __('FREE') }}</span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500" style="direction:ltr;unicode-bidi:bidi-override">
                        @if ($line->unit_price == 0)
                            × {{ $line->quantity }}
                        @else
                            ${{ number_format($line->unit_price, 2) }} × {{ $line->quantity }}
                        @endif
                    </p>
                </div>
                <p class="font-bold shrink-0 {{ $line->unit_price == 0 ? 'text-green-600' : 'text-gray-900' }}" style="direction:ltr;unicode-bidi:bidi-override">
                    {{ $line->unit_price == 0 ? __('FREE') : '$' . number_format($line->subtotal, 2) }}
                </p>
            </div>
        @endforeach
    </div>
    <div class="mt-4 pt-3 border-t border-gray-200 space-y-2">
        @if ($invoice->discount > 0)
        @php
            $subtotal = $invoice->total_amount + $invoice->discount;
            $pct = $subtotal > 0 ? round($invoice->discount / $subtotal * 100, 1) : 0;
        @endphp
        <div class="flex justify-between items-center text-sm text-gray-400">
            <span>{{ __('Subtotal') }}</span>
            <span style="direction:ltr;unicode-bidi:bidi-override">${{ number_format($subtotal, 2) }}</span>
        </div>
        <div class="flex justify-between items-center text-sm text-red-500 font-medium">
            <span>{{ __('Discount') }} ({{ $pct }}%)</span>
            <span style="direction:ltr;unicode-bidi:bidi-override">− ${{ number_format($invoice->discount, 2) }}</span>
        </div>
        @endif
        <div class="flex justify-between items-center">
            <span class="font-bold text-gray-700 text-lg">{{ __('Total') }}</span>
            <span class="font-bold text-indigo-600 text-2xl" style="direction:ltr;unicode-bidi:bidi-override">${{ number_format($invoice->total_amount, 2) }}</span>
        </div>
        @if ($invoice->status === 'pending')
            @if ($invoice->amount_paid > 0)
            <div class="flex justify-between items-center text-sm">
                <span class="text-green-600 font-medium">{{ __('Paid') }}</span>
                <span class="text-green-600 font-bold" style="direction:ltr;unicode-bidi:bidi-override">${{ number_format($invoice->amount_paid, 2) }}</span>
            </div>
            @endif
            <div class="flex justify-between items-center text-sm border-t border-dashed border-orange-200 pt-2">
                <span class="text-orange-600 font-semibold">{{ __('Remaining') }}</span>
                <span class="text-orange-600 font-bold text-lg" style="direction:ltr;unicode-bidi:bidi-override">${{ number_format($invoice->remaining(), 2) }}</span>
            </div>
        @endif
    </div>
</div>

{{-- Share via WhatsApp --}}
<button type="button" onclick="shareInvoice()"
        class="w-full flex items-center justify-center gap-2 bg-green-500 active:bg-green-600 text-white font-semibold py-3 rounded-2xl shadow transition-all mb-2">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
    </svg>
    {{ __('Send via WhatsApp') }}
</button>

{{-- Delete --}}
<form method="POST" action="{{ route('invoices.destroy', $invoice) }}"
      onsubmit="return confirm('{{ __('Delete this invoice? Stock will NOT be restored.') }}')">
    @csrf @method('DELETE')
    <button type="submit" class="w-full text-red-500 text-sm py-3 font-medium">{{ __('Delete Invoice') }}</button>
</form>

<script>
async function shareInvoice() {
    const pdfUrl = '{{ route('invoices.pdf', $invoice) }}';
    const filename = 'invoice-{{ $invoice->id }}.pdf';

    try {
        const res = await fetch(pdfUrl);
        const blob = await res.blob();
        const file = new File([blob], filename, { type: 'application/pdf' });

        if (navigator.share && navigator.canShare && navigator.canShare({ files: [file] })) {
            await navigator.share({ files: [file], title: filename });
        } else {
            // fallback: download the PDF
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = filename;
            a.click();
        }
    } catch (e) {
        // user cancelled or share failed — open PDF in new tab
        window.open(pdfUrl, '_blank');
    }
}
</script>
@endsection
