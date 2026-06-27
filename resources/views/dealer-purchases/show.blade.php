@extends('layouts.app')
@section('title', 'Purchase #' . $dealerPurchase->id)

@section('content')

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-4 space-y-2 text-sm">
    <div class="flex justify-between">
        <span class="text-gray-500">Dealer</span>
        <a href="{{ route('dealers.show', $dealerPurchase->dealer) }}" class="font-medium text-indigo-600">
            {{ $dealerPurchase->dealer->name }}
        </a>
    </div>
    <div class="flex justify-between">
        <span class="text-gray-500">{{ __('Date') }}</span>
        <span class="font-medium">{{ $dealerPurchase->purchase_date->format('d M Y') }}</span>
    </div>
    @if ($dealerPurchase->notes)
    <div class="flex justify-between">
        <span class="text-gray-500">{{ __('Notes') }}</span>
        <span class="font-medium">{{ $dealerPurchase->notes }}</span>
    </div>
    @endif
</div>

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-4">
    <h2 class="font-semibold text-gray-700 mb-3 text-sm uppercase tracking-wide">{{ __('Items Purchased') }}</h2>
    <div class="space-y-3">
        @foreach ($dealerPurchase->items as $line)
            <div class="flex items-center gap-3">
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-gray-900 text-sm">{{ $line->item->name }}</p>
                    <p class="text-xs text-gray-500">${{ number_format($line->unit_cost, 2) }} × {{ $line->quantity }}</p>
                </div>
                <p class="font-bold text-gray-900 shrink-0">${{ number_format($line->subtotal, 2) }}</p>
            </div>
        @endforeach
    </div>
    <div class="mt-4 pt-3 border-t border-gray-200 flex justify-between">
        <span class="font-bold text-gray-700 text-lg">{{ __('Total') }}</span>
        <span class="font-bold text-indigo-600 text-2xl">${{ number_format($dealerPurchase->total_amount, 2) }}</span>
    </div>
</div>

<a href="{{ route('dealer-purchases.edit', $dealerPurchase) }}"
   class="block w-full text-center bg-white border border-indigo-200 text-indigo-600 font-semibold text-sm py-3 rounded-2xl mb-2">
    {{ __('Edit Purchase') }}
</a>

<form method="POST" action="{{ route('dealer-purchases.destroy', $dealerPurchase) }}"
      onsubmit="return confirm('{{ __('Delete this purchase? Stock will NOT be reversed.') }}')">
    @csrf @method('DELETE')
    <button type="submit" class="w-full text-red-500 text-sm py-3 font-medium">{{ __('Delete Purchase') }}</button>
</form>
@endsection
