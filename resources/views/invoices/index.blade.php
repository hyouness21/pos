@extends('layouts.app')
@section('title', 'Invoices')

@section('content')

{{-- Summary cards --}}
<div id="summary-cards" class="grid grid-cols-3 gap-2 mb-4">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-3 text-center">
        <p class="text-xs text-gray-400 mb-0.5">{{ __('Invoices') }}</p>
        <p class="text-xl font-bold text-gray-800">{{ $summary->count }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-3 text-center">
        <p class="text-xs text-gray-400 mb-0.5">{{ __('Paid') }}</p>
        <p class="text-lg font-bold text-green-600" style="direction:ltr;unicode-bidi:bidi-override">${{ number_format($summary->paid, 2) }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-3 text-center">
        <p class="text-xs text-gray-400 mb-0.5">{{ __('Pending') }}</p>
        <p class="text-lg font-bold text-orange-500" style="direction:ltr;unicode-bidi:bidi-override">${{ number_format($summary->pending, 2) }}</p>
    </div>
    <div class="col-span-3 bg-indigo-50 border border-indigo-100 rounded-2xl p-3">
        <p class="text-xs text-indigo-500 font-medium mb-0.5">{{ __('Total Revenue') }}</p>
        <p class="text-2xl font-bold text-indigo-600 truncate" style="direction:ltr;unicode-bidi:bidi-override">${{ number_format($summary->total, 2) }}</p>
    </div>
</div>

{{-- Filters --}}
<form id="filter-form" method="GET" class="space-y-2 mb-4">
    <div class="relative">
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </span>
        <input type="number" name="search" id="filter-search" value="{{ request('search') }}"
               placeholder="{{ __('Invoice #') }}…"
               oninput="liveFilter()"
               class="w-full border border-gray-200 rounded-xl pl-9 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
    </div>
    <div class="relative">
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        </span>
        <input type="text" name="customer" id="filter-customer" value="{{ request('customer') }}"
               placeholder="{{ __('Search clients…') }}"
               oninput="liveFilter()"
               class="w-full border border-gray-200 rounded-xl pl-9 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
    </div>
    <div class="flex gap-2">
        <select name="status" onchange="liveFilter(0)"
                class="flex-1 border border-gray-300 rounded-xl px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-indigo-500 outline-none">
            <option value="">{{ __('All Status') }}</option>
            <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>{{ __('Paid') }}</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
        </select>
        <input type="date" name="date" value="{{ request('date') }}" onchange="liveFilter(0)"
               class="flex-1 border border-gray-300 rounded-xl px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-indigo-500 outline-none">
    </div>
    <div class="flex gap-2 items-center">
        <input type="month" name="month" value="{{ request('month') }}" onchange="liveFilter(0)"
               class="flex-1 border border-gray-300 rounded-xl px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-indigo-500 outline-none">
        @if(request()->hasAny(['status','date','month','search','customer']))
            <a href="{{ route('invoices.index') }}"
               class="text-sm text-gray-400 hover:text-red-500 whitespace-nowrap">{{ __('Clear') }}</a>
        @endif
    </div>
</form>

<div id="invoice-results">
    <div class="space-y-3">
        @forelse ($invoices as $invoice)
            <a href="{{ route('invoices.show', $invoice) }}"
               class="bg-white rounded-2xl shadow-sm border border-gray-100 flex items-center gap-3 p-3 active:bg-gray-50">
                <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0
                    {{ $invoice->status === 'paid' ? 'bg-green-100' : 'bg-orange-100' }}">
                    <svg class="w-5 h-5 {{ $invoice->status === 'paid' ? 'text-green-600' : 'text-orange-500' }}"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        @if($invoice->status === 'paid')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        @else
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        @endif
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-900">#{{ $invoice->id }} — {{ $invoice->customer->name }}</p>
                    <p class="text-xs text-gray-500">{{ $invoice->created_at->format('d M Y, H:i') }}</p>
                </div>
                <div class="text-right shrink-0">
                    <p class="font-bold text-gray-900" style="direction:ltr;unicode-bidi:bidi-override">${{ number_format($invoice->total_amount, 2) }}</p>
                    @if ($invoice->status === 'pending' && $invoice->amount_paid > 0)
                        <p class="text-xs text-orange-600 font-medium" style="direction:ltr;unicode-bidi:bidi-override">${{ number_format($invoice->remaining(), 2) }} {{ __('left') }}</p>
                    @else
                        <span class="text-xs px-2 py-0.5 rounded-full
                            {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700' }}">
                            {{ ucfirst($invoice->status) }}
                        </span>
                    @endif
                </div>
            </a>
        @empty
            <div class="text-center py-16 text-gray-400">
                <p class="font-medium">{{ __('No invoices yet.') }}</p>
            </div>
        @endforelse
    </div>
    {{ $invoices->links() }}
</div>

<script>
(function () {
    let _timer = null;

    window.liveFilter = function (delay) {
        clearTimeout(_timer);
        _timer = setTimeout(function () {
            const form   = document.getElementById('filter-form');
            const params = new URLSearchParams(new FormData(form));

            // remove empty values to keep the URL clean
            for (const [k, v] of [...params]) {
                if (!v) params.delete(k);
            }

            const url = '?' + params.toString();

            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.text(); })
                .then(function (html) {
                    const doc     = new DOMParser().parseFromString(html, 'text/html');
                    const results = doc.getElementById('invoice-results');
                    const summary = doc.getElementById('summary-cards');

                    if (results) document.getElementById('invoice-results').innerHTML = results.innerHTML;
                    if (summary) document.getElementById('summary-cards').innerHTML   = summary.innerHTML;

                    history.replaceState(null, '', url || '?');
                });
        }, delay !== undefined ? delay : 400);
    };
})();
</script>

@endsection
