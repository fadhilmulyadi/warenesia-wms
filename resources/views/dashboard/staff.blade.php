@extends('layouts.app')

@section('title', 'Dashboard Staff Gudang')



@section('page-header')
    <x-page-header title="Dashboard Staff Gudang"
        description="Input cepat transaksi gudang dan pantau aktivitas hari ini." />
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
    {{-- Perhatikan x-data di sini memanggil fungsi staffDashboard yang kita buat di bawah --}}
    <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-6" x-data="staffDashboard({
                                skuMap: @js($productSkuMap), 
                                products: @js($productOptions) 
                             })">

        {{-- LEFT SIDE: Tasks & Actions --}}
        <div class="space-y-6 lg:col-span-3">

            {{-- 1. SECTION: PO SIAP DITERIMA (Revised) --}}
            <x-dashboard.card padding="p-0">
                {{-- Manual Header to fix padding issue with p-0 --}}
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

                                    {{-- Info PO --}}
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

                                    {{-- Action Button (Standardized) --}}
                                    <x-action-button href="{{ route('purchases.create', ['restock_order_id' => $po->id]) }}"
                                        variant="primary" size="sm" icon="arrow-right">
                                        Proses
                                    </x-action-button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    {{-- Empty State (UX Improvement) --}}
                    <div class="flex flex-col items-center justify-center py-8 text-center px-4 border-t border-slate-100">
                        <div class="bg-slate-50 p-3 rounded-full mb-3">
                            <x-lucide-check-circle class="w-6 h-6 text-slate-300" />
                        </div>
                        <p class="text-sm font-medium text-slate-900">Semua Beres!</p>
                        <p class="text-xs text-slate-500 mt-1">Tidak ada PO yang perlu diterima saat ini.</p>
                    </div>
                @endif
            </x-dashboard.card>

            {{-- 2. SECTION: QUICK ACTION & ENTRY --}}
            <div class="space-y-4">
                {{-- Scan Action Header --}}
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

                    {{-- Scan Button (Standardized) --}}
                    <x-action-button type="button" variant="primary" icon="scan-line"
                        x-on:click="openScanModal('incoming')">
                        Mulai Scan
                    </x-action-button>
                </div>

                {{-- Component Quick Entry --}}
                <x-dashboard.quick-entry :supplierOptions="$supplierOptions" :productOptions="$productOptions"
                    :productStocks="$productStocks" :prefilledType="$prefilledType"
                    :prefilledSupplierId="$prefilledSupplierId" :prefilledProductId="$prefilledProductId"
                    :prefilledCustomerName="$prefilledCustomerName" :prefilledQuantity="$prefilledQuantity" />
            </div>
        </div>

        {{-- RIGHT SIDE --}}
        <div class="space-y-6 lg:col-span-3">
            <x-dashboard.card title="Transaksi Hari Ini" subtitle="5-10 transaksi yang kamu buat hari ini." padding="p-4">
                @if(count($todayList) === 0)
                    <div
                        class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-xs text-slate-500 text-center">
                        Belum ada transaksi hari ini.
                    </div>
                @else
                    <x-dashboard.list :items="$todayList" />
                @endif
            </x-dashboard.card>
        </div>

        {{-- Scan Modal Component --}}
        <x-dashboard.scan-modal />

    </div>

    {{-- LOGIC SCANNER --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('staffDashboard', ({ skuMap, products }) => ({
                isScanOpen: false,
                scanMode: 'incoming', // 'incoming' (beli) atau 'outgoing' (jual)
                scanner: null,
                cameras: [],
                selectedCamera: null,
                isFileScanning: false,
                dragOver: false,

                init() {
                    this.$watch('isScanOpen', (value) => {
                        if (value) {
                            this.$nextTick(() => this.initScanner());
                        } else {
                            this.stopScanner();
                        }
                    });
                },

                async initScanner() {
                    if (!this.scanner) {
                        this.scanner = new Scanner(
                            "reader",
                            (decodedText, decodedResult) => this.handleScanSuccess(decodedText, decodedResult),
                            (errorMessage) => this.handleScanFailure(errorMessage)
                        );
                    }

                    if (this.cameras.length === 0) {
                        this.cameras = await this.scanner.getCameras();
                    }
                    
                    if (this.cameras.length > 0) {
                        // Prefer back camera if available
                        const backCamera = this.cameras.find(c => c.label.toLowerCase().includes('back') || c.label.toLowerCase().includes('belakang'));
                        this.selectedCamera = backCamera ? backCamera.id : this.cameras[0].id;
                        this.startScanner();
                    } else {
                        console.warn("No cameras found.");
                    }
                },

                async startScanner() {
                    if (this.selectedCamera) {
                        this.isFileScanning = false;
                        await this.scanner.start(this.selectedCamera);
                    }
                },

                async stopScanner() {
                    if (this.scanner) {
                        await this.scanner.stop();
                    }
                },

                handleScanSuccess(decodedText, decodedResult) {
                    console.log(`Scan result: ${decodedText}`, decodedResult);
                    alert(`Berhasil Scan: ${decodedText}`);
                    // TODO: Implement logic to add item to list based on SKU
                    this.closeScanModal();
                },

                handleScanFailure(errorMessage) {
                    // console.warn(`Scan error: ${errorMessage}`);
                },

                async handleFileUpload(event) {
                    const file = event.target.files[0];
                    if (file) {
                        this.scanFile(file);
                    }
                },

                async handleDrop(event) {
                    this.dragOver = false;
                    const file = event.dataTransfer.files[0];
                    if (file) {
                        this.scanFile(file);
                    }
                },

                async scanFile(file) {
                    this.isFileScanning = true;
                    try {
                        await this.stopScanner(); // Stop camera before file scan
                        await this.scanner.scanFile(file);
                    } catch (err) {
                        alert("Gagal memindai file. Pastikan gambar QR code jelas.");
                        this.isFileScanning = false;
                        this.startScanner(); // Restart camera
                    }
                },

                openScanModal(mode = 'incoming') {
                    this.scanMode = mode;
                    this.isScanOpen = true;
                },

                closeScanModal() {
                    this.isScanOpen = false;
                }
            }));
        });
    </script>
@endsection