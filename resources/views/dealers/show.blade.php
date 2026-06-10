@extends('layouts.app')
@section('title', $dealer->name)

@section('header-actions')
    <a href="{{ route('dealer-purchases.create', $dealer) }}"
       class="bg-white text-indigo-600 text-sm font-semibold px-3 py-1 rounded-lg">+ {{ __('Purchase') }}</a>
    <a href="{{ route('dealers.edit', $dealer) }}"
       class="bg-white text-indigo-600 text-sm font-semibold px-3 py-1 rounded-lg">{{ __('Edit') }}</a>
@endsection

@section('content')

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-4 space-y-2 text-sm">
    <div class="flex justify-between">
        <span class="text-gray-500">{{ __('Total Spent') }}</span>
        <span class="font-bold text-indigo-600 text-base">${{ number_format($dealer->totalSpent(), 2) }}</span>
    </div>
    @if ($dealer->phone)
    <div class="flex justify-between"><span class="text-gray-500">{{ __('Phone') }}</span><span class="font-medium">{{ $dealer->phone }}</span></div>
    @endif
    @if ($dealer->email)
    <div class="flex justify-between"><span class="text-gray-500">Email</span><span class="font-medium">{{ $dealer->email }}</span></div>
    @endif
    @if ($dealer->notes)
    <div class="flex justify-between"><span class="text-gray-500">{{ __('Notes') }}</span><span class="font-medium">{{ $dealer->notes }}</span></div>
    @endif
</div>

<h2 class="font-semibold text-gray-700 mb-2 text-sm uppercase tracking-wide">{{ __('Purchase History') }}</h2>
<div class="space-y-3">
    @forelse ($purchases as $purchase)
        <a href="{{ route('dealer-purchases.show', $purchase) }}"
           class="bg-white rounded-2xl border border-gray-100 shadow-sm p-3 flex items-center gap-3 block active:bg-gray-50">
            <div class="flex-1 min-w-0">
                <p class="font-semibold text-gray-900">Purchase #{{ $purchase->id }}</p>
                <p class="text-xs text-gray-500">{{ $purchase->purchase_date->format('d M Y') }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $purchase->items->count() }} item type{{ $purchase->items->count() !== 1 ? 's' : '' }}</p>
            </div>
            <p class="font-bold text-gray-900 shrink-0">${{ number_format($purchase->total_amount, 2) }}</p>
        </a>
    @empty
        <p class="text-center text-gray-400 py-8 text-sm">{{ __('No purchases recorded yet.') }}</p>
    @endforelse
</div>
{{ $purchases->links() }}
@endsection
