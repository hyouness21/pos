<nav class="fixed bottom-0 inset-x-0 z-40 bg-white border-t border-gray-200 shadow-lg">
    <div class="grid grid-cols-7 h-16">

        @php
            if (request()->routeIs('reports.*')) $current = 'reports';
            elseif (request()->routeIs('categories.*') || request()->routeIs('items.*')) $current = 'catalog';
            elseif (request()->routeIs('storehouse')) $current = 'storehouse';
            elseif (request()->routeIs('invoices.*')) $current = 'invoices';
            elseif (request()->routeIs('customers.*')) $current = 'customers';
            elseif (request()->routeIs('dealers.*') || request()->routeIs('dealer-purchases.*')) $current = 'dealers';
            elseif (request()->routeIs('expenses.*')) $current = 'reports';
            else $current = '';
        @endphp

        {{-- Reports --}}
        <a href="{{ route('reports.index') }}"
           class="flex flex-col items-center justify-center gap-0.5 font-medium transition-colors
                  {{ $current === 'reports' ? 'text-indigo-600' : 'text-gray-500 hover:text-indigo-500' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <span class="text-[11px]">{{ __('Reports') }}</span>
        </a>

        {{-- Catalog --}}
        <a href="{{ route('categories.index') }}"
           class="flex flex-col items-center justify-center gap-0.5 font-medium transition-colors
                  {{ $current === 'catalog' ? 'text-indigo-600' : 'text-gray-500 hover:text-indigo-500' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
            </svg>
            <span class="text-[11px]">{{ __('Catalog') }}</span>
        </a>

        {{-- Storehouse --}}
        <a href="{{ route('storehouse') }}"
           class="flex flex-col items-center justify-center gap-0.5 font-medium transition-colors
                  {{ $current === 'storehouse' ? 'text-indigo-600' : 'text-gray-500 hover:text-indigo-500' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
            </svg>
            <span class="text-[11px]">{{ __('Stock') }}</span>
        </a>

        {{-- New Invoice (FAB — center slot 4 of 7) --}}
        <a href="{{ route('invoices.create') }}"
           class="flex flex-col items-center justify-center">
            <span class="flex items-center justify-center -mt-5 rounded-full bg-indigo-600 text-white shadow-lg active:scale-95 transition-transform"
                  style="width:48px;height:48px;">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
            </span>
        </a>

        {{-- Invoices --}}
        <a href="{{ route('invoices.index') }}"
           class="flex flex-col items-center justify-center gap-0.5 font-medium transition-colors
                  {{ $current === 'invoices' ? 'text-indigo-600' : 'text-gray-500 hover:text-indigo-500' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span class="text-[11px]">{{ __('Invoices') }}</span>
        </a>

        {{-- Customers --}}
        <a href="{{ route('customers.index') }}"
           class="flex flex-col items-center justify-center gap-0.5 font-medium transition-colors
                  {{ $current === 'customers' ? 'text-indigo-600' : 'text-gray-500 hover:text-indigo-500' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span class="text-[11px]">{{ __('Clients') }}</span>
        </a>

        {{-- Dealers --}}
        <a href="{{ route('dealers.index') }}"
           class="flex flex-col items-center justify-center gap-0.5 font-medium transition-colors
                  {{ $current === 'dealers' ? 'text-indigo-600' : 'text-gray-500 hover:text-indigo-500' }}">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            <span class="text-[11px]">{{ __('Dealers') }}</span>
        </a>

    </div>
</nav>
