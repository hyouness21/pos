@extends('layouts.app')
@section('title', 'Edit Category')

@section('content')
<form method="POST" action="{{ route('categories.update', $category) }}" enctype="multipart/form-data" class="space-y-4">
    @csrf @method('PUT')

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 space-y-4">

        @if ($category->image)
            <img src="{{ Storage::url($category->image) }}" class="w-full h-36 object-cover rounded-xl">
        @endif

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Name *') }}</label>
            <input type="text" name="name" value="{{ old('name', $category->name) }}" required
                   class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Description') }}</label>
            <textarea name="description" rows="3"
                      class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none resize-none">{{ old('description', $category->description) }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Replace Image') }}</label>
            <input type="file" name="image" accept="image/*"
                   class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700">
        </div>

    </div>

    <button type="submit"
            class="w-full bg-indigo-600 text-white font-semibold py-3 rounded-2xl shadow active:scale-95 transition-transform">
        {{ __('Save Changes') }}
    </button>

    <a href="{{ route('categories.index') }}"
       class="block text-center text-gray-500 text-sm py-2">{{ __('Cancel') }}</a>
</form>
@endsection
