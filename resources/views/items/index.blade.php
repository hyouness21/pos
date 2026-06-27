@extends('layouts.app')

@php
    $activeCategory = $categories->firstWhere('id', request('category'));
@endphp

@section('title', $activeCategory ? $activeCategory->name : 'Inventory')

@section('header-actions')
    <a href="{{ route('items.create') }}"
       class="bg-white text-indigo-600 text-sm font-semibold px-3 py-1 rounded-lg">+ {{ __('New') }}</a>
@endsection

@section('content')

{{-- Search --}}
<form id="item-filter" method="GET" class="relative mb-3">
    @foreach(request()->except(['search','page']) as $k => $v)
        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
    @endforeach
    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
    </span>
    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="{{ __('Search items…') }}"
           oninput="itemFilter()"
           class="w-full border border-gray-200 rounded-xl pl-9 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
</form>

{{-- Back to categories (when drilling in from a category) --}}
@if ($activeCategory)
    <a href="{{ route('categories.index') }}"
       class="flex items-center gap-1.5 text-indigo-600 text-sm font-medium mb-3">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        {{ __('All Categories') }}
    </a>
@endif

{{-- Category pill tabs --}}
<div class="flex gap-2 overflow-x-auto pb-2 mb-3 scrollbar-hide">
    <a href="{{ route('items.index', array_filter(['low_stock' => request('low_stock'), 'search' => request('search')])) }}"
       class="shrink-0 px-3 py-1.5 rounded-full text-sm font-medium transition-colors
              {{ !request('category') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600' }}">
        {{ __('All') }}
    </a>
    @foreach ($categories as $cat)
        <a href="{{ route('items.index', array_filter(['category' => $cat->id, 'low_stock' => request('low_stock'), 'search' => request('search')])) }}"
           class="shrink-0 px-3 py-1.5 rounded-full text-sm font-medium transition-colors
                  {{ request('category') == $cat->id ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600' }}">
            {{ $cat->name }}
        </a>
    @endforeach
</div>

{{-- Low stock toggle --}}
<div class="mb-4">
    <label class="flex items-center gap-2 text-sm cursor-pointer w-fit">
        <input type="checkbox" id="low-stock-cb" name="low_stock" value="1"
               onchange="itemFilter(0)"
               {{ request()->boolean('low_stock') ? 'checked' : '' }}
               class="rounded text-indigo-600">
        <span class="text-gray-600 font-medium">{{ __('Low Stock Only') }}</span>
    </label>
</div>

<div id="item-results">
<div class="space-y-3">
    @forelse ($items as $item)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="flex items-center gap-3 p-3">
                @if ($item->image)
                    <img src="{{ Storage::url($item->image) }}" class="w-14 h-14 rounded-xl object-cover shrink-0">
                @else
                    <div class="w-14 h-14 rounded-xl bg-gray-100 flex items-center justify-center shrink-0 text-gray-300">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"/>
                        </svg>
                    </div>
                @endif
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-900 truncate">{{ $item->name }}</p>
                    @unless ($activeCategory)
                        <p class="text-xs text-gray-400">{{ $item->category->name }}</p>
                    @endunless
                    <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                        <span class="text-indigo-600 font-bold text-sm">${{ number_format($item->price, 2) }}</span>
                        <span class="text-xs px-2 py-0.5 rounded-full
                            {{ $item->isLowStock() ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                            {{ $item->stock }} {{ __('in stock') }}
                        </span>
                        @if ($item->expiry_date)
                            @php $exDays = $item->daysUntilExpiry(); $exStatus = $item->expiryStatus(); @endphp
                            @if ($exStatus === 'expired')
                                <span class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-700 font-semibold">
                                    ⚠ {{ __('Expired') }} · {{ $item->expiry_date->format('d M Y') }}
                                </span>
                            @elseif ($exStatus === 'soon')
                                <span class="text-xs px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 font-semibold">
                                    ⏳ {{ $exDays === 0 ? __('Expires today') : __('Expires in :d days', ['d' => $exDays]) }} · {{ $item->expiry_date->format('d M Y') }}
                                </span>
                            @else
                                <span class="text-xs text-gray-400">{{ __('Expiry Date') }}: {{ $item->expiry_date->format('d M Y') }}</span>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex border-t border-gray-100">
                <a href="{{ route('items.edit', $item) }}"
                   class="flex-1 text-center text-sm text-indigo-600 font-medium py-2 active:bg-indigo-50">{{ __('Edit') }}</a>
                <div class="w-px bg-gray-100"></div>
                <form method="POST" action="{{ route('items.destroy', $item) }}"
                      onsubmit="return confirm('{{ __('Delete this item?') }}')" class="flex-1">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="w-full text-sm text-red-500 font-medium py-2 active:bg-red-50">{{ __('Delete') }}</button>
                </form>
            </div>
        </div>
    @empty
        <div class="text-center py-16 text-gray-400">
            <p class="font-medium">{{ __('No items found') }}</p>
            <a href="{{ route('items.create') }}" class="text-indigo-600 text-sm mt-1 inline-block">{{ __('Add one') }}</a>
        </div>
    @endforelse
</div>

{{ $items->links() }}
</div>{{-- #item-results --}}

<script>
(function(){
    var _t;
    window.itemFilter = function(delay) {
        clearTimeout(_t);
        _t = setTimeout(function() {
            var params = new URLSearchParams(new FormData(document.getElementById('item-filter')));
            var cb = document.getElementById('low-stock-cb');
            if (cb && cb.checked) params.set('low_stock', '1');
            else params.delete('low_stock');
            for (var [k,v] of [...params]) { if (!v) params.delete(k); }
            var url = '?' + params.toString();
            fetch(url, {headers:{'X-Requested-With':'XMLHttpRequest'}})
                .then(function(r){return r.text();})
                .then(function(html){
                    var doc = new DOMParser().parseFromString(html,'text/html');
                    var res = doc.getElementById('item-results');
                    if (res) document.getElementById('item-results').innerHTML = res.innerHTML;
                    history.replaceState(null,'',url||'?');
                });
        }, delay !== undefined ? delay : 400);
    };
})();
</script>
@endsection
