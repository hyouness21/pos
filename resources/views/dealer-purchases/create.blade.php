@extends('layouts.app')
@section('title', 'New Purchase — ' . $dealer->name)

@section('content')

<div x-data="purchaseBuilder({{ $items->toJson() }})" class="space-y-4">

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 space-y-4">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Purchase Date *') }}</label>
            <input type="date" x-model="purchaseDate" required
                   :max="today"
                   class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Notes') }}</label>
            <textarea x-model="notes" rows="2"
                      class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none resize-none"></textarea>
        </div>

    </div>

    {{-- Item picker --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
        <h2 class="font-semibold text-gray-700 mb-3 text-sm uppercase tracking-wide">{{ __('Add Items') }}</h2>

        <div class="flex gap-2 mb-3">
            <input type="text" x-model="search" placeholder="{{ __('Search items…') }}"
                   class="flex-1 border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
        </div>

        <div class="space-y-2 max-h-48 overflow-y-auto">
            <template x-for="item in filteredItems()" :key="item.id">
                <div class="flex items-center gap-3 border border-gray-100 rounded-xl px-3 py-2">
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-900 text-sm truncate" x-text="item.name"></p>
                        <p class="text-xs text-gray-400" x-text="item.category ? item.category.name : ''"></p>
                    </div>
                    <button type="button" @click="addItem(item)"
                            class="shrink-0 text-sm bg-indigo-50 text-indigo-600 font-medium px-3 py-1.5 rounded-lg active:bg-indigo-100">
                        + Add
                    </button>
                </div>
            </template>
        </div>
    </div>

    {{-- Line items --}}
    <div x-show="lines.length > 0" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
        <h2 class="font-semibold text-gray-700 mb-3 text-sm uppercase tracking-wide">{{ __('Order Lines') }}</h2>
        <div class="space-y-4">
            <template x-for="(line, index) in lines" :key="index">
                <div class="border-b border-gray-100 pb-4 last:border-0 last:pb-0">
                    <div class="flex justify-between items-start mb-2">
                        <p class="font-medium text-gray-900 text-sm" x-text="line.name"></p>
                        <button type="button" @click="removeLine(index)" class="text-red-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">{{ __('Qty') }}</label>
                            <input type="number" x-model.number="line.quantity" min="1"
                                   class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">{{ __('Unit Cost ($)') }}</label>
                            <input type="number" x-model.number="line.unit_cost" min="0" step="0.01"
                                   class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                    </div>
                    <p class="text-right text-sm font-bold text-gray-700 mt-1"
                       x-text="'{{ __('Subtotal:') }} $' + (line.quantity * line.unit_cost).toFixed(2)"></p>
                </div>
            </template>
        </div>

        <div class="mt-4 pt-3 border-t border-gray-200 flex justify-between items-center">
            <span class="font-bold text-gray-700">{{ __('Total') }}</span>
            <span class="text-2xl font-bold text-indigo-600" x-text="'$' + grandTotal().toFixed(2)"></span>
        </div>
    </div>

    {{-- Hidden form --}}
    <form id="purchase-form" method="POST"
          action="{{ route('dealer-purchases.store', $dealer) }}" x-ref="form">
        @csrf
        <input type="hidden" name="purchase_date" :value="purchaseDate">
        <input type="hidden" name="notes" :value="notes">
        <template x-for="(line, index) in lines" :key="index">
            <span>
                <input type="hidden" :name="'lines[' + index + '][item_id]'" :value="line.item_id">
                <input type="hidden" :name="'lines[' + index + '][quantity]'" :value="line.quantity">
                <input type="hidden" :name="'lines[' + index + '][unit_cost]'" :value="line.unit_cost">
            </span>
        </template>
    </form>

    <button type="button" @click="submit()"
            :disabled="!canSubmit()"
            :class="canSubmit() ? 'bg-indigo-600 active:scale-95' : 'bg-gray-300 cursor-not-allowed'"
            class="w-full text-white font-semibold py-3 rounded-2xl shadow transition-all">
        <span x-text="canSubmit() ? '{{ __('Record Purchase') }} — $' + grandTotal().toFixed(2) : '{{ __('Fill all fields') }}'"></span>
    </button>

</div>

<script>
function purchaseBuilder(items) {
    return {
        items,
        search: '',
        purchaseDate: new Date().toISOString().split('T')[0],
        today: new Date().toISOString().split('T')[0],
        notes: '',
        lines: [],

        filteredItems() {
            if (!this.search) return this.items;
            return this.items.filter(i => i.name.toLowerCase().includes(this.search.toLowerCase()));
        },

        addItem(item) {
            const existing = this.lines.find(l => l.item_id === item.id);
            if (existing) { existing.quantity++; return; }
            this.lines.push({ item_id: item.id, name: item.name, quantity: 1, unit_cost: 0 });
        },

        removeLine(index) { this.lines.splice(index, 1); },
        grandTotal() { return this.lines.reduce((s, l) => s + l.quantity * l.unit_cost, 0); },
        canSubmit() { return this.purchaseDate && this.lines.length > 0 && this.lines.every(l => l.quantity > 0 && l.unit_cost >= 0); },

        submit() { if (this.canSubmit()) this.$refs.form.submit(); },
    };
}
</script>
@endsection
