@extends('layouts.app')
@section('title', 'New Item')

@section('content')
<form method="POST" action="{{ route('items.store') }}" enctype="multipart/form-data" class="space-y-4">
    @csrf

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 space-y-4">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Category *') }}</label>
            <select name="category_id" required
                    class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                <option value="">{{ __('Select category') }}</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Item Name *') }}</label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
        </div>

        @include('items._barcode_field', ['value' => old('barcode')])

        {{-- Pricing --}}
        <div x-data="{ cost: '', sell: '' }"
             class="bg-gray-50 rounded-xl p-3 space-y-3">

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">{{ __('Cost Price') }}</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">$</span>
                        <input type="number" name="cost_price" x-model="cost"
                               value="{{ old('cost_price') }}" step="0.01" min="0" placeholder="0.00"
                               class="w-full border border-gray-300 rounded-xl pl-7 pr-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <p class="text-xs text-gray-400 mt-0.5">{{ __('what items cost you') }}</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">{{ __('Sell Price') }} *</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">$</span>
                        <input type="number" name="price" x-model="sell"
                               value="{{ old('price') }}" step="0.01" min="0" required placeholder="0.00"
                               class="w-full border border-gray-300 rounded-xl pl-7 pr-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <p class="text-xs text-gray-400 mt-0.5">{{ __('what you charged') }}</p>
                </div>
            </div>

            {{-- Live profit preview --}}
            <div x-show="cost > 0 && sell > 0" x-cloak
                 class="flex items-center justify-between text-sm pt-1 border-t border-gray-200">
                <span class="text-gray-500">{{ __('Profit per unit') }}</span>
                <span :class="(sell - cost) >= 0 ? 'text-green-600 font-bold' : 'text-red-500 font-bold'"
                      x-text="'$' + (sell - cost).toFixed(2) + ' (' + (cost > 0 ? Math.round((sell - cost) / cost * 100) : 0) + '% margin)'">
                </span>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Stock *') }}</label>
                <input type="number" name="stock" value="{{ old('stock', 0) }}" min="0" required
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Low Stock Alert') }}</label>
                <input type="number" name="low_stock_threshold" value="{{ old('low_stock_threshold', 5) }}" min="0" required
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Expiry Date') }}</label>
            <input type="date" name="expiry_date" value="{{ old('expiry_date') }}"
                   class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Image') }}</label>
            <input type="file" name="image" accept="image/*"
                   class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700">
        </div>

    </div>

    <button type="submit"
            class="w-full bg-indigo-600 text-white font-semibold py-3 rounded-2xl shadow active:scale-95 transition-transform">
        {{ __('Create Item') }}
    </button>
</form>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script>
function barcodeField(initial) {
    return {
        barcode: initial || '',
        mode: initial ? 'custom' : 'custom',
        render() {
            if (!this.barcode || typeof JsBarcode === 'undefined') return;
            try {
                JsBarcode(this.$refs.svg, this.barcode, {
                    format: 'CODE128', width: 2, height: 60,
                    displayValue: false, margin: 10,
                });
            } catch(e) {}
        },
        generate() {
            this.barcode = String(Math.floor(Math.random() * 9e11) + 1e11);
            this.$nextTick(() => this.render());
        },
        init() { this.$nextTick(() => this.render()); }
    };
}
</script>
@endpush
