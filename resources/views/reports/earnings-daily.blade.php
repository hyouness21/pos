@extends('layouts.app')
@section('title', 'Daily Earnings')

@section('content')

{{-- Date picker --}}
<form method="GET" class="mb-4">
    <input type="date" name="date" value="{{ $data['date']->format('Y-m-d') }}" onchange="this.form.submit()"
           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm bg-white focus:ring-2 focus:ring-indigo-500 outline-none">
</form>

{{-- Summary cards --}}
<div class="grid grid-cols-2 gap-3 mb-5">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <p class="text-xs text-gray-400 mb-0.5">{{ __('Revenue') }}</p>
        <p class="text-xl font-bold text-indigo-600">${{ number_format($data['revenue'], 2) }}</p>
        <p class="text-xs text-gray-400 mt-0.5">{{ __('what you charged') }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <p class="text-xs text-gray-400 mb-0.5">{{ __('Cost') }}</p>
        <p class="text-xl font-bold text-gray-600">${{ number_format($data['cost'], 2) }}</p>
        <p class="text-xs text-gray-400 mt-0.5">{{ __('what items cost you') }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 col-span-2
                {{ $data['profit'] >= 0 ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' }}">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold {{ $data['profit'] >= 0 ? 'text-green-700' : 'text-red-700' }} uppercase tracking-wide">
                    {{ __('Net Earnings') }}
                </p>
                <p class="text-3xl font-bold {{ $data['profit'] >= 0 ? 'text-green-600' : 'text-red-600' }} mt-0.5">
                    ${{ number_format($data['profit'], 2) }}
                </p>
            </div>
            <div class="text-right">
                <p class="text-xs text-gray-400">{{ __('Margin') }}</p>
                <p class="text-2xl font-bold {{ $data['profit'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ $data['margin'] }}%
                </p>
            </div>
        </div>
    </div>

    {{-- Expenses --}}
    <div class="bg-red-50 border border-red-100 rounded-2xl p-4">
        <p class="text-xs text-red-400 mb-0.5">{{ __('Expenses') }}</p>
        <p class="text-xl font-bold text-red-500">${{ number_format($data['expenses_total'], 2) }}</p>
        <a href="{{ route('expenses.index', ['date' => $data['date']->format('Y-m-d')]) }}"
           class="text-xs text-red-400 mt-0.5 block hover:underline">{{ $data['expenses']->count() }} {{ __('records') }} →</a>
    </div>
    <div class="rounded-2xl p-4 border
                {{ $data['net_profit'] >= 0 ? 'bg-emerald-50 border-emerald-200' : 'bg-red-100 border-red-300' }}">
        <p class="text-xs font-semibold {{ $data['net_profit'] >= 0 ? 'text-emerald-700' : 'text-red-700' }} mb-0.5">
            {{ __('After Expenses') }}
        </p>
        <p class="text-xl font-bold {{ $data['net_profit'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
            ${{ number_format($data['net_profit'], 2) }}
        </p>
        <p class="text-xs {{ $data['net_profit'] >= 0 ? 'text-emerald-400' : 'text-red-400' }} mt-0.5">
            {{ __('Net Earnings') }} − {{ __('Expenses') }}
        </p>
    </div>
</div>

{{-- Expenses breakdown --}}
@if ($data['expenses']->isNotEmpty())
@php
    $typeColors = ['fuel' => 'bg-blue-100 text-blue-700', 'salary' => 'bg-purple-100 text-purple-700', 'item_cost' => 'bg-orange-100 text-orange-700', 'other' => 'bg-gray-100 text-gray-600'];
@endphp
<h2 class="font-semibold text-gray-700 mb-2 text-sm uppercase tracking-wide">{{ __('Expenses') }}</h2>
<div class="space-y-2 mb-5">
    @foreach ($data['expenses'] as $expense)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-3 flex items-center gap-3">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-0.5">
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $typeColors[$expense->type] ?? 'bg-gray-100 text-gray-600' }}">
                        {{ __($expense->typeLabel()) }}
                    </span>
                </div>
                @if ($expense->notes)
                    <p class="text-xs text-gray-400 truncate">{{ $expense->notes }}</p>
                @endif
            </div>
            <p class="font-bold text-red-500 shrink-0">${{ number_format($expense->amount, 2) }}</p>
        </div>
    @endforeach
</div>
@endif

@if ($data['rows']->isEmpty())
    <div class="text-center py-16 text-gray-400">
        <p class="text-4xl mb-3">📊</p>
        <p class="font-medium">{{ __('No earnings data') }}</p>
        <p class="text-sm mt-1">{{ __('Either no sales on this date, or items have no cost price set.') }}</p>
    </div>
@else
    {{-- Per-item breakdown --}}
    <h2 class="font-semibold text-gray-700 mb-2 text-sm uppercase tracking-wide">{{ __('Breakdown by Product') }}</h2>
    <div class="space-y-2">
        @foreach ($data['rows'] as $row)
            @php $margin = $row->revenue > 0 ? round($row->profit / $row->revenue * 100, 1) : 0; @endphp
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-3">
                <div class="flex items-start justify-between gap-2 mb-2">
                    <p class="font-semibold text-gray-900 text-sm">{{ $row->item_name }}</p>
                    <span class="shrink-0 text-xs px-2 py-0.5 rounded-full
                        {{ $row->profit >= 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }} font-semibold">
                        {{ $row->profit >= 0 ? '+' : '' }}${{ number_format($row->profit, 2) }}
                    </span>
                </div>
                <div class="grid grid-cols-3 gap-2 text-xs text-gray-500">
                    <div><span class="block text-gray-400">{{ __('Sold') }}</span><span class="font-medium text-gray-700">{{ $row->qty_sold }} {{ __('units') }}</span></div>
                    <div><span class="block text-gray-400">{{ __('Revenue') }}</span><span class="font-medium text-gray-700">${{ number_format($row->revenue, 2) }}</span></div>
                    <div><span class="block text-gray-400">{{ __('Margin') }}</span><span class="font-medium {{ $margin >= 0 ? 'text-green-600' : 'text-red-500' }}">{{ $margin }}%</span></div>
                </div>
                {{-- Profit bar --}}
                <div class="mt-2 bg-gray-100 rounded-full h-1.5 overflow-hidden">
                    <div class="{{ $row->profit >= 0 ? 'bg-green-500' : 'bg-red-400' }} h-1.5 rounded-full"
                         style="width: {{ min(100, abs($margin)) }}%"></div>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
