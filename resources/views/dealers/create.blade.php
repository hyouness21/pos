@extends('layouts.app')
@section('title', 'New Dealer')

@section('content')
<form method="POST" action="{{ route('dealers.store') }}" class="space-y-4">
    @csrf

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 space-y-4">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Company / Name *') }}</label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Phone') }}</label>
            <x-phone-input :value="old('phone')" />
            @error('phone')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Address') }}</label>
            <textarea name="address" rows="2"
                      class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none resize-none">{{ old('address') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Notes') }}</label>
            <textarea name="notes" rows="2"
                      class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none resize-none">{{ old('notes') }}</textarea>
        </div>

    </div>

    <button type="submit"
            class="w-full bg-indigo-600 text-white font-semibold py-3 rounded-2xl shadow active:scale-95 transition-transform">
        {{ __('Create Dealer') }}
    </button>
</form>

<script>
(function() {
    const KEY = 'draft_' + window.location.pathname;
    const hasErrors = {{ $errors->any() ? 'true' : 'false' }};

    function saveForm(form) {
        const data = {};
        form.querySelectorAll('input:not([type=file]):not([type=hidden]), textarea, select').forEach(el => {
            if (el.name) data[el.name] = el.value;
        });
        const ph = form.querySelector('input[type="hidden"][name="phone"]');
        if (ph && window.Alpine) {
            const wrap = ph.closest('[x-data]');
            if (wrap) { try { const d = Alpine.$data(wrap); data._pc = d.code; data._pn = d.number; } catch(e) {} }
        }
        localStorage.setItem(KEY, JSON.stringify(data));
    }

    function restoreForm(form, data) {
        Object.keys(data).forEach(k => {
            if (k.startsWith('_')) return;
            const el = form.querySelector('[name="' + k + '"]:not([type=file]):not([type=hidden])');
            if (el) { el.value = data[k]; el.dispatchEvent(new Event('input', { bubbles: true })); el.dispatchEvent(new Event('change', { bubbles: true })); }
        });
        if (data._pc || data._pn) {
            const ph = form.querySelector('input[type="hidden"][name="phone"]');
            if (ph) { const wrap = ph.closest('[x-data]'); if (wrap) wrap.dispatchEvent(new CustomEvent('set-phone', { detail: { code: data._pc || '+961', number: data._pn || '' } })); }
        }
    }

    const isReload = (performance.getEntriesByType('navigation')[0]?.type || '') === 'reload';
    if (!isReload) localStorage.removeItem(KEY);

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('main form');
        if (!form) return;
        let submitting = false;
        form.addEventListener('submit', () => { submitting = true; localStorage.removeItem(KEY); });
        window.addEventListener('beforeunload', () => { if (!submitting) saveForm(form); });
    });

    document.addEventListener('alpine:initialized', function() {
        if (!isReload || hasErrors) return;
        const saved = localStorage.getItem(KEY);
        if (!saved) return;
        const form = document.querySelector('main form');
        if (form) { try { restoreForm(form, JSON.parse(saved)); } catch(e) {} }
    });
})();
</script>
@endsection
