@extends('layouts.app')
@section('title', 'Edit Customer')

@section('content')
<form method="POST" action="{{ route('customers.update', $customer) }}" class="space-y-4">
    @csrf @method('PUT')

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 space-y-4">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Full Name *') }}</label>
            <input type="text" name="name" value="{{ old('name', $customer->name) }}" required
                   class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Phone *') }}</label>
            <x-phone-input :value="old('phone', $customer->phone)" required />
            @error('phone')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Address') }}</label>
            <textarea name="address" rows="2"
                      class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none resize-none">{{ old('address', $customer->address) }}</textarea>
        </div>

    </div>

    <button type="submit"
            class="w-full bg-indigo-600 text-white font-semibold py-3 rounded-2xl shadow active:scale-95 transition-transform">
        {{ __('Save Changes') }}
    </button>

    <a href="{{ route('customers.show', $customer) }}"
       class="w-full flex items-center justify-center text-gray-500 text-sm py-2">{{ __('Cancel') }}</a>
</form>

@error('delete')
    <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-2.5 mt-2">
        {{ $message }}
    </div>
@enderror

<div x-data="{ open: false }" class="mt-2">
    <button type="button" @click="open = true"
            class="w-full text-red-500 text-sm py-3 font-medium">{{ __('Delete Customer') }}</button>

    <div x-show="open" x-cloak
         class="fixed inset-0 z-50 flex items-end justify-center bg-black/50 pb-6 px-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-5 space-y-4">
            <h2 class="font-bold text-gray-900 text-lg">{{ __('Delete Customer') }}</h2>
            <p class="text-sm text-gray-500">{{ __('Are you sure? This cannot be undone.') }}</p>
            <div class="grid grid-cols-2 gap-3">
                <button type="button" @click="open = false"
                        class="py-3 rounded-xl border border-gray-200 text-gray-600 font-semibold text-sm">
                    {{ __('Cancel') }}
                </button>
                <form method="POST" action="{{ route('customers.destroy', $customer) }}">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="w-full py-3 rounded-xl bg-red-500 text-white font-semibold text-sm active:scale-95 transition-transform">
                        {{ __('Delete') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
