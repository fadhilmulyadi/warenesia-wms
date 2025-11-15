<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ trim($__env->yieldContent('title', 'Warenesia')) }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="font-sans antialiased bg-slate-100 text-slate-900">
    <div
        x-data="{ sidebarOpen: true, appearanceOpen: false, userMenuOpen: false }"
        class="min-h-screen flex">
        {{-- SIDEBAR --}}
        <aside
            class="flex flex-col bg-slate-900 text-slate-100 transition-all duration-300 ease-in-out relative"
            :class="sidebarOpen ? 'w-64' : 'w-20'">
            {{-- Logo + nama aplikasi --}}
            <div class="h-16 flex items-center px-4 border-b border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="h-9 w-9 rounded-xl bg-teal-400 flex items-center justify-center text-slate-900 font-black text-lg">
                        W
                    </div>
                    <div class="flex flex-col" x-show="sidebarOpen" x-transition>
                        <span class="font-semibold text-lg tracking-tight">Warenesia</span>
                        <span class="text-xs text-slate-400">Warehouse Management</span>
                    </div>
                </div>
            </div>

            {{-- Menu utama --}}
            <nav class="flex-1 px-2 py-4 space-y-1 overflow-y-auto text-sm">
                @php
                $role = auth()->user()->role ?? null;

                $dashboardRoute = match ($role) {
                'admin' => 'admin.dashboard',
                'manager' => 'manager.dashboard',
                'staff' => 'staff.dashboard',
                'supplier' => 'supplier.dashboard',
                default => 'dashboard',
                };

                function sidebar_classes($isActive = false) {
                $base = 'group flex items-center gap-3 px-3 py-2.5 rounded-xl transition';
                $inactive = 'text-slate-300 hover:bg-slate-800 hover:text-white';
                $active = 'bg-slate-800 text-white';
                return $base . ' ' . ($isActive ? $active : $inactive);
                }
                @endphp

                {{-- Dashboard --}}
                <a href="{{ route($dashboardRoute) }}"
                    class="{{ sidebar_classes(request()->routeIs($dashboardRoute)) }}">
                    <span class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-slate-800/60 group-hover:bg-slate-700">
                        <x-lucide-layout-dashboard class="h-4 w-4" />
                    </span>
                    <span class="font-medium" x-show="sidebarOpen" x-transition>Dashboard</span>
                </a>

                {{-- Purchases / Barang Masuk --}}
                <a href="#"
                    class="{{ sidebar_classes(false) }}">
                    <span class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-slate-800/60 group-hover:bg-slate-700">
                        <x-lucide-shopping-bag class="h-4 w-4" />
                    </span>
                    <span class="font-medium" x-show="sidebarOpen" x-transition>Purchases</span>
                </a>

                {{-- Sales / Barang Keluar --}}
                <a href="#"
                    class="{{ sidebar_classes(false) }}">
                    <span class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-slate-800/60 group-hover:bg-slate-700">
                        <x-lucide-shopping-cart class="h-4 w-4" />
                    </span>
                    <span class="font-medium" x-show="sidebarOpen" x-transition>Sales</span>
                </a>

                {{-- Inventory / Products --}}
                <a href="#"
                    class="{{ sidebar_classes(false) }}">
                    <span class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-slate-800/60 group-hover:bg-slate-700">
                        <x-lucide-box class="h-4 w-4" />
                    </span>
                    <span class="font-medium" x-show="sidebarOpen" x-transition>Inventory</span>
                </a>

                {{-- Reports --}}
                <a href="#"
                    class="{{ sidebar_classes(false) }}">
                    <span class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-slate-800/60 group-hover:bg-slate-700">
                        <x-lucide-bar-chart-3 class="h-4 w-4" />
                    </span>
                    <span class="font-medium" x-show="sidebarOpen" x-transition>Reports</span>
                </a>

                {{-- Settings --}}
                <a href="#"
                    class="{{ sidebar_classes(false) }}">
                    <span class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-slate-800/60 group-hover:bg-slate-700">
                        <x-lucide-settings class="h-4 w-4" />
                    </span>
                    <span class="font-medium" x-show="sidebarOpen" x-transition>Settings</span>
                </a>
            </nav>

            {{-- Tombol SUPPORT --}}
            <div class="px-3 pb-3 pt-2 mt-auto border-t border-slate-800">
                <button
                    type="button"
                    class="w-full inline-flex items-center justify-center gap-2 rounded-xl border border-teal-400/70 px-3 py-2 text-xs font-semibold text-teal-200 hover:bg-teal-400 hover:text-slate-900 transition">
                    <span class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-teal-300">
                        ?
                    </span>
                    <span x-show="sidebarOpen" x-transition>Support</span>
                </button>
            </div>

            {{-- Tombol collapse --}}
            <button
                type="button"
                @click="sidebarOpen = !sidebarOpen"
                class="absolute -right-3 top-20 h-7 w-7 rounded-full bg-slate-900 border border-slate-700 flex items-center justify-center text-slate-200 shadow-md hover:bg-slate-800">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    :class="sidebarOpen ? '' : 'rotate-180 transition-transform'">
                    <path d="M15 18l-6-6 6-6" />
                </svg>
            </button>
        </aside>

        {{-- MAIN AREA --}}
        <div class="flex-1 flex flex-col min-w-0">
            {{-- TOPBAR --}}
            <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 lg:px-6">
                <div class="flex items-center gap-3">
                    @yield('page-header')
                </div>

                <div class="flex items-center gap-4">
                    {{-- Palette / Appearance --}}
                    <button
                        type="button"
                        class="inline-flex items-center justify-center h-9 w-9 rounded-full border border-slate-300 hover:bg-slate-100"
                        @click="appearanceOpen = true">
                        <span class="sr-only">Appearance</span>
                        <x-lucide-palette class="h-4 w-4" />
                    </button>

                    {{-- User menu --}}
                    <div class="relative" x-data="{ open: false }">
                        <button
                            type="button"
                            @click="open = !open"
                            class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-2 py-1.5 hover:bg-slate-200">
                            <div class="h-8 w-8 rounded-full bg-teal-500 text-slate-900 flex items-center justify-center font-semibold text-sm">
                                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                            </div>
                            <div class="hidden md:flex flex-col items-start">
                                <span class="text-xs text-slate-500">Logged in as</span>
                                <span class="text-sm font-medium leading-none">
                                    {{ auth()->user()->name ?? 'User' }}
                                </span>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-500" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M6 9l6 6 6-6" />
                            </svg>
                        </button>

                        <div
                            x-cloak
                            x-show="open"
                            @click.outside="open = false"
                            x-transition
                            class="absolute right-0 mt-2 w-48 rounded-xl bg-white shadow-lg border border-slate-200 py-2 text-sm z-20">
                            <a href="{{ route('profile.edit') }}" class="block px-3 py-2 hover:bg-slate-50">
                                Profile
                            </a>
                            <div class="border-t my-1 border-slate-100"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-3 py-2 hover:bg-slate-50">
                                    Log out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            {{-- CONTENT --}}
            <main class="flex-1 px-4 py-6 lg:px-8">
                @yield('content')
            </main>
        </div>

        {{-- Modal appearance (dummy untuk sekarang) --}}
        <div
            x-cloak
            x-show="appearanceOpen"
            class="fixed inset-0 z-30 flex items-center justify-center bg-slate-900/40">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold">Appearance settings</h2>
                    <button @click="appearanceOpen = false" class="text-slate-400 hover:text-slate-600">
                        ✕
                    </button>
                </div>
                <p class="text-sm text-slate-500">
                    (Placeholder) Nanti di sini kamu bisa tambah pilihan warna tema dan gaya navigasi.
                </p>
                <div class="flex justify-end gap-2 pt-2">
                    <button
                        type="button"
                        class="px-3 py-1.5 rounded-lg text-sm border border-slate-200 hover:bg-slate-50"
                        @click="appearanceOpen = false">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>

</html>