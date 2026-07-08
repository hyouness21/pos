@extends('layouts.app')
@section('title', 'Categories')

@section('header-actions')
    <a href="{{ route('categories.create') }}"
       class="bg-white text-indigo-600 text-sm font-semibold px-3 py-1 rounded-lg">+ {{ __('New') }}</a>
@endsection

@section('content')
<form id="cat-filter" method="GET" class="relative mb-3">
    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
    </span>
    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="{{ __('Search categories…') }}"
           oninput="liveFilter('cat-filter','cat-results')"
           class="w-full border border-gray-200 rounded-xl pl-9 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
</form>

<div id="cat-results">
    <div class="space-y-3">
        @forelse ($categories as $category)
            <div x-data="{ open: false }" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <a href="{{ route('items.index', ['category' => $category->id]) }}"
                   class="flex items-center gap-3 p-3 active:bg-gray-50">
                    @if ($category->image)
                        <img src="{{ Storage::url($category->image) }}" class="w-14 h-14 rounded-xl object-cover shrink-0">
                    @else
                        <div class="w-14 h-14 rounded-xl bg-indigo-50 flex items-center justify-center shrink-0">
                            <svg class="w-7 h-7 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-900 truncate">{{ $category->name }}</p>
                        <p class="text-sm text-gray-500">{{ $category->items_count }} item{{ $category->items_count !== 1 ? 's' : '' }}</p>
                    </div>
                    <svg class="w-5 h-5 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                <div class="flex border-t border-gray-100">
                    <a href="{{ route('categories.edit', $category) }}"
                       class="flex-1 text-center text-sm text-indigo-600 font-medium py-2 active:bg-indigo-50">{{ __('Edit') }}</a>
                    <div class="w-px bg-gray-100"></div>
                    <button type="button" @click="open = true"
                            class="flex-1 text-sm text-red-500 font-medium py-2 active:bg-red-50">{{ __('Delete') }}</button>
                </div>

                <div x-show="open" x-cloak
                     class="fixed inset-0 z-50 flex items-end justify-center bg-black/50 pb-6 px-4">
                    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-5 space-y-4">
                        <h2 class="font-bold text-gray-900 text-lg">{{ __('Delete Category') }}</h2>
                        <p class="text-sm text-gray-500">{{ __('Are you sure? This cannot be undone.') }}</p>
                        <div class="grid grid-cols-2 gap-3">
                            <button type="button" @click="open = false"
                                    class="py-3 rounded-xl border border-gray-200 text-gray-600 font-semibold text-sm">
                                {{ __('Cancel') }}
                            </button>
                            <form method="POST" action="{{ route('categories.destroy', $category) }}">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="w-full py-3 rounded-xl bg-red-500 text-white font-semibold text-sm active:scale-95 transition-transform">
                                    {{ __('Delete') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-16 text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <p class="font-medium">{{ __('No categories yet') }}</p>
                <a href="{{ route('categories.create') }}" class="text-indigo-600 text-sm mt-1 inline-block">Create one</a>
            </div>
        @endforelse
    </div>
</div>

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
