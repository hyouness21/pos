@extends('layouts.app')
@section('title', 'Storehouse')

@section('header-actions')
    <a href="{{ route('items.create') }}"
       class="bg-white text-indigo-600 text-sm font-semibold px-3 py-1 rounded-lg">+ {{ __('Item') }}</a>
@endsection

@section('content')

{{-- Search --}}
<form id="store-filter" method="GET" class="relative mb-4">
    @foreach(request()->except(['search','page']) as $k => $v)
        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
    @endforeach
    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
    </span>
    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="{{ __('Search items…') }}"
           oninput="liveFilter('store-filter','store-results')"
           class="w-full border border-gray-200 rounded-xl pl-9 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
</form>

{{-- Stats strip --}}
<div class="grid grid-cols-2 gap-3 mb-5">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <p class="text-xs text-gray-400 mb-0.5">{{ __('Sell Value') }}</p>
        <p class="text-xl font-bold text-indigo-600">${{ number_format($stats['total_value'], 2) }}</p>
        <p class="text-xs text-gray-400 mt-0.5">{{ number_format($stats['total_units']) }} units · {{ $stats['total_skus'] }} SKUs</p>
    </div>
    @php $profit = $stats['total_value'] - $stats['total_cost']; @endphp
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <p class="text-xs text-gray-400 mb-0.5">{{ __('Profit Potential') }}</p>
        <p class="text-xl font-bold {{ $profit >= 0 ? 'text-green-600' : 'text-red-500' }}">
            ${{ number_format($profit, 2) }}
        </p>
        <p class="text-xs text-gray-400 mt-0.5">{{ __('if all stock sold') }}</p>
    </div>
</div>
<div class="grid grid-cols-2 gap-3 mb-5">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <p class="text-xs text-gray-400 mb-0.5">{{ __('Stock Cost') }}</p>
        <p class="text-xl font-bold text-gray-700">${{ number_format($stats['total_cost'], 2) }}</p>
        <p class="text-xs text-gray-400 mt-0.5">{{ __('total invested') }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <p class="text-xs text-gray-400 mb-0.5">{{ __('Alerts') }}</p>
        <p class="text-xl font-bold {{ $stats['low_stock'] > 0 ? 'text-orange-500' : 'text-green-500' }}">
            {{ $stats['low_stock'] }} {{ __('low') }}
        </p>
        <p class="text-xs text-gray-400 mt-0.5">{{ $stats['out_of_stock'] }} {{ __('Out of stock') }}</p>
        @if ($stats['expiring_soon'] > 0)
            <p class="text-xs text-amber-600 font-semibold mt-0.5">⏳ {{ $stats['expiring_soon'] }} {{ __('expiring soon') }}</p>
        @endif
    </div>
</div>

{{-- Category tabs --}}
<div class="flex gap-2 overflow-x-auto pb-2 mb-4 scrollbar-hide">
    <a href="{{ route('storehouse') }}"
       class="shrink-0 px-3 py-1.5 rounded-full text-sm font-medium transition-colors
              {{ !$activeCategory ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600' }}">
        {{ __('All') }}
    </a>
    @foreach ($categories as $cat)
        <a href="{{ route('storehouse', ['category' => $cat->id]) }}"
           class="shrink-0 px-3 py-1.5 rounded-full text-sm font-medium transition-colors
                  {{ $activeCategory == $cat->id ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600' }}">
            {{ $cat->name }}
        </a>
    @endforeach
</div>

<div id="store-results">
{{-- Items grouped by category --}}
@foreach ($categories as $cat)
    @php
        $items = $activeCategory ? $cat->items->where('category_id', $activeCategory) : $cat->items;
        if ($activeCategory && $cat->id != $activeCategory) continue;
    @endphp

    @php
        $catCost = $cat->items->sum(fn($i) => (float)($i->cost_price ?? 0) * $i->stock);
        $catSell = $cat->items->sum(fn($i) => (float)$i->price * $i->stock);
        $catHasCost = $cat->items->whereNotNull('cost_price')->count() > 0;
    @endphp
    <div class="mb-5">
        <div class="flex items-center justify-between mb-2">
            <h2 class="font-bold text-gray-800 text-sm uppercase tracking-wide">{{ $cat->name }}</h2>
            <span class="text-xs text-gray-400">{{ $cat->items->count() }} item{{ $cat->items->count() !== 1 ? 's' : '' }}</span>
        </div>

        {{-- Category financials (only when a specific category is active) --}}
        @if ($activeCategory)
        @php $catEarning = $catSell - $catCost; @endphp
        <div class="grid grid-cols-3 gap-2 mb-3">
            <div class="bg-gray-50 rounded-xl px-3 py-2">
                <p class="text-xs text-gray-400 mb-0.5">{{ __('Stock Cost') }}</p>
                @if ($catHasCost)
                    <p class="text-sm font-bold text-gray-700">${{ number_format($catCost, 2) }}</p>
                @else
                    <p class="text-xs text-gray-400 italic">—</p>
                @endif
                <p class="text-xs text-gray-400">{{ __('what you paid') }}</p>
            </div>
            <div class="bg-indigo-50 rounded-xl px-3 py-2">
                <p class="text-xs text-indigo-400 mb-0.5">{{ __('Sell Value') }}</p>
                <p class="text-sm font-bold text-indigo-600">${{ number_format($catSell, 2) }}</p>
                <p class="text-xs text-indigo-400">{{ __('if all stock sold') }}</p>
            </div>
            <div class="{{ $catHasCost ? ($catEarning >= 0 ? 'bg-green-50' : 'bg-red-50') : 'bg-gray-50' }} rounded-xl px-3 py-2">
                <p class="text-xs mb-0.5 {{ $catHasCost ? ($catEarning >= 0 ? 'text-green-500' : 'text-red-400') : 'text-gray-400' }}">{{ __('Earnings') }}</p>
                @if ($catHasCost)
                    <p class="text-sm font-bold {{ $catEarning >= 0 ? 'text-green-600' : 'text-red-500' }}">${{ number_format($catEarning, 2) }}</p>
                @else
                    <p class="text-xs text-gray-400 italic">—</p>
                @endif
                <p class="text-xs {{ $catHasCost ? ($catEarning >= 0 ? 'text-green-400' : 'text-red-400') : 'text-gray-400' }}">{{ __('Profit Potential') }}</p>
            </div>
        </div>
        @endif

        <div class="space-y-2">
            @forelse ($cat->items as $item)
                @php
                    $pct      = $item->low_stock_threshold > 0
                                ? min(100, round($item->stock / $item->low_stock_threshold * 100))
                                : 100;
                    $barColor = $item->stock === 0       ? 'bg-red-500'
                              : ($item->isLowStock()     ? 'bg-orange-400'
                              :                            'bg-green-500');
                    $badge    = $item->stock === 0       ? ['bg-red-100 text-red-700',    __('Out of stock')]
                              : ($item->isLowStock()     ? ['bg-orange-100 text-orange-700', __('Low stock')]
                              :                            ['bg-green-100 text-green-700',   'OK']);
                    $exStatus = $item->expiryStatus();
                    $exDays   = $item->daysUntilExpiry();
                @endphp

                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-3
                            {{ $exStatus === 'expired' ? 'border-red-200' : ($exStatus === 'soon' ? 'border-amber-200' : '') }}"
                     x-data="{ editing: false, stock: {{ $item->stock }} }">

                    <div class="flex items-start gap-3 mb-2">
                        {{-- Item image --}}
                        @if ($item->image)
                            <img src="{{ Storage::url($item->image) }}"
                                 class="w-11 h-11 rounded-xl object-cover shrink-0">
                        @else
                            <div class="w-11 h-11 rounded-xl bg-gray-100 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                        @endif

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2">
                                <p class="font-semibold text-gray-900 text-sm truncate">{{ $item->name }}</p>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    @if ($exStatus === 'expired')
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-700 font-semibold">⚠ {{ __('Expired') }}</span>
                                    @elseif ($exStatus === 'soon')
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 font-semibold">
                                            ⏳ {{ $exDays === 0 ? __('Expires today') : __('Expires in :d days', ['d' => $exDays]) }}
                                        </span>
                                    @endif
                                    <span class="text-xs px-2 py-0.5 rounded-full {{ $badge[0] }}">
                                        {{ $badge[1] }}
                                    </span>
                                </div>
                            </div>
                            @if ($item->expiry_date)
                            <div class="mt-0.5">
                                @if ($exStatus === 'expired')
                                    <span class="text-xs text-red-600 font-medium">⚠ {{ __('Expired') }}: {{ $item->expiry_date->format('d M Y') }}</span>
                                @elseif ($exStatus === 'soon')
                                    <span class="text-xs text-amber-600 font-medium">⏳ {{ $exDays === 0 ? __('Expires today') : __('Expires in :d days', ['d' => $exDays]) }} · {{ $item->expiry_date->format('d M Y') }}</span>
                                @else
                                    <span class="text-xs text-gray-400">{{ __('Expiry Date') }}: {{ $item->expiry_date->format('d M Y') }}</span>
                                @endif
                            </div>
                            @endif
                            <div class="flex items-center gap-3 mt-1 flex-wrap">
                                @if ($item->cost_price !== null)
                                    <span class="text-xs text-gray-400">Cost <span class="font-medium text-gray-600">${{ number_format($item->cost_price, 2) }}</span></span>
                                    <span class="text-xs text-gray-300">→</span>
                                    <span class="text-xs text-gray-400">Sell <span class="font-medium text-indigo-600">${{ number_format($item->price, 2) }}</span></span>
                                    @if ($item->profit() !== null)
                                        <span class="text-xs px-1.5 py-0.5 rounded-md {{ $item->profit() >= 0 ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-600' }} font-semibold">
                                            +${{ number_format($item->profit(), 2) }} ({{ $item->marginPercent() }}%)
                                        </span>
                                    @endif
                                @else
                                    <span class="text-xs text-indigo-600 font-medium">${{ number_format($item->price, 2) }} each</span>
                                    <span class="text-xs text-gray-400">{{ __('— no cost set') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Stock bar --}}
                    <div class="flex items-center gap-3 mb-3">
                        <div class="flex-1 bg-gray-100 rounded-full h-2 overflow-hidden">
                            <div class="{{ $barColor }} h-2 rounded-full transition-all"
                                 style="width: {{ $pct }}%"></div>
                        </div>
                        <span class="text-sm font-bold text-gray-700 w-16 text-right shrink-0">
                            <span x-text="stock"></span> / {{ $item->low_stock_threshold }}
                        </span>
                    </div>

                    {{-- Quick restock controls --}}
                    <div x-show="!editing" class="flex gap-2">
                        <button type="button" @click="editing = true"
                                class="flex-1 text-sm text-indigo-600 font-medium py-1.5 bg-indigo-50 rounded-xl active:bg-indigo-100">
                            ✏ {{ __('Adjust Stock') }}
                        </button>
                        <a href="{{ route('items.edit', $item) }}"
                           class="px-4 text-sm text-gray-500 font-medium py-1.5 bg-gray-50 rounded-xl">
                            {{ __('Edit') }}
                        </a>
                    </div>

                    <div x-show="editing" x-cloak class="flex gap-2 items-center">
                        <button type="button" @click="stock = Math.max(0, stock - 1)"
                                class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center text-gray-700 font-bold text-lg active:bg-gray-200">
                            −
                        </button>
                        <input type="number" x-model.number="stock" min="0"
                               class="flex-1 border border-gray-300 rounded-xl px-3 py-2 text-center text-sm font-bold focus:ring-2 focus:ring-indigo-500 outline-none">
                        <button type="button" @click="stock = stock + 1"
                                class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-lg active:bg-indigo-200">
                            +
                        </button>

                        <form method="POST" action="{{ route('items.stock', $item) }}" class="contents">
                            @csrf @method('PATCH')
                            <input type="hidden" name="stock" :value="stock">
                            <button type="submit"
                                    class="px-4 h-10 bg-indigo-600 text-white text-sm font-semibold rounded-xl active:scale-95 transition-transform">
                                Save
                            </button>
                        </form>

                        <button type="button" @click="editing = false; stock = {{ $item->stock }}"
                                class="px-3 h-10 text-gray-400 text-sm rounded-xl active:bg-gray-100">
                            ✕
                        </button>
                    </div>

                </div>
            @empty
                <p class="text-center text-gray-400 text-sm py-4">{{ __('No items in this category.') }}</p>
            @endforelse
        </div>
    </div>
@endforeach
</div>{{-- #store-results --}}

<script>
(function(){
    var _t;
    window.liveFilter = function(formId, resultsId, delay) {
        clearTimeout(_t);
        _t = setTimeout(function() {
            var params = new URLSearchParams(new FormData(document.getElementById(formId)));
            for (var [k,v] of [...params]) { if (!v) params.delete(k); }
            var url = '?' + params.toString();
            fetch(url, {headers:{'X-Requested-With':'XMLHttpRequest'}})
                .then(function(r){return r.text();})
                .then(function(html){
                    var doc = new DOMParser().parseFromString(html,'text/html');
                    var res = doc.getElementById(resultsId);
                    if (res) document.getElementById(resultsId).innerHTML = res.innerHTML;
                    history.replaceState(null,'',url||'?');
                });
        }, delay !== undefined ? delay : 400);
    };
})();
</script>
@endsection
