@extends('layouts.app')
@section('title', 'Best Seller')

@section('content')
@if ($item)
    <div class="text-center py-8">
        @if ($item->image)
            <img src="{{ Storage::url($item->image) }}"
                 class="w-32 h-32 rounded-2xl object-cover mx-auto mb-4 shadow-md">
        @else
            <div class="w-32 h-32 rounded-2xl bg-indigo-50 flex items-center justify-center mx-auto mb-4">
                <span class="text-6xl">🏆</span>
            </div>
        @endif

        <p class="text-2xl font-bold text-gray-900">{{ $item->name }}</p>
        <p class="text-gray-500 text-sm mt-1">{{ $item->category->name }}</p>

        <div class="mt-6 bg-white rounded-2xl border border-gray-100 shadow-sm p-5 text-left space-y-3">
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">{{ __('Total Sold') }}</span>
                <span class="font-bold text-indigo-600 text-base">{{ number_format($item->invoice_items_sum_quantity) }} {{ __('units') }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Selling Price</span>
                <span class="font-medium">${{ number_format($item->price, 2) }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">{{ __('Current Stock') }}</span>
                <span class="font-medium {{ $item->isLowStock() ? 'text-red-600' : 'text-green-600' }}">
                    {{ $item->stock }} {{ __('units') }}
                </span>
            </div>
        </div>

        <a href="{{ route('items.edit', $item) }}"
           class="mt-4 block w-full bg-indigo-600 text-white font-semibold py-3 rounded-2xl shadow">
            {{ __('Edit') }} Item
        </a>
    </div>
@else
    <div class="text-center py-20 text-gray-400">
        <p class="text-lg font-medium">{{ __('No data yet') }}</p>
        <p class="text-sm mt-1">{{ __('Create some invoices first.') }}</p>
    </div>
@endif
@endsection
