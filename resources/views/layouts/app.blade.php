<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#4f46e5">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @if(app()->getLocale() === 'ar')
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet" />
    @endif

    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🛒</text></svg>">
    <title>{{ config('app.name') }} — @yield('title', 'POS')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @if(app()->getLocale() === 'ar')
    <style>body { font-family: 'Cairo', sans-serif; }</style>
    @endif
</head>
<body class="h-full bg-gray-50 font-sans antialiased" x-data>

    {{-- Top header --}}
    <header class="fixed top-0 inset-x-0 z-40 bg-indigo-600 text-white shadow-md">
        <div class="flex items-center justify-between px-4 h-14">
            <h1 class="text-lg font-bold tracking-tight">@yield('title', 'POS')</h1>
            <div class="flex items-center gap-3">
                @yield('header-actions')
                {{-- Language toggle --}}
                @if(app()->getLocale() === 'ar')
                    <a href="{{ route('locale.switch', 'en') }}" class="text-indigo-200 hover:text-white text-sm font-bold">EN</a>
                @else
                    <a href="{{ route('locale.switch', 'ar') }}" class="text-indigo-200 hover:text-white text-sm font-bold">ع</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-indigo-200 hover:text-white text-sm font-medium">{{ __('Logout') }}</button>
                </form>
            </div>
        </div>
    </header>

    {{-- Page content --}}
    <main class="pt-14 pb-20 min-h-full">
        @if (session('success'))
            <div class="mx-4 mt-3 px-4 py-3 bg-green-100 border border-green-300 text-green-800 rounded-xl text-sm flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mx-4 mt-3 px-4 py-3 bg-red-100 border border-red-300 text-red-800 rounded-xl text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="px-4 py-4">
            @yield('content')
        </div>
    </main>

    {{-- Bottom navigation --}}
    @include('layouts.partials.bottom-nav')

    @stack('scripts')
</body>
</html>
