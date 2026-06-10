@extends('layouts.app')
@section('title', 'Pending Payments')

@section('content')

@if ($invoices->count() > 0)
    <div class="bg-orange-50 border border-orange-200 rounded-2xl px-4 py-3 mb-4 text-sm text-orange-800">
        <span class="font-bold">${{ number_format($invoices->sum('total_amount'), 2) }}</span>
        {{ __('total outstanding') }} across {{ $invoices->count() }} invoice{{ $invoices->count() !== 1 ? 's' : '' }}
    </div>
@endif

<div class="space-y-3">
    @forelse ($invoices as $invoice)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-3">
            <div class="flex items-start justify-between gap-3 mb-2">
                <div>
                    <a href="{{ route('invoices.show', $invoice) }}" class="font-semibold text-gray-900">
                        #{{ $invoice->id }}
                    </a>
                    <span class="text-gray-500 text-sm"> — </span>
                    <a href="{{ route('customers.show', $invoice->customer) }}" class="text-indigo-600 text-sm font-medium">
                        {{ $invoice->customer->name }}
                    </a>
                </div>
                <p class="font-bold text-gray-900 shrink-0">${{ number_format($invoice->total_amount, 2) }}</p>
            </div>
            <div class="flex items-center justify-between">
                <p class="text-xs text-gray-400">{{ $invoice->created_at->format('d M Y') }}</p>
                <form method="POST" action="{{ route('invoices.status', $invoice) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="paid">
                    <button type="submit" class="text-xs bg-green-600 text-white font-medium px-3 py-1.5 rounded-lg active:scale-95">
                        {{ __('Mark Paid') }}
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div class="text-center py-20 text-gray-400">
            <p class="text-5xl mb-3">🎉</p>
            <p class="font-medium text-lg">{{ __('All caught up!') }}</p>
            <p class="text-sm mt-1">{{ __('No pending payments.') }}</p>
        </div>
    @endforelse
</div>
@endsection
