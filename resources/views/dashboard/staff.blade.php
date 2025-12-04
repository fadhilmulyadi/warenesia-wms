@extends('layouts.app')

@section('title', 'Dashboard Staff Gudang')

@push('scripts')
    <script src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>
@endpush

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

        {{-- LEFT SIDE --}}
        <div class="space-y-6 lg:col-span-3">
            <div class="space-y-6">
                {{-- PO Siap Diterima Widget --}}
                @if(isset($poReadyToReceive) && count($poReadyToReceive) > 0)
                    <x-dashboard.card title="PO Siap Diterima" subtitle="Restock Order yang sudah sampai (Received)."
                        padding="p-0">
                        <div class="divide-y divide-slate-100">
                            @foreach($poReadyToReceive as $po)
                                <div class="p-4 flex items-center justify-between hover:bg-slate-50 transition">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="font-semibold text-slate-800 text-sm">{{ $po->po_number }}</span>
                                            <span
                                                class="text-[10px] px-1.5 py-0.5 rounded-full bg-blue-50 text-blue-600 font-medium border border-blue-100">
                                                {{ $po->items->count() }} Items
                                            </span>
                                        </div>
                                        <p class="text-xs text-slate-500 truncate">
                                            {{ $po->supplier->name ?? 'Unknown Supplier' }}
                                        </p>
                                    </div>
                                    <a href="{{ route('purchases.create', ['restock_order_id' => $po->id]) }}"
                                        class="ml-3 inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-white bg-teal-600 rounded-lg hover:bg-teal-700 focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition shadow-sm">
                                        <span>Proses</span>
                                        <x-lucide-arrow-right class="w-3 h-3" />
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </x-dashboard.card>
                @endif

                <div class="space-y-3">
                    {{-- Tombol Scan + Info --}}
                    <div class="flex items-center justify-between gap-3">
                        <button type="button"
                            class="inline-flex items-center gap-2 rounded-xl border border-teal-500 text-teal-700
                                        px-3 py-2 text-xs font-semibold bg-teal-50 hover:bg-teal-100 active:scale-[0.98] transition"
                            x-on:click="openScanModal('incoming')">
                            <x-lucide-scan-line class="w-4 h-4" />
                            <span>Scan Barcode</span>
                        </button>
                        <p class="text-[11px] text-slate-500">
                            Gunakan kamera untuk input cepat transaksi.
                        </p>
                    </div>

                    {{-- Component Quick Entry (Sudah aman, tidak perlu diubah) --}}
                    <x-dashboard.quick-entry :supplierOptions="$supplierOptions" :productOptions="$productOptions"
                        :productStocks="$productStocks" :prefilledType="$prefilledType"
                        :prefilledSupplierId="$prefilledSupplierId" :prefilledProductId="$prefilledProductId"
                        :prefilledCustomerName="$prefilledCustomerName" :prefilledQuantity="$prefilledQuantity" />
                </div>
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
                scanner: null,
                scanMode: 'incoming', // 'incoming' (beli) atau 'outgoing' (jual)

                init() {
                    // Matikan kamera jika user reload/tutup tab
                    window.addEventListener('beforeunload', () => {
                        if (this.scanner) this.scanner.stop();
                    });
                },

                openScanModal(mode = 'incoming') {
                    this.scanMode = mode;
                    this.isScanOpen = true;

                    this.$nextTick(() => {
                        this.startScanner();
                    });
                },

                closeScanModal() {
                    this.isScanOpen = false;
                    if (this.scanner) {
                        this.scanner.stop();
                    }
                },

                startScanner() {
                    let videoEl = document.getElementById('scanner-preview');

                    if (!videoEl) {
                        console.error("Elemen video #scanner-preview tidak ditemukan.");
                        return;
                    }

                    if (!this.scanner) {
                        this.scanner = new Instascan.Scanner({ video: videoEl });

                        this.scanner.addListener('scan', (content) => {
                            this.handleScanResult(content);
                        });
                    }

                    Instascan.Camera.getCameras().then((cameras) => {
                        if (cameras.length > 0) {
                            console.log('Kamera yang ditemukan:', cameras);

                            let selectedCam = cameras[0];

                            let realCamera = cameras.find(c =>
                                !c.name.toLowerCase().includes('droidcam') &&
                                !c.name.toLowerCase().includes('virtual') &&
                                !c.name.toLowerCase().includes('obs')
                            );

                            if (realCamera) {
                                selectedCam = realCamera;
                            } else if (cameras.length > 1) {
                                selectedCam = cameras[cameras.length - 1];
                            }

                            console.log('Memilih kamera:', selectedCam.name);
                            this.scanner.start(selectedCam);

                        } else {
                            alert('Tidak ada kamera yang ditemukan.');
                        }
                    }).catch((e) => console.error(e));
                },

                handleScanResult(sku) {
                    console.log('Scanned SKU:', sku);

                    let productId = skuMap[sku];

                    if (productId) {
                        this.$dispatch('barcode-prefill', {
                            product_id: productId,
                            mode: this.scanMode
                        });
                        this.closeScanModal();
                    } else {
                        alert(`Produk dengan SKU "${sku}" tidak ditemukan.`);
                    }
                }
            }));
        });
    </script>
@endsection