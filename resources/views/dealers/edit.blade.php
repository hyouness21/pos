@extends('layouts.app')
@section('title', 'Edit Dealer')

@section('content')
<form method="POST" action="{{ route('dealers.update', $dealer) }}" class="space-y-4">
    @csrf @method('PUT')

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 space-y-4">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Company / Name *') }}</label>
            <input type="text" name="name" value="{{ old('name', $dealer->name) }}" required
                   class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Phone') }}</label>
            <x-phone-input :value="old('phone', $dealer->phone)" />
            @error('phone')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Address') }}</label>
            <textarea name="address" rows="2"
                      class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none resize-none">{{ old('address', $dealer->address) }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Notes') }}</label>
            <textarea name="notes" rows="2"
                      class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none resize-none">{{ old('notes', $dealer->notes) }}</textarea>
        </div>

    </div>

    <button type="submit"
            class="w-full bg-indigo-600 text-white font-semibold py-3 rounded-2xl shadow active:scale-95 transition-transform">
        {{ __('Save Changes') }}
    </button>

    <div class="flex gap-3">
        <a href="{{ route('dealers.show', $dealer) }}"
           class="flex-1 text-center text-gray-500 text-sm py-2">{{ __('Cancel') }}</a>
        <form method="POST" action="{{ route('dealers.destroy', $dealer) }}"
              onsubmit="return confirm('{{ __('Delete this dealer?') }}')" class="flex-1">
            @csrf @method('DELETE')
            <button type="submit" class="w-full text-red-500 text-sm py-2">{{ __('Delete Dealer') }}</button>
        </form>
    </div>
</form>
@endsection
