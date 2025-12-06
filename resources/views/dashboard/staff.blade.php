@extends('layouts.app')

@section('title', 'Dashboard Staff')

@section('page-header')
    <x-page-header title="Dashboard Staff"
        description="Akses cepat transaksi gudang dan aktivitas harian" />
@endsection

@php
    $supplierOptions = $suppliers?->pluck('name', 'id')->toArray() ?? [];

    $productOptions = $products?->mapWithKeys(
        fn($p) => [$p->id => "{$p->name} ({$p->sku}) - Stok: {$p->current_stock}"]
    )->toArray() ?? [];

    $productStocks = $products?->mapWithKeys(
        fn($p) => [$p->id => (int) $p->current_stock]
    )->toArray() ?? [];

    $todayList = $todayTransactions ?? [];
@endphp

@section('content')
    {{-- DASHBOARD INIT --}}
    <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-6" x-data="staffDashboard({
                                                    skuMap: @js($productSkuMap), 
                                                    products: @js($productOptions) 
                                                 })">

        {{-- LEFT SIDE --}}
        <div class="space-y-6 lg:col-span-3">

            {{-- SECTION: PO Ready --}}
            <x-dashboard.card padding="p-0">
                <div class="px-4 pt-4 mb-4 space-y-1">
                    <h3 class="text-sm font-semibold text-slate-900">PO Siap Diterima</h3>
                    <p class="text-sm text-slate-500">Restock Order yang sudah sampai di lokasi.</p>
                </div>

                @if(isset($poReadyToReceive) && count($poReadyToReceive) > 0)
                    <div
                        class="max-h-[350px] overflow-y-auto scrollbar-thin scrollbar-thumb-slate-200 scrollbar-track-transparent border-t border-slate-100">
                        <div class="divide-y divide-slate-100">
                            @foreach($poReadyToReceive as $po)
                                <div class="p-4 flex items-center justify-between hover:bg-slate-50 transition group">

                                    {{-- PO Info --}}
                                    <div class="min-w-0 flex-1 pr-4">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="font-bold text-slate-800 text-sm tracking-tight">
                                                {{ $po->po_number }}
                                            </span>
                                            <span
                                                class="text-[10px] px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 font-semibold border border-blue-100">
                                                {{ $po->items->count() }} Items
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-1.5 text-xs text-slate-500">
                                            <x-lucide-truck class="w-3 h-3 text-slate-400" />
                                            <span class="truncate">{{ $po->supplier->name ?? 'Unknown Supplier' }}</span>
                                        </div>
                                    </div>

                                    {{-- ACTION --}}
                                    <x-action-button href="{{ route('purchases.create', ['restock_order_id' => $po->id]) }}"
                                        variant="primary" size="sm" icon="arrow-right">
                                        Proses
                                    </x-action-button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    {{-- EMPTY STATE --}}
                    <div class="px-4 pb-6 border-t border-slate-100">
                        <x-empty-state
                            title="Semua beres!"
                            description="Tidak ada PO yang perlu diterima saat ini."
                            icon="check-circle"
                        />
                    </div>
                @endif
            </x-dashboard.card>

            {{-- SECTION: Quick Action --}}
            <div class="space-y-4">
                {{-- SCANNER ENTRY --}}
                <div
                    class="bg-teal-50 border border-teal-100 rounded-xl p-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-white rounded-lg border border-teal-100 shadow-sm text-teal-600">
                            <x-lucide-qr-code class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-teal-900">Mode Scan Cepat</h3>
                            <p class="text-xs text-teal-700 mt-0.5">Gunakan kamera untuk input otomatis.</p>
                        </div>
                    </div>

                    <x-action-button type="button" variant="primary" icon="scan-line"
                        x-on:click="openScanModal('incoming')">
                        Mulai Scan
                    </x-action-button>
                </div>

                {{-- FORM: Quick Entry --}}
                <x-dashboard.quick-entry :supplierOptions="$supplierOptions" :productOptions="$productOptions"
                    :productStocks="$productStocks" :prefilledType="$prefilledType"
                    :prefilledSupplierId="$prefilledSupplierId" :prefilledProductId="$prefilledProductId"
                    :prefilledCustomerName="$prefilledCustomerName" :prefilledQuantity="$prefilledQuantity" />
            </div>
        </div>

        {{-- RIGHT SIDE --}}
        <div class="space-y-6 lg:col-span-3">
            {{-- SECTION: Today Transactions --}}
            <x-dashboard.card title="Transaksi Hari Ini" subtitle="Daftar transaksi yang kamu buat hari ini." padding="p-4">
                @if(count($todayList) === 0)
                    <x-empty-state
                        title="Belum ada transaksi hari ini"
                        description="Transaksi yang kamu buat hari ini akan muncul di sini."
                        icon="clock-3"
                    />
                @else
                    <x-dashboard.list :items="$todayList" />
                @endif
            </x-dashboard.card>
        </div>

        {{-- MODAL --}}
        <x-dashboard.scan-modal />

    </div>

@endsection
