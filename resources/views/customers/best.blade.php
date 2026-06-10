@extends('layouts.app')
@section('title', 'Best Customer')

@section('content')
@if ($customer)
    <div class="text-center py-8">
        <div class="w-24 h-24 rounded-full bg-yellow-100 flex items-center justify-center mx-auto mb-4">
            <span class="text-yellow-600 font-bold text-4xl">{{ strtoupper(substr($customer->name, 0, 1)) }}</span>
        </div>
        <p class="text-2xl font-bold text-gray-900">{{ $customer->name }}</p>
        <p class="text-gray-500 text-sm mt-1">{{ __('Top Buyer') }}</p>

        <div class="mt-6 bg-white rounded-2xl border border-gray-100 shadow-sm p-5 text-left space-y-3">
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">{{ __('Total Purchases') }}</span>
                <span class="font-bold text-indigo-600 text-base">${{ number_format($customer->invoices_sum_total_amount, 2) }}</span>
            </div>
            @if ($customer->phone)
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">{{ __('Phone') }}</span>
                <span class="font-medium">{{ $customer->phone }}</span>
            </div>
            @endif
            @if ($customer->email)
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Email</span>
                <span class="font-medium">{{ $customer->email }}</span>
            </div>
            @endif
        </div>

        <a href="{{ route('customers.show', $customer) }}"
           class="mt-4 block w-full bg-indigo-600 text-white font-semibold py-3 rounded-2xl shadow">
            {{ __('View Full Profile') }}
        </a>
    </div>
@else
    <div class="text-center py-20 text-gray-400">
        <p class="text-lg font-medium">{{ __('No data yet') }}</p>
        <p class="text-sm mt-1">{{ __('Create some invoices first.') }}</p>
    </div>
@endif
@endsection
