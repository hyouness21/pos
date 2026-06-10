@extends('layouts.app')
@section('title', $customer->name)

@section('header-actions')
    <a href="{{ route('customers.edit', $customer) }}"
       class="bg-white text-indigo-600 text-sm font-semibold px-3 py-1 rounded-lg">{{ __('Edit') }}</a>
@endsection

@section('content')

{{-- Summary cards --}}
<div class="grid grid-cols-2 gap-3 mb-4">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center">
        <p class="text-2xl font-bold text-gray-900">${{ number_format($customer->totalPurchases(), 2) }}</p>
        <p class="text-xs text-gray-500 mt-0.5">{{ __('Total Purchases') }}</p>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center">
        <p class="text-2xl font-bold {{ $customer->totalOwed() > 0 ? 'text-red-600' : 'text-green-600' }}">
            ${{ number_format($customer->totalOwed(), 2) }}
        </p>
        <p class="text-xs text-gray-500 mt-0.5">{{ __('Outstanding Debt') }}</p>
    </div>
</div>

{{-- Customer info --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-4 space-y-2 text-sm">
    @if ($customer->phone)
        <div class="flex justify-between"><span class="text-gray-500">{{ __('Phone') }}</span><span class="font-medium">{{ $customer->phone }}</span></div>
    @endif
    @if ($customer->email)
        <div class="flex justify-between"><span class="text-gray-500">Email</span><span class="font-medium">{{ $customer->email }}</span></div>
    @endif
    @if ($customer->address)
        <div class="flex justify-between"><span class="text-gray-500">{{ __('Address') }}</span><span class="font-medium text-right max-w-xs">{{ $customer->address }}</span></div>
    @endif
</div>

{{-- Invoices --}}
<h2 class="font-semibold text-gray-700 mb-2 text-sm uppercase tracking-wide">{{ __('Invoices') }}</h2>
<div class="space-y-3">
    @forelse ($invoices as $invoice)
        <a href="{{ route('invoices.show', $invoice) }}"
           class="bg-white rounded-2xl border border-gray-100 shadow-sm p-3 flex items-center gap-3 block active:bg-gray-50">
            <div class="flex-1 min-w-0">
                <p class="font-semibold text-gray-900">#{{ $invoice->id }}</p>
                <p class="text-xs text-gray-500">{{ $invoice->created_at->format('d M Y') }}</p>
            </div>
            <div class="text-right shrink-0">
                <p class="font-bold text-gray-900">${{ number_format($invoice->total_amount, 2) }}</p>
                <span class="text-xs px-2 py-0.5 rounded-full
                    {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700' }}">
                    {{ ucfirst($invoice->status) }}
                </span>
            </div>
        </a>
    @empty
        <p class="text-center text-gray-400 py-8 text-sm">{{ __('No invoices yet.') }}</p>
    @endforelse
</div>
{{ $invoices->links() }}
@endsection
