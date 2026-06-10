@extends('layouts.app')
@section('title', 'Daily Sales')

@section('content')

{{-- Date picker --}}
<form method="GET" class="mb-4">
    <input type="date" name="date" value="{{ $data['date']->format('Y-m-d') }}" onchange="this.form.submit()"
           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm bg-white focus:ring-2 focus:ring-indigo-500 outline-none">
</form>

{{-- Summary --}}
<div class="grid grid-cols-3 gap-3 mb-4">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-3 text-center">
        <p class="text-xl font-bold text-indigo-600">${{ number_format($data['total'], 2) }}</p>
        <p class="text-xs text-gray-400 mt-0.5">{{ __('Total') }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-3 text-center">
        <p class="text-xl font-bold text-green-600">${{ number_format($data['paid'], 2) }}</p>
        <p class="text-xs text-gray-400 mt-0.5">{{ __('Paid') }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-3 text-center">
        <p class="text-xl font-bold text-orange-500">${{ number_format($data['pending'], 2) }}</p>
        <p class="text-xs text-gray-400 mt-0.5">{{ __('Pending') }}</p>
    </div>
</div>

{{-- Invoices --}}
<p class="text-xs text-gray-500 mb-2">{{ $data['count'] }} invoice{{ $data['count'] !== 1 ? 's' : '' }} on {{ $data['date']->format('d M Y') }}</p>
<div class="space-y-3">
    @forelse ($data['invoices'] as $invoice)
        <a href="{{ route('invoices.show', $invoice) }}"
           class="bg-white rounded-2xl border border-gray-100 shadow-sm p-3 flex items-center gap-3 block active:bg-gray-50">
            <div class="flex-1 min-w-0">
                <p class="font-semibold text-gray-900">#{{ $invoice->id }} — {{ $invoice->customer->name }}</p>
                <p class="text-xs text-gray-500">{{ $invoice->created_at->format('H:i') }}</p>
            </div>
            <div class="text-right">
                <p class="font-bold text-gray-900">${{ number_format($invoice->total_amount, 2) }}</p>
                <span class="text-xs px-2 py-0.5 rounded-full {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700' }}">
                    {{ ucfirst($invoice->status) }}
                </span>
            </div>
        </a>
    @empty
        <p class="text-center text-gray-400 py-10 text-sm">{{ __('No invoices on this date.') }}</p>
    @endforelse
</div>
@endsection
