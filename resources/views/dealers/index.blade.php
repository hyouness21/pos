@extends('layouts.app')
@section('title', 'Dealers')

@section('header-actions')
    <a href="{{ route('dealers.create') }}"
       class="bg-white text-indigo-600 text-sm font-semibold px-3 py-1 rounded-lg">+ {{ __('New') }}</a>
@endsection

@section('content')
<form method="GET" class="relative mb-3">
    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
    </span>
    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="{{ __('Search dealers…') }}"
           oninput="clearTimeout(window._st);window._st=setTimeout(()=>this.form.submit(),400)"
           class="w-full border border-gray-200 rounded-xl pl-9 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
</form>
<div class="space-y-3">
    @forelse ($dealers as $dealer)
        <a href="{{ route('dealers.show', $dealer) }}"
           class="bg-white rounded-2xl shadow-sm border border-gray-100 flex items-center gap-3 p-3 active:bg-gray-50">
            <div class="w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center shrink-0">
                <span class="text-emerald-600 font-bold text-lg">{{ strtoupper(substr($dealer->name, 0, 1)) }}</span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-semibold text-gray-900 truncate">{{ $dealer->name }}</p>
                <p class="text-sm text-gray-500">{{ $dealer->phone ?? $dealer->email ?? '—' }}</p>
            </div>
            <div class="text-right shrink-0">
                <p class="text-sm font-semibold text-gray-900">{{ $dealer->purchases_count }} orders</p>
                <p class="text-xs text-gray-500">${{ number_format($dealer->purchases_sum_total_amount ?? 0, 2) }}</p>
            </div>
        </a>
    @empty
        <div class="text-center py-16 text-gray-400">
            <p class="font-medium">{{ __('No dealers yet') }}</p>
            <a href="{{ route('dealers.create') }}" class="text-indigo-600 text-sm mt-1 inline-block">{{ __('Add one') }}</a>
        </div>
    @endforelse
</div>
{{ $dealers->links() }}
@endsection
