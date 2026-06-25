@extends('layouts.app')
@section('title', 'New Invoice')

@section('content')

<div x-data="invoiceBuilder({{ $categories->toJson() }})" class="space-y-4">

    {{-- Customer & Payment --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 space-y-4">

        <div>
            <div class="flex items-center justify-between mb-1">
                <label class="block text-sm font-medium text-gray-700">{{ __('Customer *') }}</label>
                <button type="button" @click="newCustomer = !newCustomer; customerId = ''; newName = ''; newPhone = ''; newAddress = ''"
                        class="text-xs font-medium text-indigo-600 active:opacity-70">
                    <span x-text="newCustomer ? '{{ __('← Select existing') }}' : '{{ __('+ New customer') }}'"></span>
                </button>
            </div>

            {{-- Existing customer dropdown --}}
            <select x-show="!newCustomer" x-model="customerId"
                    class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                <option value="">{{ __('Select customer') }}</option>
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                @endforeach
            </select>

            {{-- New customer mini-form --}}
            <div x-show="newCustomer" class="space-y-2">
                @error('new_customer_phone')
                    <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-2.5">
                        {{ $message }}
                    </div>
                @enderror
                <input type="text" x-model="newName" placeholder="{{ __('Customer name *') }}"
                       autocapitalize="words" autocomplete="off"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                <div class="flex gap-2">
                    <div class="relative shrink-0" @click.outside="phoneOpen = false">
                        <button type="button" @click="phoneOpen = !phoneOpen"
                                class="flex items-center gap-1 border border-gray-300 rounded-xl px-3 py-2.5 bg-white text-sm focus:ring-2 focus:ring-indigo-500 outline-none whitespace-nowrap">
                            <span x-text="(phoneCodes.find(c => c.code === dialCode) || phoneCodes[0]).flag" class="text-base"></span>
                            <span x-text="dialCode" class="text-gray-700 font-medium"></span>
                            <svg class="w-3.5 h-3.5 text-gray-400 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="phoneOpen" x-cloak
                             class="absolute z-50 top-full left-0 mt-1 w-56 bg-white border border-gray-200 rounded-xl shadow-lg overflow-y-auto overscroll-y-contain" style="max-height:185px">
                            <template x-for="c in phoneCodes" :key="c.code">
                                <button type="button" @click="dialCode = c.code; phoneOpen = false"
                                        class="w-full flex items-center gap-2 px-3 py-2 text-sm hover:bg-indigo-50 text-left"
                                        :class="c.code === dialCode ? 'bg-indigo-50 font-semibold text-indigo-700' : 'text-gray-700'">
                                    <span x-text="c.flag" class="text-base shrink-0"></span>
                                    <span x-text="c.name" class="flex-1 truncate"></span>
                                    <span x-text="c.code" class="text-gray-400 text-xs shrink-0"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                    <input type="tel" x-model="newPhone" placeholder="{{ __('Phone number') }} *"
                           autocomplete="off"
                           class="flex-1 border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <input type="text" x-model="newAddress" placeholder="{{ __('Address') }}"
                       autocomplete="off"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Payment Method *') }}</label>
            <div class="grid grid-cols-2 gap-3">
                <label class="flex items-center gap-2 border border-gray-300 rounded-xl px-4 py-3 cursor-pointer"
                       :class="paymentMethod === 'cash' ? 'border-indigo-500 bg-indigo-50' : ''">
                    <input type="radio" x-model="paymentMethod" value="cash" class="text-indigo-600">
                    <span class="text-sm font-medium">{{ __('Cash (Paid)') }}</span>
                </label>
                <label class="flex items-center gap-2 border border-gray-300 rounded-xl px-4 py-3 cursor-pointer"
                       :class="paymentMethod === 'pay_later' ? 'border-orange-400 bg-orange-50' : ''">
                    <input type="radio" x-model="paymentMethod" value="pay_later" class="text-orange-500">
                    <span class="text-sm font-medium">{{ __('Pay Later') }}</span>
                </label>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Date') }}</label>
            <input type="date" x-model="invoiceDate" max="{{ date('Y-m-d') }}"
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

        <h2 class="font-semibold text-gray-700 mb-3 text-sm uppercase tracking-wide">{{ __('Items') }}</h2>

        {{-- Barcode scan input --}}
        <div class="relative mb-3">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 4v1m0 14v1M4 12h1m14 0h1M6.3 6.3l.7.7m10 10l.7.7M6.3 17.7l.7-.7m10-10l.7-.7
                             M8 12a4 4 0 118 0 4 4 0 01-8 0z"/>
                </svg>
            </span>
            <input type="text" x-ref="scanInput"
                   @keydown.enter.prevent="scanBarcode($el.value); $el.value = ''"
                   placeholder="{{ __('Scan barcode…') }}"
                   autocomplete="off"
                   class="w-full border border-gray-200 rounded-xl pl-9 pr-10 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none font-mono bg-gray-50">
            <button type="button" @click="openCamera()"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-indigo-600 transition-colors"
                    title="{{ __('Scan from camera') }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </button>
            <div x-show="scanMsg" x-cloak x-transition
                 :class="scanFound ? 'text-green-600 bg-green-50' : 'text-red-500 bg-red-50'"
                 class="absolute inset-x-0 -bottom-6 text-xs font-medium text-center py-0.5 rounded-b-xl"
                 x-text="scanMsg"></div>
        </div>

        {{-- Camera scanner modal --}}
        <div x-show="cameraOpen" x-cloak
             class="fixed inset-0 z-50 bg-black/80 flex flex-col items-center justify-center p-4"
             @keydown.escape.window="closeCamera()">
            <div class="bg-white rounded-2xl overflow-hidden w-full max-w-sm shadow-2xl">
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                    <span class="font-semibold text-gray-800 text-sm">{{ __('Scan Barcode') }}</span>
                    <button type="button" @click="closeCamera()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div id="camera-reader" class="w-full"></div>
                <p class="text-xs text-gray-400 text-center py-3">{{ __('Point camera at a barcode') }}</p>
            </div>
        </div>

        {{-- Category tabs --}}
        <div class="flex gap-2 overflow-x-auto pb-2 mb-3 scrollbar-hide">
            <template x-for="cat in categories" :key="cat.id">
                <button type="button" @click="activeCategory = cat.id; itemSearch = ''"
                        class="shrink-0 px-3 py-1.5 rounded-full text-sm font-medium transition-colors"
                        :class="activeCategory === cat.id ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600'">
                    <span x-text="cat.name"></span>
                </button>
            </template>
        </div>

        {{-- Item search --}}
        <div class="relative mb-3">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                </svg>
            </span>
            <input type="text" x-model="itemSearch"
                   placeholder="{{ __('Search items…') }}"
                   autocomplete="off"
                   class="w-full border border-gray-200 rounded-xl pl-9 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-gray-50">
        </div>

        {{-- Items grid --}}
        <div class="grid grid-cols-2 gap-2">
            <template x-for="item in filteredItems()" :key="item.id">
                    <button type="button"
                            @click="addItem(item)"
                            :disabled="item.stock <= 0 || orderedQty(item.id) >= item.stock"
                            :class="item.stock <= 0 || orderedQty(item.id) >= item.stock
                                ? 'border-gray-100 bg-gray-50 opacity-50 cursor-not-allowed'
                                : 'border-gray-200 active:bg-indigo-50 cursor-pointer'"
                            class="border rounded-xl p-3 text-left transition-colors w-full">
                        <p class="font-medium text-sm truncate"
                           :class="item.stock <= 0 ? 'text-gray-400' : 'text-gray-900'"
                           x-text="item.name"></p>
                        <p class="font-bold text-sm mt-0.5"
                           :class="item.stock <= 0 ? 'text-gray-400' : 'text-indigo-600'"
                           x-text="'$' + parseFloat(item.price).toFixed(2)"></p>
                        <p class="text-xs mt-0.5"
                           :class="item.stock <= 0 ? 'text-red-400 font-medium' : 'text-gray-400'"
                           x-text="item.stock <= 0 ? '{{ __('Out of stock') }}' : (item.stock - orderedQty(item.id) <= 0 ? '{{ __('Max reached') }}' : (item.stock - orderedQty(item.id)) + ' {{ __('left') }}')"></p>
                    </button>
            </template>
        </div>

    </div>

    {{-- Line items --}}
    <div x-show="lines.length > 0" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
        <h2 class="font-semibold text-gray-700 mb-3 text-sm uppercase tracking-wide">{{ __('Order Lines') }}</h2>
        <div class="space-y-3">
            <template x-for="(line, index) in lines" :key="index">
                <div class="border-b border-gray-100 pb-3 last:border-0 last:pb-0">
                    {{-- Top row: name / qty / total / remove --}}
                    <div class="flex items-center gap-3">
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-900 text-sm truncate" x-text="line.name"></p>
                            <div class="flex items-center gap-1 mt-0.5">
                                <span class="text-xs text-gray-400">$</span>
                                <input type="number" x-model="line.unit_price" min="0" step="0.01"
                                       class="w-20 text-xs border-b border-gray-300 focus:border-indigo-400 outline-none text-gray-700 bg-transparent">
                                <span class="text-xs text-gray-400">{{ __('each') }}</span>
                                <span x-show="parseFloat(line.unit_price) !== line.original_price" x-cloak
                                      class="text-xs text-orange-400"
                                      x-text="'(was $' + line.original_price.toFixed(2) + ')'"></span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <button type="button" @click="decrement(index)"
                                    class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 font-bold active:bg-gray-200">−</button>
                            <span class="w-6 text-center font-semibold text-sm" x-text="line.quantity"></span>
                            <button type="button" @click="increment(index)"
                                    class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold active:bg-indigo-200">+</button>
                        </div>
                        <p class="w-16 text-right font-bold text-gray-900 text-sm shrink-0"
                           x-text="'$' + lineTotal(line).toFixed(2)"></p>
                        <button type="button" @click="removeLine(index)"
                                class="text-red-400 hover:text-red-600 shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    {{-- Free quantity row --}}
                    <div class="flex items-center gap-2 mt-2 pl-0.5">
                        <span class="text-xs text-gray-400">🎁 {{ __('Free') }}:</span>
                        <button type="button" @click="decrementFree(index)"
                                :disabled="line.free_qty === 0"
                                class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 text-sm font-bold active:bg-gray-200 disabled:opacity-30">−</button>
                        <span class="w-5 text-center text-sm font-semibold"
                              :class="line.free_qty > 0 ? 'text-green-600' : 'text-gray-400'"
                              x-text="line.free_qty"></span>
                        <button type="button" @click="incrementFree(index)"
                                :disabled="line.free_qty >= line.quantity"
                                class="w-6 h-6 rounded-full bg-green-100 flex items-center justify-center text-green-700 text-sm font-bold active:bg-green-200 disabled:opacity-30">+</button>
                        <span x-show="line.free_qty > 0" x-cloak
                              class="text-xs text-green-600 font-medium"
                              x-text="'(' + (line.quantity - line.free_qty) + ' {{ __('paid') }}, ' + line.free_qty + ' {{ __('free') }}'  + ')'"></span>
                    </div>
                </div>
            </template>
        </div>

        {{-- Discount --}}
        <div x-show="lines.length > 0" x-cloak class="mt-3 pt-3 border-t border-gray-100">
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-600 flex-1">{{ __('Discount') }}</span>
                <div class="flex rounded-lg overflow-hidden border border-gray-200 text-xs font-medium">
                    <button type="button" @click="discountType = 'fixed'"
                            :class="discountType === 'fixed' ? 'bg-indigo-600 text-white' : 'bg-gray-50 text-gray-500'"
                            class="px-3 py-1.5 transition-colors">$</button>
                    <button type="button" @click="discountType = 'percent'"
                            :class="discountType === 'percent' ? 'bg-indigo-600 text-white' : 'bg-gray-50 text-gray-500'"
                            class="px-3 py-1.5 border-l border-gray-200 transition-colors">%</button>
                </div>
                <input type="number" x-model="discountRaw"
                       min="0" :max="discountType === 'percent' ? 100 : subtotal()"
                       step="0.01" placeholder="0"
                       class="w-20 border border-gray-200 rounded-lg px-2 py-1.5 text-sm text-right outline-none focus:ring-2 focus:ring-indigo-400">
            </div>
        </div>

        {{-- Total --}}
        <div class="mt-3 pt-3 border-t border-gray-200 space-y-1">
            <div x-show="discountAmount() > 0" class="flex justify-between items-center text-sm text-gray-400">
                <span>{{ __('Subtotal') }}</span>
                <span x-text="'$' + subtotal().toFixed(2)"></span>
            </div>
            <div x-show="discountAmount() > 0" class="flex justify-between items-center text-sm text-red-500 font-medium">
                <span>{{ __('Discount') }}</span>
                <span x-text="'− $' + discountAmount().toFixed(2)"></span>
            </div>
            <div class="flex justify-between items-center">
                <span class="font-bold text-gray-700">{{ __('Total') }}</span>
                <span class="text-2xl font-bold text-indigo-600" x-text="'$' + grandTotal().toFixed(2)"></span>
            </div>
        </div>
    </div>

    {{-- Hidden form inputs + submit --}}
    <form id="invoice-form" method="POST" action="{{ route('invoices.store') }}" x-ref="form">
        @csrf
        <input type="hidden" name="customer_id" :value="newCustomer ? '' : customerId">
        <input type="hidden" name="new_customer_name" :value="newCustomer ? newName : ''">
        <input type="hidden" name="new_customer_phone" :value="newCustomer && newPhone ? dialCode + newPhone : ''">
        <input type="hidden" name="new_customer_address" :value="newCustomer ? newAddress : ''">
        <input type="hidden" name="invoice_date" :value="invoiceDate">
        <input type="hidden" name="payment_method" :value="paymentMethod">
        <input type="hidden" name="notes" :value="notes">
        <input type="hidden" name="discount" :value="discountAmount().toFixed(2)">
        <template x-for="(line, index) in submissionLines()" :key="index">
            <span>
                <input type="hidden" :name="'lines[' + index + '][item_id]'" :value="line.item_id">
                <input type="hidden" :name="'lines[' + index + '][quantity]'" :value="line.quantity">
                <input type="hidden" :name="'lines[' + index + '][unit_price]'" :value="line.unit_price">
            </span>
        </template>
    </form>

    <button type="button" @click="submit()"
            :disabled="!canSubmit()"
            :class="canSubmit() ? 'bg-indigo-600 active:scale-95' : 'bg-gray-300 cursor-not-allowed'"
            class="w-full text-white font-semibold py-3 rounded-2xl shadow transition-all">
        <span x-text="canSubmit() ? '{{ __('Create Invoice') }} — $' + grandTotal().toFixed(2) : '{{ __('Add items to continue') }}'"></span>
    </button>

</div>

<script>
function invoiceBuilder(categories) {
    return {
        categories,
        activeCategory: categories.length ? categories[0].id : null,
        customerId: '',
        newCustomer: {{ $errors->has('new_customer_phone') ? 'true' : 'false' }},
        newName: '{{ old('new_customer_name', '') }}',
        dialCode: '+961',
        phoneOpen: false,
        phoneCodes: [
            {code:'+961',flag:'🇱🇧',name:'Lebanon'},
            {code:'+213',flag:'🇩🇿',name:'Algeria'},
            {code:'+1',flag:'🇺🇸',name:'USA / Canada'},
            {code:'+44',flag:'🇬🇧',name:'UK'},
            {code:'+33',flag:'🇫🇷',name:'France'},
            {code:'+49',flag:'🇩🇪',name:'Germany'},
            {code:'+34',flag:'🇪🇸',name:'Spain'},
            {code:'+39',flag:'🇮🇹',name:'Italy'},
            {code:'+212',flag:'🇲🇦',name:'Morocco'},
            {code:'+216',flag:'🇹🇳',name:'Tunisia'},
            {code:'+218',flag:'🇱🇾',name:'Libya'},
            {code:'+20',flag:'🇪🇬',name:'Egypt'},
            {code:'+221',flag:'🇸🇳',name:'Senegal'},
            {code:'+966',flag:'🇸🇦',name:'Saudi Arabia'},
            {code:'+971',flag:'🇦🇪',name:'UAE'},
            {code:'+974',flag:'🇶🇦',name:'Qatar'},
            {code:'+965',flag:'🇰🇼',name:'Kuwait'},
            {code:'+962',flag:'🇯🇴',name:'Jordan'},
            {code:'+963',flag:'🇸🇾',name:'Syria'},
            {code:'+964',flag:'🇮🇶',name:'Iraq'},
            {code:'+90',flag:'🇹🇷',name:'Turkey'},
            {code:'+98',flag:'🇮🇷',name:'Iran'},
            {code:'+92',flag:'🇵🇰',name:'Pakistan'},
            {code:'+91',flag:'🇮🇳',name:'India'},
            {code:'+86',flag:'🇨🇳',name:'China'},
            {code:'+55',flag:'🇧🇷',name:'Brazil'},
            {code:'+27',flag:'🇿🇦',name:'South Africa'},
            {code:'+234',flag:'🇳🇬',name:'Nigeria'},
        ],
        newPhone: '',
        newAddress: '',
        invoiceDate: new Date().toISOString().slice(0, 10),
        paymentMethod: 'cash',
        notes: '',
        lines: [],
        itemSearch: '',
        discountType: 'fixed',
        discountRaw: 0,
        scanMsg: '',
        scanFound: false,
        _scanTimer: null,
        cameraOpen: false,
        _scanner: null,

        openCamera() {
            this.cameraOpen = true;
            this.$nextTick(() => {
                if (typeof Html5Qrcode === 'undefined') return;
                this._scanner = new Html5Qrcode('camera-reader');
                this._scanner.start(
                    { facingMode: 'environment' },
                    {
                        fps: 15,
                        qrbox: { width: 280, height: 100 },
                        formatsToSupport: [
                            Html5QrcodeSupportedFormats.CODE_128,
                            Html5QrcodeSupportedFormats.EAN_13,
                            Html5QrcodeSupportedFormats.EAN_8,
                            Html5QrcodeSupportedFormats.CODE_39,
                            Html5QrcodeSupportedFormats.UPC_A,
                            Html5QrcodeSupportedFormats.UPC_E,
                        ]
                    },
                    (code) => { this.closeCamera(); this.scanBarcode(code); },
                    () => {}
                ).catch(() => { this.cameraOpen = false; });
            });
        },
        closeCamera() {
            if (this._scanner) {
                this._scanner.stop().catch(() => {});
                this._scanner = null;
            }
            this.cameraOpen = false;
        },

        scanBarcode(code) {
            code = (code || '').trim();
            if (!code) return;
            const item = this.categories.flatMap(c => c.items).find(i => i.barcode === code);
            clearTimeout(this._scanTimer);
            if (item) {
                if (item.stock <= 0 || this.orderedQty(item.id) >= item.stock) {
                    this.scanFound = false;
                    this.scanMsg = '{{ __('Out of stock') }}: ' + item.name;
                } else {
                    this.addItem(item);
                    this.activeCategory = item.category_id;
                    this.scanFound = true;
                    this.scanMsg = '✓ ' + item.name;
                }
            } else {
                this.scanFound = false;
                this.scanMsg = '{{ __('Barcode not found') }}: ' + code;
            }
            this._scanTimer = setTimeout(() => this.scanMsg = '', 2500);
        },

        filteredItems() {
            if (this.itemSearch.trim()) {
                const q = this.itemSearch.toLowerCase();
                return this.categories.flatMap(c => c.items).filter(i => i.name.toLowerCase().includes(q));
            }
            const cat = this.categories.find(c => c.id === this.activeCategory);
            return cat ? cat.items : [];
        },

        orderedQty(itemId) {
            const line = this.lines.find(l => l.item_id === itemId);
            return line ? line.quantity : 0;
        },

        addItem(item) {
            if (this.orderedQty(item.id) >= item.stock) return;
            const existing = this.lines.find(l => l.item_id === item.id);
            if (existing) {
                existing.quantity++;
            } else {
                this.lines.push({
                    item_id: item.id,
                    name: item.name,
                    original_price: parseFloat(item.price),
                    unit_price: parseFloat(item.price),
                    stock: item.stock,
                    quantity: 1,
                    free_qty: 0,
                });
            }
        },

        increment(index) {
            const line = this.lines[index];
            if (line.quantity < line.stock) line.quantity++;
        },
        decrement(index) {
            const line = this.lines[index];
            if (line.quantity > 1) {
                line.quantity--;
                if (line.free_qty > line.quantity) line.free_qty = line.quantity;
            } else {
                this.removeLine(index);
            }
        },
        incrementFree(index) {
            const line = this.lines[index];
            if (line.free_qty < line.quantity) line.free_qty++;
        },
        decrementFree(index) {
            if (this.lines[index].free_qty > 0) this.lines[index].free_qty--;
        },
        removeLine(index) { this.lines.splice(index, 1); },
        lineTotal(line) { return (line.quantity - line.free_qty) * parseFloat(line.unit_price || 0); },
        subtotal() { return this.lines.reduce((s, l) => s + this.lineTotal(l), 0); },
        discountAmount() {
            const sub = this.subtotal();
            const v = parseFloat(this.discountRaw) || 0;
            if (this.discountType === 'percent') return Math.min(sub, sub * v / 100);
            return Math.min(sub, v);
        },
        grandTotal() { return Math.max(0, this.subtotal() - this.discountAmount()); },
        submissionLines() {
            const result = [];
            for (const line of this.lines) {
                const paidQty = line.quantity - line.free_qty;
                if (paidQty > 0) {
                    result.push({ item_id: line.item_id, quantity: paidQty, unit_price: parseFloat(line.unit_price || 0) });
                }
                if (line.free_qty > 0) {
                    result.push({ item_id: line.item_id, quantity: line.free_qty, unit_price: 0 });
                }
            }
            return result;
        },
        canSubmit() {
            const hasCustomer = this.newCustomer ? this.newName.trim() !== '' && this.newPhone.trim() !== '' : this.customerId !== '';
            return hasCustomer && this.paymentMethod && this.lines.length > 0;
        },

        submit() {
            if (this.canSubmit()) {
                this.$refs.form.submit();
            }
        },
    };
}
</script>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
@endpush
