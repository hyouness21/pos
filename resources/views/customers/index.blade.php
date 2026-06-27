@extends('layouts.app')
@section('title', 'Customers')

@section('header-actions')
    <a href="{{ route('customers.best') }}"
       class="bg-yellow-400 text-yellow-900 text-sm font-semibold px-3 py-1 rounded-lg">⭐ {{ __('Best') }}</a>
    <a href="{{ route('customers.create') }}"
       class="bg-white text-indigo-600 text-sm font-semibold px-3 py-1 rounded-lg">+ {{ __('New') }}</a>
@endsection

@section('content')
<form id="cust-filter" method="GET" class="relative mb-3">
    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
    </span>
    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="{{ __('Search clients…') }}"
           oninput="liveFilter('cust-filter','cust-results')"
           class="w-full border border-gray-200 rounded-xl pl-9 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
</form>

<div id="cust-results">
    <div class="space-y-3">
        @forelse ($customers as $customer)
            <a href="{{ route('customers.show', $customer) }}"
               class="bg-white rounded-2xl shadow-sm border border-gray-100 flex items-center gap-3 p-3 active:bg-gray-50">
                <div class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center shrink-0">
                    <span class="text-indigo-600 font-bold text-lg">{{ strtoupper(substr($customer->name, 0, 1)) }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-900 truncate">{{ $customer->name }}</p>
                    <p class="text-sm text-gray-500">{{ $customer->phone ?? $customer->email ?? '—' }}</p>
                </div>
                <div class="text-right shrink-0">
                    <p class="text-sm font-semibold text-gray-900">{{ $customer->invoices_count }} invoice{{ $customer->invoices_count !== 1 ? 's' : '' }}</p>
                    <p class="text-xs text-gray-500">${{ number_format($customer->invoices_sum_total_amount ?? 0, 2) }} total</p>
                </div>
            </a>
        @empty
            <div class="text-center py-16 text-gray-400">
                <p class="font-medium">{{ __('No customers yet') }}</p>
                <a href="{{ route('customers.create') }}" class="text-indigo-600 text-sm mt-1 inline-block">{{ __('Add one') }}</a>
            </div>
        @endforelse
    </div>
    {{ $customers->links() }}
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
