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
        <div class="flex gap-2">
            <input type="text" name="barcode" x-ref="customInput"
                   x-model="barcode" @input="render()"
                   placeholder="{{ __('Type or scan your barcode') }}"
                   class="flex-1 border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none font-mono">
            <button type="button" @click="openCamera()"
                    class="shrink-0 flex items-center gap-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-3 py-2.5 rounded-xl text-sm transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                {{ __('Scan') }}
            </button>
        </div>
        <p class="text-xs text-gray-400 mt-1">{{ __('Type any number or text, or scan with a barcode scanner.') }}</p>
    </div>

    {{-- Camera modal --}}
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
            <div id="barcode-camera-reader" class="w-full"></div>
            <p class="text-xs text-gray-400 text-center py-3">{{ __('Point camera at a barcode') }}</p>
        </div>
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
