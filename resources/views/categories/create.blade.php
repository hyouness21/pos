@extends('layouts.app')
@section('title', 'New Category')

@section('content')
<form method="POST" action="{{ route('categories.store') }}" enctype="multipart/form-data" class="space-y-4">
    @csrf

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 space-y-4">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Name *') }}</label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Description') }}</label>
            <textarea name="description" rows="3"
                      class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none resize-none">{{ old('description') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Image') }}</label>
            <input type="file" name="image" accept="image/*"
                   class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700">
        </div>

    </div>

    <button type="submit"
            class="w-full bg-indigo-600 text-white font-semibold py-3 rounded-2xl shadow active:scale-95 transition-transform">
        {{ __('Create Category') }}
    </button>
</form>

<script>
(function() {
    const KEY = 'draft_' + window.location.pathname;
    const hasErrors = {{ $errors->any() ? 'true' : 'false' }};

    const isReload = (performance.getEntriesByType('navigation')[0]?.type || '') === 'reload';
    if (!isReload) localStorage.removeItem(KEY);

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('main form');
        if (!form) return;
        let submitting = false;
        form.addEventListener('submit', () => { submitting = true; localStorage.removeItem(KEY); });
        window.addEventListener('beforeunload', function() {
            if (submitting) return;
            const data = {};
            form.querySelectorAll('input:not([type=file]):not([type=hidden]), textarea').forEach(el => {
                if (el.name) data[el.name] = el.value;
            });
            localStorage.setItem(KEY, JSON.stringify(data));
        });
        if (isReload && !hasErrors) {
            const saved = localStorage.getItem(KEY);
            if (saved) {
                try {
                    const data = JSON.parse(saved);
                    Object.keys(data).forEach(k => {
                        const el = form.querySelector('[name="' + k + '"]:not([type=file])');
                        if (el) el.value = data[k];
                    });
                } catch(e) {}
            }
        }
    });
})();
</script>
@endsection
