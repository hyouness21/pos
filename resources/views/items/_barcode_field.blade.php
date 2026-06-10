{{-- Barcode field partial — used in create & edit --}}
{{-- $value: current barcode string or null --}}
<div x-data="barcodeField('{{ $value ?? '' }}')">
    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('Barcode') }}</label>

    {{-- Mode toggle --}}
    <div class="flex rounded-xl overflow-hidden border border-gray-200 mb-3 text-sm font-medium">
        <button type="button" @click="mode = 'custom'; $nextTick(() => $refs.customInput?.focus())"
                :class="mode === 'custom' ? 'bg-indigo-600 text-white' : 'bg-gray-50 text-gray-500'"
                class="flex-1 py-2.5 transition-colors">
            {{ __('My Own Barcode') }}
        </button>
        <button type="button" @click="mode = 'random'"
                :class="mode === 'random' ? 'bg-indigo-600 text-white' : 'bg-gray-50 text-gray-500'"
                class="flex-1 py-2.5 border-l border-gray-200 transition-colors">
            {{ __('Random') }}
        </button>
    </div>

    {{-- Custom mode --}}
    <div x-show="mode === 'custom'">
        <input type="text" name="barcode" x-ref="customInput"
               x-model="barcode" @input="render()"
               placeholder="{{ __('Type or scan your barcode') }}"
               class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none font-mono">
        <p class="text-xs text-gray-400 mt-1">{{ __('Type any number or text, or scan with a barcode scanner.') }}</p>
    </div>

    {{-- Random mode --}}
    <div x-show="mode === 'random'" x-cloak>
        <input type="hidden" name="barcode" x-model="barcode">
        <button type="button" @click="generate()"
                class="w-full bg-gray-100 text-gray-700 font-medium py-2.5 rounded-xl text-sm active:bg-gray-200 transition-colors">
            {{ __('Generate Random Barcode') }}
        </button>
    </div>

    {{-- Preview --}}
    <div x-show="barcode.length > 0" x-cloak
         class="mt-3 bg-gray-50 border border-gray-200 rounded-xl p-4 flex flex-col items-center gap-2">
        <svg x-ref="svg" class="max-w-full"></svg>
        <p class="text-xs font-mono text-gray-500" x-text="barcode"></p>
    </div>
</div>
