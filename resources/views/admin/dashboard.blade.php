@extends('layouts.app')

@section('title', 'Overview Dashboard - Warenesia')

@section('page-header')
<div>
    <h1 class="text-xl md:text-2xl font-semibold text-slate-900">
        Overview dashboard
    </h1>
    <p class="text-xs md:text-sm text-slate-500">
        Ringkasan performa gudang dan aktivitas terbaru.
    </p>
</div>
@endsection

@section('content')
<div
    x-data="{
        manageDashboardOpen: false,
        widgets: {
            quickLinks:      { visible: true,  minimized: false, expanded: false, label: 'Quick links' },
            kpi:             { visible: true,  minimized: false, expanded: false, label: 'Key performance indicators' },
            sales:           { visible: true,  minimized: false, expanded: false, label: 'Sales overview' },
            purchases:       { visible: true,  minimized: false, expanded: false, label: 'Purchases overview' },
            productOverview: { visible: true,  minimized: false, expanded: false, label: 'Product overview' },
            stockHealth:     { visible: true,  minimized: false, expanded: false, label: 'Stock health' },
            reorder:         { visible: true,  minimized: false, expanded: false, label: 'Reorder' },
            reminders:       { visible: true,  minimized: false, expanded: false, label: 'Reminders' },
            customerOverview:{ visible: false, minimized: false, expanded: false, label: 'Customer overview (optional)' }
        },
        toggleMinimize(name) {
            this.widgets[name].minimized = ! this.widgets[name].minimized;
        },
        toggleExpand(name) {
            this.widgets[name].expanded = ! this.widgets[name].expanded;
        },
        closeWidget(name) {
            this.widgets[name].visible = false;
        },
        collapseAll() {
            Object.keys(this.widgets).forEach(key => this.widgets[key].minimized = true);
        },
        expandAll() {
            Object.keys(this.widgets).forEach(key => this.widgets[key].minimized = false);
        },
        resetLayout() {
            window.location.reload();
        },
        initSortable(el) {
            if (!window.Sortable) return;
            window.Sortable.create(el, {
                handle: '.widget-drag-handle'
            });
        }
    }"
    class="space-y-6">

    {{-- BAR ATAS: date range + dashboard actions + tombol Manage dashboard --}}
    <div
        x-data="{ rangeOpen: false, selectedRange: 'Last 30 days' }"
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div class="flex items-center gap-2">
            <span class="text-sm text-slate-600">Showing data for</span>
            <div class="relative">
                <button
                    type="button"
                    @click="rangeOpen = !rangeOpen"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    <x-lucide-calendar-range class="h-4 w-4" />
                    <span x-text="selectedRange"></span>
                    <x-lucide-chevron-down class="h-3 w-3 text-slate-400" />
                </button>

                {{-- Date range dropdown (UI saja, belum ke backend) --}}
                <div
                    x-cloak
                    x-show="rangeOpen"
                    @click.outside="rangeOpen = false"
                    class="absolute z-20 mt-2 w-60 rounded-xl border border-slate-200 bg-white shadow-lg text-sm">
                    <div class="px-3 py-2 border-b border-slate-100 font-semibold text-slate-700">
                        Date range
                    </div>
                    <div class="max-h-60 overflow-y-auto py-1">
                        @php
                        $presets = [
                        'Today',
                        'Yesterday',
                        'Last 7 days',
                        'Last 30 days',
                        'This Month',
                        'Last Month',
                        'This Quarter',
                        'Last Quarter',
                        'This Financial Year',
                        'Last Financial Year',
                        ];
                        @endphp

                        @foreach ($presets as $preset)
                        <button
                            type="button"
                            class="w-full text-left px-3 py-1.5 hover:bg-slate-50 text-slate-600"
                            @click="selectedRange = '{{ $preset }}'; rangeOpen = false">
                            {{ $preset }}
                        </button>
                        @endforeach
                    </div>
                    <div class="flex justify-end gap-2 px-3 py-2 border-t border-slate-100">
                        <button
                            type="button"
                            class="px-3 py-1.5 rounded-lg text-xs border border-slate-200 hover:bg-slate-50 text-slate-600"
                            @click="rangeOpen = false">
                            Cancel
                        </button>
                        <button
                            type="button"
                            class="px-3 py-1.5 rounded-lg text-xs bg-teal-500 text-white font-semibold hover:bg-teal-600"
                            @click="rangeOpen = false">
                            Apply
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 justify-between md:justify-end">
            {{-- Dashboard actions --}}
            <div class="flex items-center gap-2 text-xs text-slate-500">
                <button
                    type="button"
                    class="inline-flex items-center gap-1 hover:text-slate-800"
                    @click="collapseAll()">
                    <x-lucide-chevrons-down class="h-3 w-3" />
                    Collapse all widgets
                </button>
                <span>·</span>
                <button
                    type="button"
                    class="inline-flex items-center gap-1 hover:text-slate-800"
                    @click="expandAll()">
                    <x-lucide-chevrons-up class="h-3 w-3" />
                    Expand all widgets
                </button>
                <span>·</span>
                <button
                    type="button"
                    class="inline-flex items-center gap-1 hover:text-slate-800"
                    @click="resetLayout()">
                    <x-lucide-rotate-ccw class="h-3 w-3" />
                    Reset layout
                </button>
            </div>

            {{-- Tombol Manage dashboard --}}
            <button
                type="button"
                class="inline-flex items-center gap-2 rounded-xl bg-teal-500 px-3 py-2 text-sm font-semibold text-white hover:bg-teal-600"
                @click="manageDashboardOpen = true">
                <x-lucide-layout-dashboard class="h-4 w-4" />
                Manage dashboard
            </button>
        </div>
    </div>

    {{-- GRID WIDGET --}}
    <div
        class="grid grid-cols-1 md:grid-cols-2 gap-4 items-start"
        x-ref="widgetsGrid"
        x-init="initSortable($refs.widgetsGrid)">

        {{-- QUICK LINKS --}}
        <section
            x-show="widgets.quickLinks.visible"
            :class="widgets.quickLinks.expanded ? 'md:col-span-2' : ''"
            class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 flex flex-col transition-all duration-200">
            <div class="flex items-start justify-between gap-3 mb-3">
                <div class="flex items-start gap-2">
                    <button type="button" class="widget-drag-handle text-slate-300 hover:text-slate-500 mt-0.5">
                        <x-lucide-grip-vertical class="h-4 w-4" />
                    </button>
                    <div>
                        <h2 class="text-sm font-semibold text-slate-800">Quick links</h2>
                        <p class="text-xs text-slate-500">
                            Akses cepat ke master data gudang.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    <button
                        type="button"
                        class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"
                        @click="toggleExpand('quickLinks')">
                        <x-lucide-maximize-2 class="h-3 w-3" />
                    </button>
                    <button
                        type="button"
                        class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"
                        @click="toggleMinimize('quickLinks')">
                        <x-lucide-chevron-up
                            class="h-3 w-3 transition-transform duration-150"
                            x-bind:class="widgets.quickLinks.minimized ? 'rotate-180' : ''" />
                    </button>
                    <button
                        type="button"
                        class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"
                        @click="closeWidget('quickLinks')">
                        <x-lucide-x class="h-3 w-3" />
                    </button>
                </div>
            </div>

            <div
                x-show="!widgets.quickLinks.minimized"
                class="mt-1">
                <div class="grid grid-cols-2 md:grid-cols-5 gap-3 text-sm">
                    <a href="#"
                        class="group rounded-xl border border-slate-100 bg-slate-50 px-3 py-2 hover:bg-teal-50 hover:border-teal-100 flex flex-col gap-1">
                        <span class="text-xs text-slate-500">Products</span>
                        <span class="text-xl font-semibold text-slate-900 group-hover:text-teal-700">0</span>
                    </a>
                    <a href="#"
                        class="group rounded-xl border border-slate-100 bg-slate-50 px-3 py-2 hover:bg-teal-50 hover:border-teal-100 flex flex-col gap-1">
                        <span class="text-xs text-slate-500">Categories</span>
                        <span class="text-xl font-semibold text-slate-900 group-hover:text-teal-700">0</span>
                    </a>
                    <a href="#"
                        class="group rounded-xl border border-slate-100 bg-slate-50 px-3 py-2 hover:bg-teal-50 hover:border-teal-100 flex flex-col gap-1">
                        <span class="text-xs text-slate-500">Suppliers</span>
                        <span class="text-xl font-semibold text-slate-900 group-hover:text-teal-700">0</span>
                    </a>
                    <a href="#"
                        class="group rounded-xl border border-slate-100 bg-slate-50 px-3 py-2 hover:bg-teal-50 hover:border-teal-100 flex flex-col gap-1">
                        <span class="text-xs text-slate-500">Customers</span>
                        <span class="text-xl font-semibold text-slate-900 group-hover:text-teal-700">0</span>
                    </a>
                    <a href="#"
                        class="group rounded-xl border border-slate-100 bg-slate-50 px-3 py-2 hover:bg-teal-50 hover:border-teal-100 flex flex-col gap-1">
                        <span class="text-xs text-slate-500">Users</span>
                        <span class="text-xl font-semibold text-slate-900 group-hover:text-teal-700">0</span>
                    </a>
                </div>
            </div>
        </section>

        {{-- KPI --}}
        <section
            x-show="widgets.kpi.visible"
            :class="widgets.kpi.expanded ? 'md:col-span-2' : ''"
            class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 flex flex-col transition-all duration-200">
            <div class="flex items-start justify-between gap-3 mb-3">
                <div class="flex items-start gap-2">
                    <button type="button" class="widget-drag-handle text-slate-300 hover:text-slate-500 mt-0.5">
                        <x-lucide-grip-vertical class="h-4 w-4" />
                    </button>
                    <div>
                        <h2 class="text-sm font-semibold text-slate-800">Key performance indicators</h2>
                        <p class="text-xs text-slate-500">
                            Ringkasan revenue, inflow/outflow, dan status order.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    <button
                        type="button"
                        class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"
                        @click="toggleExpand('kpi')">
                        <x-lucide-maximize-2 class="h-3 w-3" />
                    </button>
                    <button
                        type="button"
                        class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"
                        @click="toggleMinimize('kpi')">
                        <x-lucide-chevron-up
                            class="h-3 w-3 transition-transform duration-150"
                            x-bind:class="widgets.kpi.minimized ? 'rotate-180' : ''" />
                    </button>
                    <button
                        type="button"
                        class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"
                        @click="closeWidget('kpi')">
                        <x-lucide-x class="h-3 w-3" />
                    </button>
                </div>
            </div>

            <div
                x-show="!widgets.kpi.minimized"
                class="mt-1">
                {{-- 7 KPI cards --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs mb-4">
                    @php
                    $kpiLabels = ['Revenue (Rp)', 'Net (Rp)', 'Pending orders', 'Due orders', 'Overdue orders', 'Inflow (Rp)', 'Outflow (Rp)'];
                    @endphp
                    @foreach ($kpiLabels as $label)
                    <div class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-2">
                        <p class="text-[11px] text-slate-500">{{ $label }}</p>
                        <p class="text-base sm:text-lg font-semibold text-slate-900">0</p>
                    </div>
                    @endforeach
                </div>

                {{-- Tabs + chart placeholder --}}
                <div class="border-t border-slate-100 pt-3">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex flex-wrap gap-2 text-xs">
                            <button class="px-3 py-1.5 rounded-full bg-slate-900 text-white border border-slate-900">
                                Revenue & Net
                            </button>
                            <button class="px-3 py-1.5 rounded-full border border-slate-200 text-slate-600">
                                Sales orders
                            </button>
                            <button class="px-3 py-1.5 rounded-full border border-slate-200 text-slate-600">
                                Purchases
                            </button>
                            <button class="px-3 py-1.5 rounded-full border border-slate-200 text-slate-600">
                                Stock movement
                            </button>
                        </div>
                        <div class="flex items-center gap-2 text-xs">
                            <button class="inline-flex items-center gap-1 px-2 py-1 rounded-lg border border-slate-200 text-slate-600">
                                Bars
                                <x-lucide-chevron-down class="h-3 w-3" />
                            </button>
                            <button class="text-teal-600 font-semibold hover:underline text-xs">
                                View report
                            </button>
                        </div>
                    </div>

                    {{-- Legend --}}
                    <div class="flex flex-wrap items-center gap-3 text-[11px] mb-2">
                        <div class="flex items-center gap-1">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            <span>Revenue</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="h-2 w-2 rounded-full bg-orange-500"></span>
                            <span>Purchases</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <span class="h-2 w-2 rounded-full bg-indigo-500"></span>
                            <span>Net</span>
                        </div>
                    </div>

                    {{-- Chart area placeholder --}}
                    <div class="h-40 rounded-xl border border-dashed border-slate-200 flex items-center justify-center text-[11px] text-slate-400">
                        Chart area (Revenue vs Purchases vs Net per bulan)
                    </div>

                    {{-- Status ringkasan + tabel sales (UI saja) --}}
                    <div class="mt-3 space-y-2 text-xs">
                        <div class="flex flex-wrap items-center gap-2">
                            @php
                            $salesStatus = ['Draft', 'Pending approval', 'Awaiting shipment', 'Overdue', 'Completed'];
                            @endphp
                            @foreach ($salesStatus as $status)
                            <div class="inline-flex items-center gap-1 px-2 py-1 rounded-full border border-slate-200 bg-slate-50">
                                <span class="h-4 w-4 rounded-full bg-slate-200 text-[10px] flex items-center justify-center">0</span>
                                <span>{{ $status }}</span>
                            </div>
                            @endforeach
                        </div>

                        <div class="mt-2 border border-slate-100 rounded-xl overflow-hidden">
                            <table class="min-w-full text-xs">
                                <thead class="bg-slate-50 text-slate-500">
                                    <tr>
                                        <th class="px-3 py-2 text-left">ORDER #</th>
                                        <th class="px-3 py-2 text-left">ORDER DATE</th>
                                        <th class="px-3 py-2 text-left">DUE DATE</th>
                                        <th class="px-3 py-2 text-left">CUSTOMER</th>
                                        <th class="px-3 py-2 text-right">TOTAL (Rp)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="5" class="px-3 py-4 text-center text-slate-400">
                                            Belum ada data penjualan untuk range ini.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- SALES OVERVIEW --}}
        <section
            x-show="widgets.sales.visible"
            :class="widgets.sales.expanded ? 'md:col-span-2' : ''"
            class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 flex flex-col transition-all duration-200">
            <div class="flex items-start justify-between gap-3 mb-3">
                <div class="flex items-start gap-2">
                    <button type="button" class="widget-drag-handle text-slate-300 hover:text-slate-500 mt-0.5">
                        <x-lucide-grip-vertical class="h-4 w-4" />
                    </button>
                    <div>
                        <h2 class="text-sm font-semibold text-slate-800">Sales overview</h2>
                        <p class="text-xs text-slate-500">
                            Ringkasan barang keluar ke customer.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    <button
                        type="button"
                        class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"
                        @click="toggleExpand('sales')">
                        <x-lucide-maximize-2 class="h-3 w-3" />
                    </button>
                    <button
                        type="button"
                        class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"
                        @click="toggleMinimize('sales')">
                        <x-lucide-chevron-up
                            class="h-3 w-3 transition-transform duration-150"
                            x-bind:class="widgets.sales.minimized ? 'rotate-180' : ''" />
                    </button>
                    <button
                        type="button"
                        class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"
                        @click="closeWidget('sales')">
                        <x-lucide-x class="h-3 w-3" />
                    </button>
                </div>
            </div>

            <div
                x-show="!widgets.sales.minimized"
                class="mt-1 space-y-2 text-xs">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Total orders (periode)</span>
                    <span class="font-semibold text-slate-800">0</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Pending approval</span>
                    <span class="font-semibold text-amber-600">0</span>
                </div>
                <button class="mt-2 inline-flex items-center gap-1 text-xs text-teal-600 font-semibold hover:underline">
                    <x-lucide-external-link class="h-3 w-3" />
                    Go to sales list
                </button>
            </div>
        </section>

        {{-- PRODUCT OVERVIEW --}}
        <section
            x-show="widgets.productOverview.visible"
            :class="widgets.productOverview.expanded ? 'md:col-span-2' : ''"
            class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 flex flex-col transition-all duration-200">
            <div class="flex items-start justify-between gap-3 mb-3">
                <div class="flex items-start gap-2">
                    <button type="button" class="widget-drag-handle text-slate-300 hover:text-slate-500 mt-0.5">
                        <x-lucide-grip-vertical class="h-4 w-4" />
                    </button>
                    <div>
                        <h2 class="text-sm font-semibold text-slate-800">Product overview</h2>
                        <p class="text-xs text-slate-500">
                            Top selling, low stock, dan performa produk.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    <button
                        type="button"
                        class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"
                        @click="toggleExpand('productOverview')">
                        <x-lucide-maximize-2 class="h-3 w-3" />
                    </button>
                    <button
                        type="button"
                        class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"
                        @click="toggleMinimize('productOverview')">
                        <x-lucide-chevron-up
                            class="h-3 w-3 transition-transform duration-150"
                            x-bind:class="widgets.productOverview.minimized ? 'rotate-180' : ''" />
                    </button>
                    <button
                        type="button"
                        class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"
                        @click="closeWidget('productOverview')">
                        <x-lucide-x class="h-3 w-3" />
                    </button>
                </div>
            </div>

            <div
                x-show="!widgets.productOverview.minimized"
                class="mt-1">
                <div class="flex flex-wrap gap-2 text-xs mb-3">
                    <button class="px-3 py-1.5 rounded-full bg-slate-900 text-white border border-slate-900">
                        Top selling
                    </button>
                    <button class="px-3 py-1.5 rounded-full border border-slate-200 text-slate-600">
                        Low stock
                    </button>
                    <button class="px-3 py-1.5 rounded-full border border-slate-200 text-slate-600">
                        Most profitable
                    </button>
                    <button class="px-3 py-1.5 rounded-full border border-slate-200 text-slate-600">
                        Slow moving
                    </button>
                </div>

                <div class="border border-slate-100 rounded-xl overflow-hidden">
                    <table class="min-w-full text-xs">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th class="px-3 py-2 text-left">PRODUCT</th>
                                <th class="px-3 py-2 text-right">PRICE</th>
                                <th class="px-3 py-2 text-right">SOLD</th>
                                <th class="px-3 py-2 text-right">SALES (Rp)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="4" class="px-3 py-4 text-center text-slate-400">
                                    Belum ada data produk untuk ditampilkan.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <button class="mt-2 inline-flex items-center gap-1 text-xs text-teal-600 font-semibold hover:underline">
                    <x-lucide-plus class="h-3 w-3" />
                    Go to product list
                </button>
            </div>
        </section>

        {{-- PURCHASES / RESTOCK OVERVIEW --}}
        <section
            x-show="widgets.purchases.visible"
            :class="widgets.purchases.expanded ? 'md:col-span-2' : ''"
            class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 flex flex-col transition-all duration-200">
            <div class="flex items-start justify-between gap-3 mb-3">
                <div class="flex items-start gap-2">
                    <button type="button" class="widget-drag-handle text-slate-300 hover:text-slate-500 mt-0.5">
                        <x-lucide-grip-vertical class="h-4 w-4" />
                    </button>
                    <div>
                        <h2 class="text-sm font-semibold text-slate-800">Purchases overview</h2>
                        <p class="text-xs text-slate-500">
                            Ringkasan restock order dan status penerimaan barang.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    <button
                        type="button"
                        class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"
                        @click="toggleExpand('purchases')">
                        <x-lucide-maximize-2 class="h-3 w-3" />
                    </button>
                    <button
                        type="button"
                        class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"
                        @click="toggleMinimize('purchases')">
                        <x-lucide-chevron-up
                            class="h-3 w-3 transition-transform duration-150"
                            x-bind:class="widgets.purchases.minimized ? 'rotate-180' : ''" />
                    </button>
                    <button
                        type="button"
                        class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"
                        @click="closeWidget('purchases')">
                        <x-lucide-x class="h-3 w-3" />
                    </button>
                </div>
            </div>

            <div
                x-show="!widgets.purchases.minimized"
                class="mt-1">
                <div class="flex flex-wrap gap-2 text-xs mb-3">
                    <button class="px-3 py-1.5 rounded-full bg-slate-900 text-white border border-slate-900">
                        Draft
                    </button>
                    <button class="px-3 py-1.5 rounded-full border border-slate-200 text-slate-600">
                        Awaiting approval
                    </button>
                    <button class="px-3 py-1.5 rounded-full border border-slate-200 text-slate-600">
                        Awaiting delivery
                    </button>
                    <button class="px-3 py-1.5 rounded-full border border-slate-200 text-slate-600">
                        Partially received
                    </button>
                    <button class="px-3 py-1.5 rounded-full border border-slate-200 text-slate-600">
                        Overdue
                    </button>
                </div>

                <div class="border border-slate-100 rounded-xl overflow-hidden">
                    <table class="min-w-full text-xs">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th class="px-3 py-2 text-left">PO #</th>
                                <th class="px-3 py-2 text-left">ORDER DATE</th>
                                <th class="px-3 py-2 text-left">EXPECTED DELIVERY</th>
                                <th class="px-3 py-2 text-left">SUPPLIER</th>
                                <th class="px-3 py-2 text-right">ORDER AMOUNT</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5" class="px-3 py-4 text-center text-slate-400">
                                    Belum ada purchase order untuk range ini.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <button class="mt-2 inline-flex items-center gap-1 text-xs text-teal-600 font-semibold hover:underline">
                    <x-lucide-plus class="h-3 w-3" />
                    Go to purchases list
                </button>
            </div>
        </section>

        {{-- STOCK HEALTH --}}
        <section
            x-show="widgets.stockHealth.visible"
            :class="widgets.stockHealth.expanded ? 'md:col-span-2' : ''"
            class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 flex flex-col transition-all duration-200">
            <div class="flex items-start justify-between gap-3 mb-3">
                <div class="flex items-start gap-2">
                    <button type="button" class="widget-drag-handle text-slate-300 hover:text-slate-500 mt-0.5">
                        <x-lucide-grip-vertical class="h-4 w-4" />
                    </button>
                    <div>
                        <h2 class="text-sm font-semibold text-slate-800">Stock health</h2>
                        <p class="text-xs text-slate-500">
                            Snapshot kesehatan stok gudang.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    <button
                        type="button"
                        class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"
                        @click="toggleExpand('stockHealth')">
                        <x-lucide-maximize-2 class="h-3 w-3" />
                    </button>
                    <button
                        type="button"
                        class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"
                        @click="toggleMinimize('stockHealth')">
                        <x-lucide-chevron-up
                            class="h-3 w-3 transition-transform duration-150"
                            x-bind:class="widgets.stockHealth.minimized ? 'rotate-180' : ''" />
                    </button>
                    <button
                        type="button"
                        class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"
                        @click="closeWidget('stockHealth')">
                        <x-lucide-x class="h-3 w-3" />
                    </button>
                </div>
            </div>

            <div
                x-show="!widgets.stockHealth.minimized"
                class="mt-1 space-y-2 text-xs">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Total SKUs</span>
                    <span class="font-semibold text-slate-800">0</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Low stock items</span>
                    <span class="font-semibold text-amber-600">0</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Out of stock</span>
                    <span class="font-semibold text-red-600">0</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Total on-hand quantity</span>
                    <span class="font-semibold text-slate-800">0</span>
                </div>
            </div>
        </section>

        {{-- REORDER --}}
        <section
            x-show="widgets.reorder.visible"
            :class="widgets.reorder.expanded ? 'md:col-span-2' : ''"
            class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 flex flex-col transition-all duration-200">
            <div class="flex items-start justify-between gap-3 mb-3">
                <div class="flex items-start gap-2">
                    <button type="button" class="widget-drag-handle text-slate-300 hover:text-slate-500 mt-0.5">
                        <x-lucide-grip-vertical class="h-4 w-4" />
                    </button>
                    <div>
                        <h2 class="text-sm font-semibold text-slate-800">Reorder</h2>
                        <p class="text-xs text-slate-500">
                            Produk dengan stok di bawah minimum.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    <button
                        type="button"
                        class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"
                        @click="toggleExpand('reorder')">
                        <x-lucide-maximize-2 class="h-3 w-3" />
                    </button>
                    <button
                        type="button"
                        class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"
                        @click="toggleMinimize('reorder')">
                        <x-lucide-chevron-up
                            class="h-3 w-3 transition-transform duration-150"
                            x-bind:class="widgets.reorder.minimized ? 'rotate-180' : ''" />
                    </button>
                    <button
                        type="button"
                        class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"
                        @click="closeWidget('reorder')">
                        <x-lucide-x class="h-3 w-3" />
                    </button>
                </div>
            </div>

            <div
                x-show="!widgets.reorder.minimized"
                class="mt-1">
                <ul class="space-y-1 text-xs">
                    <li class="text-slate-400">
                        Belum ada produk yang perlu direstock.
                    </li>
                </ul>
            </div>
        </section>

        {{-- REMINDERS --}}
        <section
            x-show="widgets.reminders.visible"
            :class="widgets.reminders.expanded ? 'md:col-span-2' : ''"
            class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 flex flex-col transition-all duration-200">
            <div class="flex items-start justify-between gap-3 mb-3">
                <div class="flex items-start gap-2">
                    <button type="button" class="widget-drag-handle text-slate-300 hover:text-slate-500 mt-0.5">
                        <x-lucide-grip-vertical class="h-4 w-4" />
                    </button>
                    <div>
                        <h2 class="text-sm font-semibold text-slate-800">Reminders</h2>
                        <p class="text-xs text-slate-500">
                            Hal penting yang perlu diperhatikan Admin / Manager.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    <button
                        type="button"
                        class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"
                        @click="toggleExpand('reminders')">
                        <x-lucide-maximize-2 class="h-3 w-3" />
                    </button>
                    <button
                        type="button"
                        class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"
                        @click="toggleMinimize('reminders')">
                        <x-lucide-chevron-up
                            class="h-3 w-3 transition-transform duration-150"
                            x-bind:class="widgets.reminders.minimized ? 'rotate-180' : ''" />
                    </button>
                    <button
                        type="button"
                        class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"
                        @click="closeWidget('reminders')">
                        <x-lucide-x class="h-3 w-3" />
                    </button>
                </div>
            </div>

            <div
                x-show="!widgets.reminders.minimized"
                class="mt-1">
                <ul class="space-y-2 text-xs">
                    <li class="flex items-start gap-2">
                        <x-lucide-alert-circle class="h-4 w-4 mt-0.5 text-amber-600" />
                        <span>Tidak ada reminder aktif untuk saat ini.</span>
                    </li>
                </ul>
            </div>
        </section>

        {{-- CUSTOMER OVERVIEW (optional) --}}
        <section
            x-show="widgets.customerOverview.visible"
            :class="widgets.customerOverview.expanded ? 'md:col-span-2' : ''"
            class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 flex flex-col transition-all duration-200">
            <div class="flex items-start justify-between gap-3 mb-3">
                <div class="flex items-start gap-2">
                    <button type="button" class="widget-drag-handle text-slate-300 hover:text-slate-500 mt-0.5">
                        <x-lucide-grip-vertical class="h-4 w-4" />
                    </button>
                    <div>
                        <h2 class="text-sm font-semibold text-slate-800">Customer overview</h2>
                        <p class="text-xs text-slate-500">
                            Analisis customer berdasarkan penjualan.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    <button
                        type="button"
                        class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"
                        @click="toggleExpand('customerOverview')">
                        <x-lucide-maximize-2 class="h-3 w-3" />
                    </button>
                    <button
                        type="button"
                        class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"
                        @click="toggleMinimize('customerOverview')">
                        <x-lucide-chevron-up
                            class="h-3 w-3 transition-transform duration-150"
                            x-bind:class="widgets.customerOverview.minimized ? 'rotate-180' : ''" />
                    </button>
                    <button
                        type="button"
                        class="p-1 rounded-lg hover:bg-slate-100 text-slate-400"
                        @click="closeWidget('customerOverview')">
                        <x-lucide-x class="h-3 w-3" />
                    </button>
                </div>
            </div>

            <div
                x-show="!widgets.customerOverview.minimized"
                class="mt-1">
                <p class="text-xs text-slate-400">
                    Widget ini optional, akan diisi jika modul customer sudah tersedia.
                </p>
            </div>
        </section>
    </div>

    {{-- MANAGE DASHBOARD MODAL --}}
    <div
        x-cloak
        x-show="manageDashboardOpen"
        class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/40">
        <div
            @click.outside="manageDashboardOpen = false"
            class="bg-white rounded-2xl shadow-xl w-full max-w-md p-5 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-900">Manage dashboard</h2>
                <button
                    type="button"
                    class="text-slate-400 hover:text-slate-600"
                    @click="manageDashboardOpen = false">
                    ✕
                </button>
            </div>

            <div class="space-y-3 text-sm">
                <p class="text-xs text-slate-500">
                    Pilih widget yang ingin ditampilkan di dashboard Admin Warenesia.
                </p>

                <div class="space-y-2">
                    <p class="text-xs font-semibold text-slate-600 uppercase">
                        Select widgets to display
                    </p>

                    <template x-for="(config, key) in widgets" :key="key">
                        <label class="flex items-center gap-2 text-xs text-slate-700">
                            <input
                                type="checkbox"
                                class="rounded border-slate-300"
                                x-model="config.visible">
                            <span x-text="config.label"></span>
                        </label>
                    </template>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button
                    type="button"
                    class="px-3 py-1.5 rounded-lg text-xs border border-slate-200 hover:bg-slate-50"
                    @click="manageDashboardOpen = false">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
@endsection