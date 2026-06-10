@extends('layouts.app')
@section('title', 'Reports')

@section('content')

{{-- Summary cards --}}
<div class="grid grid-cols-2 gap-3 mb-4">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <p class="text-xs text-gray-500 mb-1">{{ __("Today's Revenue") }}</p>
        <p class="text-2xl font-bold text-indigo-600">${{ number_format($stats['today_revenue'], 2) }}</p>
        <p class="text-xs text-gray-400 mt-0.5">{{ $stats['today_invoices'] }} invoice{{ $stats['today_invoices'] !== 1 ? 's' : '' }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4
                {{ $stats['today_profit'] > 0 ? 'bg-green-50 border-green-200' : '' }}">
        <p class="text-xs text-gray-500 mb-1">{{ __("Today's Earnings") }}</p>
        <p class="text-2xl font-bold {{ $stats['today_profit'] >= 0 ? 'text-green-600' : 'text-red-500' }}">
            ${{ number_format($stats['today_profit'], 2) }}
        </p>
        <p class="text-xs text-gray-400 mt-0.5">{{ $stats['today_margin'] }}% margin</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <p class="text-xs text-gray-500 mb-1">{{ __('Pending Debt') }}</p>
        <p class="text-2xl font-bold {{ $stats['pending_debt'] > 0 ? 'text-red-500' : 'text-green-500' }}">
            ${{ number_format($stats['pending_debt'], 2) }}
        </p>
        <p class="text-xs text-gray-400 mt-0.5">{{ $stats['pending_count'] }} unpaid</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <p class="text-xs text-gray-500 mb-1">{{ __('Low Stock Items') }}</p>
        <p class="text-2xl font-bold {{ $stats['low_stock_count'] > 0 ? 'text-orange-500' : 'text-green-500' }}">
            {{ $stats['low_stock_count'] }}
        </p>
        <a href="{{ route('items.index', ['low_stock' => 1]) }}" class="text-xs text-indigo-600 mt-0.5 block">View all</a>
    </div>
</div>

{{-- Expenses shortcut --}}
<a href="{{ route('expenses.index') }}"
   class="bg-red-50 border border-red-100 rounded-2xl p-4 flex items-center gap-3 mb-4 active:bg-red-100">
    <span class="text-2xl">💸</span>
    <div class="flex-1">
        <p class="font-bold text-sm text-red-700">{{ __('Expenses') }}</p>
        <p class="text-xs text-red-400">${{ number_format($stats['today_expenses'] ?? 0, 2) }} {{ __('today') }} · ${{ number_format($stats['month_expenses'] ?? 0, 2) }} {{ __('this month') }}</p>
    </div>
    <svg class="w-5 h-5 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
</a>

{{-- Earnings section --}}
<h2 class="font-semibold text-gray-700 mb-2 text-sm uppercase tracking-wide">{{ __('Earnings') }}</h2>
<div class="grid grid-cols-2 gap-2 mb-4">
    <a href="{{ route('reports.earnings-daily') }}"
       class="bg-green-600 text-white rounded-2xl p-4 flex flex-col gap-1 active:opacity-90">
        <span class="text-2xl">💰</span>
        <p class="font-bold text-sm">{{ __('Daily Earnings') }}</p>
        <p class="text-xs text-green-200">{{ __('Profit by day') }}</p>
    </a>
    <a href="{{ route('reports.earnings-monthly') }}"
       class="bg-emerald-700 text-white rounded-2xl p-4 flex flex-col gap-1 active:opacity-90">
        <span class="text-2xl">📈</span>
        <p class="font-bold text-sm">{{ __('Monthly Earnings') }}</p>
        <p class="text-xs text-emerald-200">{{ __('Profit by month') }}</p>
    </a>
</div>

{{-- Sales section --}}
<h2 class="font-semibold text-gray-700 mb-2 text-sm uppercase tracking-wide">{{ __('Sales') }}</h2>
<div class="space-y-2">
    @php
        $links = [
            ['href' => route('reports.daily'),       'icon' => '📅', 'label' => __('Daily Sales'),      'sub' => __("Today's invoice breakdown")],
            ['href' => route('reports.monthly'),     'icon' => '📊', 'label' => __('Monthly Sales'),     'sub' => __("This month's performance")],
            ['href' => route('reports.pending'),     'icon' => '⏳', 'label' => __('Pending Payments'),  'sub' => $stats['pending_count'] . ' ' . __('unpaid invoices')],
            ['href' => route('reports.best-seller'), 'icon' => '🏆', 'label' => __('Best Seller'),       'sub' => __('Top product by quantity sold')],
            ['href' => route('customers.best'),      'icon' => '⭐', 'label' => __('Best Customer'),     'sub' => __('Highest total purchases')],
        ];
    @endphp

    @foreach ($links as $link)
        <a href="{{ $link['href'] }}"
           class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3 block active:bg-gray-50">
            <span class="text-2xl">{{ $link['icon'] }}</span>
            <div class="flex-1">
                <p class="font-semibold text-gray-900 text-sm">{{ $link['label'] }}</p>
                <p class="text-xs text-gray-400">{{ $link['sub'] }}</p>
            </div>
            <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    @endforeach
</div>
@endsection
