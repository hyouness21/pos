@extends('layouts.app')
@section('title', 'New Purchase — ' . $dealer->name)

@section('content')

<div x-data="purchaseBuilder({{ $items->toJson() }}, {{ $categories->toJson() }})" class="space-y-4">

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
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-semibold text-gray-700 text-sm uppercase tracking-wide">{{ __('Add Items') }}</h2>
            <button type="button" @click="newItemOpen = !newItemOpen"
                    class="text-xs font-medium text-indigo-600 active:opacity-70">
                <span x-text="newItemOpen ? '{{ __('← Search existing') }}' : '+ {{ __('New item') }}'"></span>
            </button>
        </div>

        {{-- Existing item search --}}
        <div x-show="!newItemOpen">
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
                            + {{ __('Add') }}
                        </button>
                    </div>
                </template>
                <div x-show="filteredItems().length === 0" class="text-sm text-gray-400 text-center py-4">
                    {{ __('No items found') }}
                </div>
            </div>
        </div>

        {{-- New item mini-form --}}
        <div x-show="newItemOpen" x-cloak class="space-y-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('Item Name *') }}</label>
                <input type="text" x-model="ni.name"
                       placeholder="{{ __('e.g. Coca-Cola 330ml') }}"
                       autocomplete="off"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-xs font-medium text-gray-600">{{ __('Category') }}</label>
                    <button type="button" @click="ni.newCat = !ni.newCat; ni.categoryId = ''; ni.newCategoryName = ''"
                            class="text-xs text-indigo-600 active:opacity-70">
                        <span x-text="ni.newCat ? '{{ __('← Select existing') }}' : '+ {{ __('New category') }}'"></span>
                    </button>
                </div>
                <select x-show="!ni.newCat" x-model="ni.categoryId"
                        class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                    <option value="">{{ __('No category') }}</option>
                    <template x-for="cat in categories" :key="cat.id">
                        <option :value="cat.id" x-text="cat.name"></option>
                    </template>
                </select>
                <input x-show="ni.newCat" x-cloak type="text" x-model="ni.newCategoryName"
                       placeholder="{{ __('New category name…') }}" autocomplete="off"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('Total Paid ($)') }}</label>
                    <input type="number" x-model.number="ni.totalPaid" min="0" step="0.01" placeholder="0.00"
                           class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('Sell Price ($)') }}</label>
                    <input type="number" x-model.number="ni.price" min="0" step="0.01" placeholder="0.00"
                           class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
            </div>
            <p class="text-xs text-gray-400 -mt-1" x-show="ni.quantity > 0 && ni.totalPaid > 0"
               x-text="'{{ __('Unit cost:') }} $' + (ni.totalPaid / ni.quantity).toFixed(4) + ' {{ __('each') }}'"></p>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('Quantity *') }}</label>
                    <input type="number" x-model.number="ni.quantity" min="1" placeholder="1"
                           class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('Expiry Date') }}</label>
                    <input type="date" x-model="ni.expiryDate"
                           class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
            </div>
            <button type="button" @click="addNewItem()"
                    :disabled="!ni.name.trim() || ni.quantity < 1 || (!ni.newCat && !ni.categoryId) || (ni.newCat && !ni.newCategoryName.trim())"
                    :class="ni.name.trim() && ni.quantity >= 1 && ((!ni.newCat && ni.categoryId) || (ni.newCat && ni.newCategoryName.trim())) ? 'bg-indigo-600 active:scale-95' : 'bg-gray-300 cursor-not-allowed'"
                    class="w-full text-white text-sm font-semibold py-2.5 rounded-xl transition-all">
                {{ __('Add New Item to Purchase') }}
            </button>
        </div>
    </div>

    {{-- Line items --}}
    <div x-show="lines.length > 0" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
        <h2 class="font-semibold text-gray-700 mb-3 text-sm uppercase tracking-wide">{{ __('Order Lines') }}</h2>
        <div class="space-y-4">
            <template x-for="(line, index) in lines" :key="index">
                <div class="border-b border-gray-100 pb-4 last:border-0 last:pb-0">
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex items-center gap-2 min-w-0">
                            <p class="font-medium text-gray-900 text-sm truncate" x-text="line.name"></p>
                            <span x-show="line.is_new"
                                  class="shrink-0 text-xs bg-emerald-100 text-emerald-700 font-semibold px-1.5 py-0.5 rounded-full">
                                {{ __('NEW') }}
                            </span>
                        </div>
                        <button type="button" @click="removeLine(index)" class="text-red-400 shrink-0 ml-2">
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
                            <label class="block text-xs text-gray-500 mb-1">{{ __('Total Paid ($)') }}</label>
                            <input type="number" x-model.number="line.total_paid" min="0" step="0.01"
                                   class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mt-1 text-right"
                       x-text="'{{ __('Unit cost:') }} $' + (line.quantity > 0 && line.total_paid > 0 ? (line.total_paid / line.quantity).toFixed(4) : '0.0000') + ' {{ __('each') }}'"></p>
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
                <input type="hidden" :name="'lines[' + index + '][item_id]'"            :value="line.is_new ? '' : line.item_id">
                <input type="hidden" :name="'lines[' + index + '][new_name]'"           :value="line.is_new ? line.new_name : ''">
                <input type="hidden" :name="'lines[' + index + '][new_category_id]'"    :value="line.is_new ? (line.new_category_id || '') : ''">
                <input type="hidden" :name="'lines[' + index + '][new_category_name]'"  :value="line.is_new ? (line.new_category_name || '') : ''">
                <input type="hidden" :name="'lines[' + index + '][new_price]'"          :value="line.is_new ? line.new_price : ''">
                <input type="hidden" :name="'lines[' + index + '][expiry_date]'"        :value="line.is_new ? (line.expiry_date || '') : ''">
                <input type="hidden" :name="'lines[' + index + '][quantity]'"           :value="line.quantity">
                <input type="hidden" :name="'lines[' + index + '][total_paid]'"         :value="line.total_paid || 0">
                <input type="hidden" :name="'lines[' + index + '][unit_cost]'"          :value="line.quantity > 0 ? line.total_paid / line.quantity : 0">
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
function purchaseBuilder(items, categories) {
    return {
        items,
        categories,
        search: '',
        purchaseDate: new Date().toISOString().split('T')[0],
        today: new Date().toISOString().split('T')[0],
        notes: '',
        lines: [],

        newItemOpen: false,
        ni: { name: '', categoryId: '', newCat: false, newCategoryName: '', price: 0, totalPaid: 0, quantity: 1, expiryDate: '' },

        filteredItems() {
            if (!this.search) return this.items;
            return this.items.filter(i => i.name.toLowerCase().includes(this.search.toLowerCase()));
        },

        addItem(item) {
            const existing = this.lines.find(l => !l.is_new && l.item_id === item.id);
            if (existing) {
                const unit = existing.quantity > 0 ? existing.total_paid / existing.quantity : 0;
                existing.quantity++;
                existing.total_paid = parseFloat((unit * existing.quantity).toFixed(2));
                return;
            }
            this.lines.push({
                item_id:    item.id,
                name:       item.name,
                quantity:   1,
                total_paid: parseFloat(item.cost_price) || 0,
                is_new:     false,
            });
        },

        addNewItem() {
            const catOk = this.ni.newCat ? this.ni.newCategoryName.trim() : this.ni.categoryId;
            if (!this.ni.name.trim() || this.ni.quantity < 1 || !catOk) return;
            this.lines.push({
                item_id:           null,
                is_new:            true,
                name:              this.ni.name.trim(),
                new_name:          this.ni.name.trim(),
                new_category_id:   this.ni.newCat ? '' : (this.ni.categoryId || ''),
                new_category_name: this.ni.newCat ? this.ni.newCategoryName.trim() : '',
                new_price:         this.ni.price || 0,
                expiry_date:       this.ni.expiryDate || '',
                quantity:          this.ni.quantity,
                total_paid:        this.ni.totalPaid || 0,
            });
            this.ni = { name: '', categoryId: '', newCat: false, newCategoryName: '', price: 0, totalPaid: 0, quantity: 1, expiryDate: '' };
            this.newItemOpen = false;
        },

        removeLine(index) { this.lines.splice(index, 1); },
        grandTotal() { return this.lines.reduce((s, l) => s + (l.total_paid || 0), 0); },
        canSubmit() {
            return this.purchaseDate && this.lines.length > 0
                && this.lines.every(l => l.quantity > 0 && (l.total_paid || 0) >= 0
                    && (l.is_new ? l.new_name.trim() : l.item_id));
        },
        init() {
            const key = 'draft_' + window.location.pathname;
            const isReload = (performance.getEntriesByType('navigation')[0]?.type || '') === 'reload';
            if (!isReload) {
                localStorage.removeItem(key);
            } else {
                const saved = localStorage.getItem(key);
                if (saved) {
                    try {
                        const s = JSON.parse(saved);
                        if (s.purchaseDate) this.purchaseDate = s.purchaseDate;
                        if (s.notes !== undefined) this.notes = s.notes;
                        if (s.lines) this.lines = s.lines;
                    } catch(e) {}
                }
            }
            let _submitting = false;
            this._clearDraft = () => { _submitting = true; localStorage.removeItem(key); };
            window.addEventListener('beforeunload', () => {
                if (_submitting) return;
                localStorage.setItem(key, JSON.stringify({
                    purchaseDate: this.purchaseDate,
                    notes: this.notes,
                    lines: this.lines,
                }));
            });
        },
        submit() {
            if (this.canSubmit()) {
                this._clearDraft();
                this.$refs.form.submit();
            }
        },
    };
}
</script>
@endsection
