@extends('layouts.app')
@section('title', __('Expenses'))

@section('header-actions')
    <button type="button" onclick="document.getElementById('add-expense').classList.toggle('hidden')"
            class="bg-white text-indigo-600 text-sm font-semibold px-3 py-1 rounded-lg">+ {{ __('Add') }}</button>
@endsection

@section('content')

{{-- Summary cards --}}
<div class="grid grid-cols-2 gap-3 mb-4">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <p class="text-xs text-gray-400 mb-0.5">{{ __("Today's Expenses") }}</p>
        <p class="text-xl font-bold text-red-500" style="direction:ltr;unicode-bidi:bidi-override">${{ number_format($todayTotal, 2) }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <p class="text-xs text-gray-400 mb-0.5">{{ __("This Month's Expenses") }}</p>
        <p class="text-xl font-bold text-orange-500" style="direction:ltr;unicode-bidi:bidi-override">${{ number_format($monthTotal, 2) }}</p>
    </div>
</div>

{{-- Add expense form --}}
<div id="add-expense" class="hidden mb-4">
    <form method="POST" action="{{ route('expenses.store') }}"
          class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 space-y-3">
        @csrf

        {{-- Type --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Expense Type') }} *</label>
            <div class="grid grid-cols-2 gap-2">
                @foreach ($types as $value => $label)
                    <label class="flex items-center gap-2 border border-gray-200 rounded-xl px-3 py-2.5 cursor-pointer text-sm
                                  {{ old('type') === $value ? 'border-indigo-500 bg-indigo-50' : '' }}">
                        <input type="radio" name="type" value="{{ $value }}"
                               {{ old('type') === $value ? 'checked' : '' }}
                               class="text-indigo-600 shrink-0">
                        <span class="font-medium">{{ __($label) }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Amount --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Amount') }} *</label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium">$</span>
                <input type="number" name="amount" value="{{ old('amount') }}"
                       min="0.01" step="0.01" placeholder="0.00"
                       class="w-full border border-gray-300 rounded-xl pl-7 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>
        </div>

        {{-- Date --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Date') }} *</label>
            <input type="date" name="date" value="{{ old('date', now()->toDateString()) }}"
                   class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
        </div>

        {{-- Notes --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Notes') }}</label>
            <input type="text" name="notes" value="{{ old('notes') }}"
                   placeholder="{{ __('Optional description…') }}"
                   class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
        </div>

        <div class="flex gap-2 pt-1">
            <button type="submit"
                    class="flex-1 bg-indigo-600 text-white font-semibold py-2.5 rounded-xl active:scale-95 transition-transform text-sm">
                {{ __('Record Expense') }}
            </button>
            <button type="button" onclick="document.getElementById('add-expense').classList.add('hidden')"
                    class="px-4 py-2.5 rounded-xl text-gray-500 bg-gray-100 text-sm font-medium">
                {{ __('Cancel') }}
            </button>
        </div>
    </form>
</div>

{{-- Filters --}}
<form method="GET" class="flex gap-2 mb-4 items-center">
    <input type="date" name="date" value="{{ request('date') }}" onchange="this.form.submit()"
           class="flex-1 border border-gray-300 rounded-xl px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-indigo-500 outline-none">
    <input type="month" name="month" value="{{ request('month') }}" onchange="this.form.submit()"
           class="flex-1 border border-gray-300 rounded-xl px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-indigo-500 outline-none">
    @if(request()->hasAny(['date', 'month']))
        <a href="{{ route('expenses.index') }}"
           class="text-sm text-gray-400 hover:text-red-500 whitespace-nowrap">{{ __('Clear') }}</a>
    @endif
</form>

{{-- Expense list --}}
@php
    $typeColors = [
        'fuel'      => 'bg-blue-100 text-blue-700',
        'salary'    => 'bg-purple-100 text-purple-700',
        'item_cost' => 'bg-orange-100 text-orange-700',
        'other'     => 'bg-gray-100 text-gray-600',
    ];
@endphp

@if ($expenses->isNotEmpty())
    {{-- Filtered total --}}
    <div class="bg-red-50 border border-red-100 rounded-2xl p-3 mb-3 flex items-center justify-between">
        <span class="text-sm font-medium text-red-600">{{ __('Total') }}</span>
        <span class="text-xl font-bold text-red-600" style="direction:ltr;unicode-bidi:bidi-override">
            ${{ number_format($expenses->sum('amount'), 2) }}
        </span>
    </div>
@endif

<div class="space-y-2">
    @forelse ($expenses as $expense)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-3 flex items-center gap-3">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-0.5">
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $typeColors[$expense->type] ?? 'bg-gray-100 text-gray-600' }}">
                        {{ __($expense->typeLabel()) }}
                    </span>
                    <span class="text-xs text-gray-400">{{ $expense->date->format('d M Y') }}</span>
                </div>
                @if ($expense->notes)
                    <p class="text-sm text-gray-500 truncate">{{ $expense->notes }}</p>
                @endif
            </div>
            <p class="font-bold text-gray-900 shrink-0" style="direction:ltr;unicode-bidi:bidi-override">
                ${{ number_format($expense->amount, 2) }}
            </p>
            <form method="POST" action="{{ route('expenses.destroy', $expense) }}"
                  onsubmit="return confirm('{{ __('Delete this expense?') }}')">
                @csrf @method('DELETE')
                <button type="submit" class="text-red-400 hover:text-red-600 p-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </form>
        </div>
    @empty
        <div class="text-center py-16 text-gray-400">
            <p class="font-medium">{{ __('No expenses recorded.') }}</p>
        </div>
    @endforelse
</div>

@if ($errors->any())
    <script>document.getElementById('add-expense').classList.remove('hidden');</script>
@endif

@endsection
