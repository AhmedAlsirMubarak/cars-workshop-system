<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Workshop Pro' }} — Tsunami Garage</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Arabic:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="h-full bg-gray-50 font-sans antialiased">

<div class="flex h-screen overflow-hidden" x-data="sidebar()">

    {{-- ── Mobile overlay ── --}}
    <div x-show="mobileOpen" x-cloak
        class="fixed inset-0 z-20 bg-black/60 lg:hidden"
        @click="closeMobile()">
    </div>

    {{-- ── Sidebar ── --}}
    <aside :class="[
        'fixed inset-y-0 start-0 z-30 flex flex-col bg-gray-900 transition-all duration-300 ease-in-out',
        'lg:static lg:translate-x-0',
        mobileOpen ? 'translate-x-0 rtl:-translate-x-0 w-72' : '-translate-x-full rtl:translate-x-full w-72',
        open ? 'lg:w-64' : 'lg:w-16',
    ]">

        {{-- Logo --}}
        <div class="flex items-center h-16 px-4 border-b border-gray-800 shrink-0 gap-3">
          <img src="{{ asset('images/ts-w-logo.png') }}" class="h-10 max-w-full" alt="Tsunami Garage">

            <span :class="open ? 'lg:opacity-100 lg:max-w-xs' : 'lg:opacity-0 lg:max-w-0 lg:overflow-hidden'"
                class="text-white font-semibold text-sm truncate transition-all duration-300">
                Tsunami Garage
            </span>
            <button @click="closeMobile()" class="lg:hidden ms-auto text-gray-400 hover:text-white p-1">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Nav links --}}
        <nav class="flex-1 py-3 overflow-y-auto space-y-0.5">
            @php $current = request()->routeIs(...array_keys($navItems ?? [])) @endphp

            @include('layouts.nav-item', ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'home'])
            @include('layouts.nav-item', ['route' => 'appointments.*', 'label' => 'Appointments', 'icon' => 'calendar'])
            @include('layouts.nav-item', ['route' => 'jobs.*', 'label' => 'Job Orders', 'icon' => 'wrench', 'badge' => $openJobCount ?? 0])
            @include('layouts.nav-item', ['route' => 'customers.*', 'label' => 'Customers', 'icon' => 'users'])
            @include('layouts.nav-item', ['route' => 'vehicles.*', 'label' => 'Vehicles', 'icon' => 'truck'])
            @include('layouts.nav-item', ['route' => 'inventory.*', 'label' => 'Inventory', 'icon' => 'box', 'badge' => $lowStockCount ?? 0, 'badge_color' => 'amber'])
            @include('layouts.nav-item', ['route' => 'invoices.*', 'label' => 'Invoices', 'icon' => 'receipt'])
            @include('layouts.nav-item', ['route' => 'payroll.*', 'label' => 'Payroll', 'icon' => 'money'])
            @can('manage-staff')
            @include('layouts.nav-item', ['route' => 'staff.*', 'label' => 'Staff', 'icon' => 'staff'])
            @endcan
        </nav>

        {{-- User + collapse --}}
        <div class="border-t border-gray-800 p-3 space-y-1 shrink-0">
            <div :class="['flex items-center gap-3 px-2 py-2', !open ? 'lg:justify-center' : '']">
                <div class="w-8 h-8 rounded-full bg-[#FEE103] text-black flex items-center justify-center font-semibold text-sm shrink-0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div :class="open ? 'lg:opacity-100 lg:max-w-xs' : 'lg:opacity-0 lg:max-w-0 lg:overflow-hidden'"
                    class="min-w-0 transition-all duration-300 overflow-hidden">
                    <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
                </div>
            </div>
            <button @click="toggle()"
                class="hidden lg:flex w-full items-center justify-center gap-2 px-2 py-2 rounded-lg text-gray-500 hover:text-gray-300 hover:bg-gray-800 transition text-xs">
                <svg :class="open ? '' : 'rotate-180'" class="w-4 h-4 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                </svg>
                <span :class="open ? '' : 'lg:hidden'">Collapse</span>
            </button>
        </div>
    </aside>

    {{-- ── Main ── --}}
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        {{-- Topbar --}}
        <header class="h-16 bg-white border-b border-gray-100 flex items-center gap-3 px-4 sm:px-6 shrink-0 sticky top-0 z-10">
            <button @click="openMobile()" class="lg:hidden p-2 rounded-lg text-gray-400 hover:bg-gray-100 transition -ms-1">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <h1 class="text-sm sm:text-base font-semibold text-gray-900 truncate">
                {{ $title ?? 'Dashboard' }}
            </h1>

            <div class="flex-1"></div>

            {{-- Low stock alert --}}
            @if(($lowStockCount ?? 0) > 0)
            <a href="{{ route('inventory.index', ['low_stock' => 1]) }}"
                class="hidden sm:flex items-center gap-1.5 text-xs bg-amber-50 border border-amber-200 text-amber-700 px-3 py-1.5 rounded-full font-medium hover:bg-amber-100 transition">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                {{ $lowStockCount }} low stock
            </a>
            @endif

            {{-- User menu --}}
            <div x-data="dropdown()" class="relative">
                <button @click="toggle()" class="flex items-center gap-2 p-1 rounded-xl hover:bg-gray-100 transition">
                    <div class="w-8 h-8 rounded-full bg-[#FEE103] text-black flex items-center justify-center font-semibold text-sm">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <svg class="w-4 h-4 text-gray-400 hidden sm:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open" x-cloak @click.outside="close()"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    class="absolute end-0 top-12 w-52 bg-white rounded-2xl shadow-xl border border-gray-100 py-2 z-50">
                    <div class="px-4 py-2 border-b border-gray-100 mb-1">
                        <p class="text-sm font-semibold text-gray-900">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ auth()->user()->email }}</p>
                    </div>
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Profile
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex items-center gap-2.5 w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            Sign out
                        </button>
                    </form>
                </div>
            </div>
        </header>

        {{-- Flash messages --}}
        @if(session('success') || session('error'))
        <div class="px-4 sm:px-6 pt-4" x-data="flash()">
            <div x-show="show" x-transition>
                @if(session('success'))
                <div class="flash-success">
                    <svg class="w-4 h-4 shrink-0 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    {{ session('success') }}
                </div>
                @endif
                @if(session('error'))
                <div class="flash-error">
                    <svg class="w-4 h-4 shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    {{ session('error') }}
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Page content --}}
        <main class="flex-1 p-4 sm:p-6 overflow-y-auto overflow-x-hidden">
            {{ $slot }}
        </main>
    </div>
</div>

{{-- Delete confirm modal (global) --}}
<div id="delete-modal" x-data="confirmDelete('')" x-cloak>
    <div x-show="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6" @click.stop>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M12 3a9 9 0 100 18A9 9 0 0012 3z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Confirm Delete</h3>
                    <p class="text-sm text-gray-500">This action cannot be undone.</p>
                </div>
            </div>
            <div class="flex gap-3">
                <button @click="confirm()" class="btn-danger flex-1">Delete</button>
                <button @click="hide()" class="btn-secondary flex-1">Cancel</button>
            </div>
        </div>
    </div>
</div>

@stack('modals')
@stack('scripts')
</body>
</html>
