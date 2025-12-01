<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ trim($__env->yieldContent('title', 'Warenesia')) }}</title>

    <link rel="icon" href="{{ asset('favicon.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

</head>

<body class="font-sans antialiased bg-slate-100 text-slate-900">
    <div x-data="{
        sidebarOpen: false,
        appearanceOpen: false,
        isDesktop: window.matchMedia('(min-width: 1024px)').matches,
        init() {
            const stored = JSON.parse(localStorage.getItem('warenesia_sidebar_open') ?? 'true');
            this.sidebarOpen = this.isDesktop ? stored : false;

            const mq = window.matchMedia('(min-width: 1024px)');
            const syncViewport = () => {
                this.isDesktop = mq.matches;
                if (!this.isDesktop) {
                    this.sidebarOpen = false;
                } else {
                    const saved = JSON.parse(localStorage.getItem('warenesia_sidebar_open') ?? 'true');
                    this.sidebarOpen = saved;
                }
            };

            mq.addEventListener('change', syncViewport);

            this.$watch('sidebarOpen', (value) => {
                if (this.isDesktop) {
                    localStorage.setItem('warenesia_sidebar_open', JSON.stringify(value));
                }
            });
        },
        toggleSidebar() {
            this.sidebarOpen = !this.sidebarOpen;
        }
    }"
        x-init="init()"
        class="h-screen flex items-stretch overflow-hidden">

        {{-- SIDEBAR --}}
        <aside
            class="fixed inset-y-0 left-0 z-30 flex h-full flex-col bg-slate-900 text-slate-100 transition-all duration-300 ease-in-out lg:relative"
            :class="[
                isDesktop ? (sidebarOpen ? 'w-64' : 'w-20') : 'w-72',
                isDesktop ? 'translate-x-0' : (sidebarOpen ? 'translate-x-0 shadow-2xl' : '-translate-x-full')
            ]">

            <div class="h-16 flex items-center px-4 border-b border-slate-800">
                <div class="flex flex-col"
                    x-show="sidebarOpen"
                    x-transition.opacity
                >
                    <span class="font-semibold text-lg tracking-tight">Warenesia</span>
                </div>
            </div>

            {{-- NAVIGATION --}}
            <nav class="flex-1 px-2 py-4 space-y-1 overflow-y-auto text-sm">
                @php
                    function sidebar_classes($isActive = false)
                    {
                        $base = 'group flex items-center gap-3 px-3 py-2.5 rounded-xl transition';
                        $inactive = 'text-slate-300 hover:bg-slate-800 hover:text-white';
                        $active = 'bg-slate-800 text-white';
                        return $base . ' ' . ($isActive ? $active : $inactive);
                    }
                @endphp

                {{-- Dashboard --}}
                <a href="{{ route('dashboard') }}" class="{{ sidebar_classes(request()->routeIs('dashboard')) }}">
                    <span
                        class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-slate-800/60 group-hover:bg-slate-700">
                        <x-lucide-layout-dashboard class="h-4 w-4" />
                    </span>
                    <span class="font-medium" x-show="sidebarOpen" x-transition>Dashboard</span>
                </a>

                @can('viewAny', \App\Models\User::class)
                    <a href="{{ route('users.index') }}" class="{{ sidebar_classes(request()->routeIs('users.*')) }}">
                        <span class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-slate-800/60 group-hover:bg-slate-700">
                            <x-lucide-users class="h-4 w-4" />
                        </span>
                        <span class="font-medium" x-show="sidebarOpen" x-transition>User Management</span>
                    </a>
                @endcan

                @php
                    $canViewIncoming = auth()->user()?->can('viewAny', \App\Models\IncomingTransaction::class);
                    $canViewOutgoing = auth()->user()?->can('viewAny', \App\Models\OutgoingTransaction::class);
                @endphp

                @if($canViewIncoming || $canViewOutgoing)
                    <a href="{{ route('transactions.index') }}"
                        class="{{ sidebar_classes(
                            request()->routeIs('transactions.*') ||
                            request()->routeIs('purchases.*') ||
                            request()->routeIs('sales.*')
                        ) }}">
                        <span class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-slate-800/60 group-hover:bg-slate-700">
                            <x-lucide-arrow-right-left class="h-4 w-4" />
                        </span>
                        <span class="font-medium" x-show="sidebarOpen" x-transition>Transaksi</span>
                    </a>
                @endif

                @can('viewAny', \App\Models\RestockOrder::class)
                    <a href="{{ route('restocks.index') }}" class="{{ sidebar_classes(request()->routeIs('restocks.*')) }}">
                        <span class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-slate-800/60 group-hover:bg-slate-700">
                            <x-lucide-repeat class="h-4 w-4" />
                        </span>
                        <span class="font-medium" x-show="sidebarOpen" x-transition>Restocks</span>
                    </a>
                @endcan

                @can('viewSupplierRestocks', \App\Models\RestockOrder::class)
                    <a href="{{ route('supplier.restocks.index') }}"
                        class="{{ sidebar_classes(request()->routeIs('supplier.restocks.*')) }}">
                        <span class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-slate-800/60 group-hover:bg-slate-700">
                            <x-lucide-truck class="h-4 w-4" />
                        </span>
                        <span class="font-medium" x-show="sidebarOpen" x-transition>Supplier Restocks</span>
                    </a>
                @endcan

                @can('viewAny', \App\Models\Product::class)
                    <a href="{{ route('products.index') }}" class="{{ sidebar_classes(request()->routeIs('products.*')) }}">
                        <span class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-slate-800/60 group-hover:bg-slate-700">
                            <x-lucide-box class="h-4 w-4" />
                        </span>
                        <span class="font-medium" x-show="sidebarOpen" x-transition>Inventory</span>
                    </a>
                @endcan

                @can('viewAny', \App\Models\Category::class)
                    <a href="{{ route('categories.index') }}" class="{{ sidebar_classes(request()->routeIs('categories.*')) }}">
                        <span class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-slate-800/60 group-hover:bg-slate-700">
                            <x-lucide-tags class="h-4 w-4" />
                        </span>
                        <span class="font-medium" x-show="sidebarOpen" x-transition>Categories</span>
                    </a>
                @endcan

                @can('viewAny', \App\Models\Unit::class)
                    <a href="{{ route('units.index') }}" class="{{ sidebar_classes(request()->routeIs('units.*')) }}">
                        <span class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-slate-800/60 group-hover:bg-slate-700">
                            <x-lucide-ruler class="h-4 w-4" />
                        </span>
                        <span class="font-medium" x-show="sidebarOpen" x-transition>Units</span>
                    </a>
                @endcan
            </nav>

            {{-- SUPPORT BUTTON --}}
            <div class="px-3 pb-3 pt-2 mt-auto border-t border-slate-800">
                <button type="button"
                    class="w-full inline-flex items-center justify-center gap-2 rounded-xl border border-teal-400/70 px-3 py-2 text-xs font-semibold text-teal-200 hover:bg-teal-400 hover:text-slate-900 transition">
                    <span class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-teal-300">
                        ?
                    </span>
                    <span x-show="sidebarOpen" x-transition>Support</span>
                </button>
            </div>

            {{-- COLLAPSE --}}
            <button type="button" @click="sidebarOpen = !sidebarOpen"
                class="hidden lg:flex absolute -right-3 top-20 h-7 w-7 rounded-full bg-slate-900 border border-slate-700 items-center justify-center text-slate-200 shadow-md hover:bg-slate-800">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 transition-transform duration-200"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    :class="sidebarOpen ? '' : 'rotate-180'">
                    <path d="M15 18l-6-6 6-6" />
                </svg>
            </button>
        </aside>

        {{-- OVERLAY MOBILE --}}
        <div x-cloak x-show="sidebarOpen && !isDesktop" x-transition.opacity
            class="fixed inset-0 z-20 bg-slate-900/60" @click="sidebarOpen = false"></div>

        {{-- MAIN AREA --}}
        <div class="flex-1 flex flex-col min-w-0 h-full">

            {{-- TOPBAR --}}
            <header
                class="min-h-[64px] bg-white border-b border-slate-200 flex flex-wrap items-center justify-between gap-3 px-3 sm:px-4 lg:px-6 shrink-0">

                <div class="flex items-center gap-3 flex-1 min-w-0">
                    <button type="button"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-100 lg:hidden"
                        @click="toggleSidebar()" aria-label="Toggle sidebar">
                        <x-lucide-menu class="h-5 w-5" />
                    </button>

                    {{-- PAGE HEADER DARI @section --}}
                    <div class="flex-1 min-w-0 flex items-center">
                        @hasSection('page-header')
                            <div class="w-full">
                                @yield('page-header')
                            </div>
                        @endif
                    </div>
                </div>

                {{-- USER MENU — REPLACED VERSION --}}
                <div class="flex items-center gap-3 flex-none">

                    <div class="relative flex-none" x-data="{ open: false }">
                        <button
                            type="button"
                            @click="open = !open"
                            class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-2 py-1.5 hover:bg-slate-200"
                        >
                            {{-- Avatar: always visible --}}
                            <div
                                class="h-9 w-9 rounded-full bg-teal-500 text-slate-900 flex items-center justify-center font-semibold text-sm"
                            >
                                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                            </div>

                            {{-- Text: visible on ≥ sm --}}
                            <div class="hidden sm:flex flex-col items-start min-w-0 max-w-[160px]">
                                <span class="text-xs text-slate-500 leading-none">Logged in as</span>
                                <span class="text-sm font-medium leading-none truncate">
                                    {{ auth()->user()->name ?? 'User' }}
                                </span>
                            </div>

                            {{-- Arrow icon: visible on ≥ sm --}}
                            <svg
                                class="hidden sm:block h-4 w-4 text-slate-500"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="M6 9l6 6 6-6" />
                            </svg>
                        </button>

                        {{-- Dropdown --}}
                        <div
                            x-cloak
                            x-show="open"
                            @click.outside="open = false"
                            x-transition
                            class="absolute right-0 mt-2 w-48 rounded-xl bg-white shadow-lg border border-slate-200 py-2 text-sm z-20"
                        >
                            <form method="POST" action="{{ route('logout') }}" @click.stop>
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
            <main class="flex-1 overflow-y-auto px-3 py-6 sm:px-4 lg:px-8">
                @yield('content')
            </main>

        </div>
    </div>

    <x-toast />
    @stack('scripts')

</body>
</html>
