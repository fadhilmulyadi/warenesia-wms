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
{{-- STATE UNTUK WIDGET & MANAGE DASHBOARD --}}
<div
    x-data="{
            manageDashboardOpen: false,
            widgets: {
                quickLinks: true,
                kpi: true,
                sales: true,
                purchases: true,
                productOverview: true,
                stockHealth: true,
                reorder: true,
                reminders: true,
                customerOverview: false, // optional / prio 2
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
                    x-transition
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
            {{-- Dashboard actions (UI dulu, nanti kita isi fungsinya) --}}
            <div class="flex items-center gap-2 text-xs text-slate-500">
                <button type="button" class="inline-flex items-center gap-1 hover:text-slate-800">
                    <x-lucide-chevrons-down class="h-3 w-3" />
                    Collapse all widgets
                </button>
                <span>·</span>
                <button type="button" class="inline-flex items-center gap-1 hover:text-slate-800">
                    <x-lucide-chevrons-up class="h-3 w-3" />
                    Expand all widgets
                </button>
                <span>·</span>
                <button type="button" class="inline-flex items-center gap-1 hover:text-slate-800">
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

    {{-- WIDGET: QUICK LINKS --}}
    <template x-if="widgets.quickLinks">
        <section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-slate-800">Quick links</h2>
                <p class="text-xs text-slate-500">Akses cepat ke master data gudang.</p>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3 text-sm">
                {{-- NOTE: angka masih placeholder, nanti diisi dari controller --}}
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
        </section>
    </template>

    {{-- ROW 1: KPI + SALES WIDGET --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- WIDGET: KPI --}}
        <template x-if="widgets.kpi">
            <section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4">
                <div class="flex items-start justify-between gap-3 mb-4">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-800">Key performance indicators</h2>
                        <p class="text-xs text-slate-500">
                            Ringkasan revenue, inflow/outflow, dan status order.
                        </p>
                    </div>
                </div>

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
                            <div class="flex flex-col sm:flex-row items-center justify-between gap-2 px-3 py-2 border-t border-slate-100 text-[11px] text-slate-500">
                                <span>Showing 0–0 of 0</span>
                                <div class="flex items-center gap-2">
                                    <span>Show:</span>
                                    <select class="border border-slate-200 rounded-lg px-2 py-1 text-xs">
                                        <option>10</option>
                                        <option>25</option>
                                        <option>50</option>
                                    </select>
                                    <span>| Go to page</span>
                                    <input type="number" class="w-12 border border-slate-200 rounded-lg px-2 py-1 text-xs" value="1" min="1">
                                    <button class="px-2 py-1 border border-slate-200 rounded-lg text-xs">Go</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </template>

        {{-- WIDGET: SALES OVERVIEW (versi list ringkas) --}}
        <template x-if="widgets.sales">
            <section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-800">Sales overview</h2>
                        <p class="text-xs text-slate-500">
                            Ringkasan barang keluar ke customer.
                        </p>
                    </div>
                    <button class="text-xs text-teal-600 font-semibold hover:underline">
                        Go to sales list
                    </button>
                </div>

                <div class="space-y-2 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Total orders (periode)</span>
                        <span class="font-semibold text-slate-800">0</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Pending approval</span>
                        <span class="font-semibold text-amber-600">0</span>
                    </div>
                </div>
            </section>
        </template>
    </div>

    {{-- ROW 2: PRODUCT OVERVIEW + PURCHASES --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- PRODUCT OVERVIEW --}}
        <template x-if="widgets.productOverview">
            <section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-800">Product overview</h2>
                        <p class="text-xs text-slate-500">
                            Top selling, low stock, dan performa produk.
                        </p>
                    </div>
                    <button class="inline-flex items-center gap-1 text-xs text-teal-600 font-semibold hover:underline">
                        <x-lucide-plus class="h-3 w-3" />
                        Go to product list
                    </button>
                </div>

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
            </section>
        </template>

        {{-- PURCHASES / RESTOCK OVERVIEW --}}
        <template x-if="widgets.purchases">
            <section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-800">Purchases overview</h2>
                        <p class="text-xs text-slate-500">
                            Ringkasan restock order dan status penerimaan barang.
                        </p>
                    </div>
                    <button class="inline-flex items-center gap-1 text-xs text-teal-600 font-semibold hover:underline">
                        <x-lucide-plus class="h-3 w-3" />
                        Go to purchases list
                    </button>
                </div>

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
            </section>
        </template>
    </div>

    {{-- ROW 3: STOCK HEALTH + REORDER + REMINDERS --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- STOCK HEALTH --}}
        <template x-if="widgets.stockHealth">
            <section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-800">Stock health</h2>
                        <p class="text-xs text-slate-500">
                            Snapshot kesehatan stok gudang.
                        </p>
                    </div>
                </div>
                <div class="space-y-2 text-xs">
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
        </template>

        {{-- REORDER / LOW STOCK LIST --}}
        <template x-if="widgets.reorder">
            <section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-800">Reorder</h2>
                        <p class="text-xs text-slate-500">
                            Produk dengan stok di bawah minimum.
                        </p>
                    </div>
                    <button class="inline-flex items-center gap-1 text-xs text-teal-600 font-semibold hover:underline">
                        <x-lucide-plus class="h-3 w-3" />
                        Create restock order
                    </button>
                </div>

                <ul class="space-y-1 text-xs">
                    <li class="text-slate-400">
                        Belum ada produk yang perlu direstock.
                    </li>
                </ul>
            </section>
        </template>

        {{-- REMINDERS --}}
        <template x-if="widgets.reminders">
            <section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-800">Reminders</h2>
                        <p class="text-xs text-slate-500">
                            Hal penting yang perlu diperhatikan Admin / Manager.
                        </p>
                    </div>
                </div>
                <ul class="space-y-2 text-xs">
                    <li class="flex items-start gap-2">
                        <x-lucide-alert-circle class="h-4 w-4 mt-0.5 text-amber-600" />
                        <span>Tidak ada reminder aktif untuk saat ini.</span>
                    </li>
                </ul>
            </section>
        </template>
    </div>

    {{-- (Optional) CUSTOMER OVERVIEW – default hidden --}}
    <template x-if="widgets.customerOverview">
        <section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4">
            <div class="flex items-start justify-between gap-3 mb-3">
                <div>
                    <h2 class="text-sm font-semibold text-slate-800">Customer overview</h2>
                    <p class="text-xs text-slate-500">
                        Analisis customer berdasarkan penjualan.
                    </p>
                </div>
                <button class="inline-flex items-center gap-1 text-xs text-teal-600 font-semibold hover:underline">
                    <x-lucide-plus class="h-3 w-3" />
                    Go to customer list
                </button>
            </div>
            <p class="text-xs text-slate-400">
                Widget ini optional, akan diisi jika modul customer sudah tersedia.
            </p>
        </section>
    </template>

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

                    {{-- Checkbox per widget --}}
                    <label class="flex items-center gap-2 text-xs text-slate-700">
                        <input type="checkbox" class="rounded border-slate-300"
                            x-model="widgets.quickLinks">
                        <span>Quick links</span>
                    </label>

                    <label class="flex items-center gap-2 text-xs text-slate-700">
                        <input type="checkbox" class="rounded border-slate-300"
                            x-model="widgets.kpi">
                        <span>Key performance indicators</span>
                    </label>

                    <label class="flex items-center gap-2 text-xs text-slate-700">
                        <input type="checkbox" class="rounded border-slate-300"
                            x-model="widgets.sales">
                        <span>Sales overview</span>
                    </label>

                    <label class="flex items-center gap-2 text-xs text-slate-700">
                        <input type="checkbox" class="rounded border-slate-300"
                            x-model="widgets.purchases">
                        <span>Purchases overview</span>
                    </label>

                    <label class="flex items-center gap-2 text-xs text-slate-700">
                        <input type="checkbox" class="rounded border-slate-300"
                            x-model="widgets.productOverview">
                        <span>Product overview</span>
                    </label>

                    <label class="flex items-center gap-2 text-xs text-slate-700">
                        <input type="checkbox" class="rounded border-slate-300"
                            x-model="widgets.stockHealth">
                        <span>Stock health</span>
                    </label>

                    <label class="flex items-center gap-2 text-xs text-slate-700">
                        <input type="checkbox" class="rounded border-slate-300"
                            x-model="widgets.reorder">
                        <span>Reorder</span>
                    </label>

                    <label class="flex items-center gap-2 text-xs text-slate-700">
                        <input type="checkbox" class="rounded border-slate-300"
                            x-model="widgets.reminders">
                        <span>Reminders</span>
                    </label>

                    <label class="flex items-center gap-2 text-xs text-slate-700">
                        <input type="checkbox" class="rounded border-slate-300"
                            x-model="widgets.customerOverview">
                        <span>Customer overview (optional)</span>
                    </label>
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