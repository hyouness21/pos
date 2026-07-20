@extends('layouts.app')
@section('title', __('Refund') . ' — #' . $invoice->id)

@section('content')

<div x-data="{
    lines: @js($lines),
    get total() {
        return this.lines.reduce((s, l) => s + (parseFloat(l.unit_price) * parseInt(l.qty || 0)), 0);
    }
}">

    {{-- Invoice ref --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-4 text-sm">
        <div class="flex justify-between">
            <span class="text-gray-500">{{ __('Invoice') }}</span>
            <a href="{{ route('invoices.show', $invoice) }}" class="font-semibold text-indigo-600">#{{ $invoice->id }}</a>
        </div>
        <div class="flex justify-between mt-1">
            <span class="text-gray-500">{{ __('Customer') }}</span>
            <span class="font-medium">{{ $invoice->customer->name }}</span>
        </div>
    </div>

    <form method="POST" action="{{ route('invoices.refunds.store', $invoice) }}">
        @csrf
        <input type="hidden" name="type" value="cash">

        {{-- Items --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-4">
            <p class="text-sm font-semibold text-gray-700 mb-3">{{ __('Items to Refund') }}</p>

            @error('items')
                <p class="text-xs text-red-500 mb-2">{{ $message }}</p>
            @enderror

            <div class="space-y-4">
                <template x-for="(line, i) in lines" :key="i">
                    <div class="border border-gray-100 rounded-xl p-3">
                        <input type="hidden" :name="'items[' + i + '][item_id]'" :value="line.item_id">
                        <input type="hidden" :name="'items[' + i + '][unit_price]'" :value="line.unit_price">

                        <div class="flex items-start justify-between mb-2">
                            <p class="font-medium text-gray-900 text-sm" x-text="line.name"></p>
                            <p class="text-xs text-gray-400 shrink-0 ml-2">
                                <span x-text="'$' + parseFloat(line.unit_price).toFixed(2)"></span>
                                / {{ __('unit') }}
                            </p>
                        </div>

                        <div class="flex items-center justify-between">
                            <p class="text-xs text-gray-400">
                                {{ __('Max') }}: <span x-text="line.refundable_qty" class="font-medium text-gray-600"></span>
                            </p>
                            <div class="flex items-center gap-2">
                                <button type="button"
                                        @click="line.qty = Math.max(0, (parseInt(line.qty) || 0) - 1)"
                                        class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 font-bold active:bg-gray-200">−</button>
                                <input type="number"
                                       :name="'items[' + i + '][quantity]'"
                                       x-model="line.qty"
                                       min="0"
                                       :max="line.refundable_qty"
                                       class="w-14 text-center border border-gray-200 rounded-lg py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <button type="button"
                                        @click="line.qty = Math.min(line.refundable_qty, (parseInt(line.qty) || 0) + 1)"
                                        class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold active:bg-indigo-200">+</button>
                            </div>
                            <p class="text-sm font-bold text-gray-900 min-w-[4rem] text-right"
                               x-text="'$' + (parseFloat(line.unit_price) * (parseInt(line.qty) || 0)).toFixed(2)"></p>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Total --}}
        <div class="bg-white rounded-2xl border border-indigo-100 shadow-sm p-4 mb-4 flex justify-between items-center">
            <span class="font-semibold text-gray-700">{{ __('Refund Total') }}</span>
            <span class="font-bold text-indigo-600 text-2xl" x-text="'$' + total.toFixed(2)"></span>
        </div>

        {{-- Notes --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-6">
            <label class="text-sm font-medium text-gray-700 block mb-1.5">{{ __('Notes') }} <span class="text-gray-400 font-normal">({{ __('optional') }})</span></label>
            <textarea name="notes" rows="2" placeholder="{{ __('Reason for refund…') }}"
                      class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none">{{ old('notes') }}</textarea>
        </div>

        {{-- Submit --}}
        <button type="submit"
                class="w-full bg-red-500 active:bg-red-600 text-white font-bold py-4 rounded-2xl shadow transition-all text-base"
                :disabled="total <= 0"
                :class="total <= 0 ? 'opacity-40 cursor-not-allowed' : ''">
            {{ __('Process Refund') }}
        </button>
    </form>
</div>

@endsection
