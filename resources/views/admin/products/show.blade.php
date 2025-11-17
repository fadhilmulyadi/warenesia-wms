@extends('layouts.app')

@section('title', 'Detail Produk')

@section('page-header')
    <div class="flex flex-col">
        <h1 class="text-base font-semibold text-slate-900">Product details</h1>
        <p class="text-xs text-slate-500">
            Ringkasan lengkap informasi produk di gudang Warenesia.
        </p>
    </div>
@endsection

@section('content')
    @php
        $categoryName = $product->category?->name ?? '-';
        $supplierName = $product->supplier?->name ?? '-';
        $isLow = $product->current_stock <= $product->min_stock && $product->current_stock > 0;
        $isOut = $product->current_stock == 0;

        $purchase = (float) $product->purchase_price;
        $sale = (float) $product->sale_price;
        $margin = $sale - $purchase;
        $marginPercent = $purchase > 0 ? ($margin / $purchase) * 100 : null;
    @endphp

    <div
        x-data="{ activeTab: 'general' }"
        class="space-y-4"
    >
        {{-- Header status + nama produk --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700">
                    Active
                </span>
                <div class="flex flex-col">
                    <span class="text-xs uppercase tracking-wide text-slate-400">Product</span>
                    <span class="text-sm font-semibold text-slate-900">
                        {{ $product->name }}
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-2 text-[11px] text-slate-500">
                <span>SKU:</span>
                <span class="font-mono text-slate-800">{{ $product->sku }}</span>
                <span class="hidden md:inline-block h-3 w-px bg-slate-200 mx-1"></span>
                <span class="hidden md:inline-block">
                    Kategori: <span class="font-medium">{{ $categoryName }}</span>
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-[200px,1fr] gap-4">
            {{-- SIDE TAB NAV --}}
            <aside class="rounded-2xl border border-slate-200 bg-white p-2 text-xs">
                <nav class="flex flex-col">
                    <button type="button"
                        @click="activeTab = 'general'"
                        :class="activeTab === 'general'
                            ? 'bg-teal-50 text-teal-700 border-teal-200'
                            : 'text-slate-600 hover:bg-slate-50 border-transparent'"
                        class="flex items-center justify-between rounded-lg border px-3 py-2 mb-1">
                        <span>General</span>
                    </button>

                    <button type="button"
                        @click="activeTab = 'prices'"
                        :class="activeTab === 'prices'
                            ? 'bg-teal-50 text-teal-700 border-teal-200'
                            : 'text-slate-600 hover:bg-slate-50 border-transparent'"
                        class="flex items-center justify-between rounded-lg border px-3 py-2 mb-1">
                        <span>Prices</span>
                    </button>

                    <button type="button"
                        @click="activeTab = 'stock'"
                        :class="activeTab === 'stock'
                            ? 'bg-teal-50 text-teal-700 border-teal-200'
                            : 'text-slate-600 hover:bg-slate-50 border-transparent'"
                        class="flex items-center justify-between rounded-lg border px-3 py-2 mb-1">
                        <span>Stock</span>
                    </button>

                    <button type="button"
                        @click="activeTab = 'suppliers'"
                        :class="activeTab === 'suppliers'
                            ? 'bg-teal-50 text-teal-700 border-teal-200'
                            : 'text-slate-600 hover:bg-slate-50 border-transparent'"
                        class="flex items-center justify-between rounded-lg border px-3 py-2 mb-1">
                        <span>Suppliers</span>
                    </button>

                    <button type="button"
                        @click="activeTab = 'movements'"
                        :class="activeTab === 'movements'
                            ? 'bg-teal-50 text-teal-700 border-teal-200'
                            : 'text-slate-600 hover:bg-slate-50 border-transparent'"
                        class="flex items-center justify-between rounded-lg border px-3 py-2 mb-1">
                        <span>Movements</span>
                    </button>

                    <button type="button"
                        @click="activeTab = 'activity'"
                        :class="activeTab === 'activity'
                            ? 'bg-teal-50 text-teal-700 border-teal-200'
                            : 'text-slate-600 hover:bg-slate-50 border-transparent'"
                        class="flex items-center justify-between rounded-lg border px-3 py-2">
                        <span>Activity log</span>
                    </button>
                </nav>
            </aside>

            {{-- MAIN PANEL --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-4 text-xs space-y-4">
                {{-- TAB: GENERAL --}}
                <div x-show="activeTab === 'general'" x-cloak class="space-y-4">
                    <h2 class="text-xs font-semibold text-slate-800 mb-1">General information</h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        {{-- Kolom 1 --}}
                        <div class="space-y-3">
                            <div>
                                <div class="text-[11px] text-slate-500 mb-1">SKU</div>
                                <div class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-1.5 font-mono text-[11px]">
                                    {{ $product->sku }}
                                </div>
                            </div>

                            <div>
                                <div class="text-[11px] text-slate-500 mb-1">Product name</div>
                                <div class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-1.5">
                                    {{ $product->name }}
                                </div>
                            </div>

                            <div>
                                <div class="text-[11px] text-slate-500 mb-1">Category</div>
                                <div class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-1.5">
                                    {{ $categoryName }}
                                </div>
                            </div>

                            <div>
                                <div class="text-[11px] text-slate-500 mb-1">Unit of measure</div>
                                <div class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-1.5">
                                    {{ $product->unit }}
                                </div>
                            </div>
                        </div>

                        {{-- Kolom 2 --}}
                        <div class="space-y-3">
                            <div>
                                <div class="text-[11px] text-slate-500 mb-1">Status</div>
                                <div class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-1.5">
                                    Active
                                </div>
                            </div>

                            <div>
                                <div class="text-[11px] text-slate-500 mb-1">Default warehouse</div>
                                <div class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-1.5">
                                    Main warehouse
                                </div>
                            </div>

                            <div>
                                <div class="text-[11px] text-slate-500 mb-1">Rack location</div>
                                <div class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-1.5">
                                    {{ $product->rack_location ?: '-' }}
                                </div>
                            </div>

                            <div>
                                <div class="text-[11px] text-slate-500 mb-1">Main supplier</div>
                                <div class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-1.5">
                                    {{ $supplierName }}
                                </div>
                            </div>
                        </div>

                        {{-- Kolom 3 --}}
                        <div class="space-y-3">
                            <div>
                                <div class="text-[11px] text-slate-500 mb-1">Current stock</div>
                                <div class="flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 px-2 py-1.5">
                                    <span class="font-semibold text-slate-900">
                                        {{ $product->current_stock }} {{ $product->unit }}
                                    </span>
                                    <span @class([
                                        'inline-flex items-center rounded-full px-2 py-0.5 text-[10px]',
                                        'bg-emerald-50 text-emerald-700' => !$isLow && !$isOut,
                                        'bg-amber-50 text-amber-700' => $isLow,
                                        'bg-red-50 text-red-700' => $isOut,
                                    ])>
                                        @if($isOut)
                                            Out of stock
                                        @elseif($isLow)
                                            Low stock
                                        @else
                                            Stock OK
                                        @endif
                                    </span>
                                </div>
                            </div>

                            <div>
                                <div class="text-[11px] text-slate-500 mb-1">Minimum stock level</div>
                                <div class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-1.5">
                                    {{ $product->min_stock }} {{ $product->unit }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Deskripsi --}}
                    <div class="mt-4">
                        <div class="text-[11px] text-slate-500 mb-1">Description</div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-[11px] text-slate-700 min-h-[60px]">
                            {{ $product->description ?: 'Belum ada deskripsi untuk produk ini.' }}
                        </div>
                    </div>
                </div>

                {{-- TAB: PRICES --}}
                <div x-show="activeTab === 'prices'" x-cloak class="space-y-4">
                    <h2 class="text-xs font-semibold text-slate-800 mb-1">Prices</h2>

                    <div class="rounded-lg border border-slate-100 bg-slate-50 px-3 py-2 mb-2 text-[11px] text-slate-600">
                        Current average cost:
                        <span class="font-semibold text-slate-900">
                            Rp {{ number_format($purchase, 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="min-w-full text-[11px]">
                            <thead class="bg-slate-50 text-slate-500">
                                <tr>
                                    <th class="px-3 py-2 text-left font-medium">Price type</th>
                                    <th class="px-3 py-2 text-right font-medium">Price (Rp)</th>
                                    <th class="px-3 py-2 text-right font-medium">Margin (Rp)</th>
                                    <th class="px-3 py-2 text-right font-medium">Margin %</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr>
                                    <td class="px-3 py-2">Purchase price</td>
                                    <td class="px-3 py-2 text-right">
                                        Rp {{ number_format($purchase, 0, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-2 text-right text-slate-400">-</td>
                                    <td class="px-3 py-2 text-right text-slate-400">-</td>
                                </tr>
                                <tr>
                                    <td class="px-3 py-2">Sale price</td>
                                    <td class="px-3 py-2 text-right">
                                        Rp {{ number_format($sale, 0, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        Rp {{ number_format($margin, 0, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        @if(!is_null($marginPercent))
                                            {{ number_format($marginPercent, 1, ',', '.') }}%
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <p class="text-[11px] text-slate-500">
                        Struktur harga di atas menggunakan harga beli sebagai dasar perhitungan margin.
                        Jika nanti ada level harga lain (retail/wholesale), bisa ditambahkan sebagai baris baru.
                    </p>
                </div>

                {{-- TAB: STOCK --}}
                <div x-show="activeTab === 'stock'" x-cloak class="space-y-4">
                    <h2 class="text-xs font-semibold text-slate-800 mb-1">Stock by location</h2>

                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="min-w-full text-[11px]">
                            <thead class="bg-slate-50 text-slate-500">
                                <tr>
                                    <th class="px-3 py-2 text-left font-medium">Location</th>
                                    <th class="px-3 py-2 text-left font-medium">Bin / Rack</th>
                                    <th class="px-3 py-2 text-right font-medium">On hand</th>
                                    <th class="px-3 py-2 text-right font-medium">Minimum</th>
                                    <th class="px-3 py-2 text-right font-medium">Available</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr>
                                    <td class="px-3 py-2">Main warehouse</td>
                                    <td class="px-3 py-2">{{ $product->rack_location ?: '-' }}</td>
                                    <td class="px-3 py-2 text-right">
                                        {{ $product->current_stock }} {{ $product->unit }}
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        {{ $product->min_stock }} {{ $product->unit }}
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        {{ max($product->current_stock - $product->min_stock, 0) }} {{ $product->unit }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <p class="text-[11px] text-slate-500">
                        Panel ini bisa diperluas untuk multi-gudang di masa depan. Untuk saat ini,
                        semua stok dianggap berada di <span class="font-medium">Main warehouse</span>.
                    </p>
                </div>

                {{-- TAB: SUPPLIERS --}}
                <div x-show="activeTab === 'suppliers'" x-cloak class="space-y-4">
                    <h2 class="text-xs font-semibold text-slate-800 mb-1">Suppliers</h2>

                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="min-w-full text-[11px]">
                            <thead class="bg-slate-50 text-slate-500">
                                <tr>
                                    <th class="px-3 py-2 text-left font-medium">Supplier</th>
                                    <th class="px-3 py-2 text-left font-medium">Main?</th>
                                    <th class="px-3 py-2 text-right font-medium">Last purchase price</th>
                                    <th class="px-3 py-2 text-right font-medium">Last updated</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @if($product->supplier)
                                    <tr>
                                        <td class="px-3 py-2">
                                            {{ $product->supplier->name }}
                                        </td>
                                        <td class="px-3 py-2">
                                            <span class="inline-flex rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] text-emerald-700">
                                                Main supplier
                                            </span>
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            Rp {{ number_format($purchase, 0, ',', '.') }}
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            {{ $product->updated_at->format('d M Y') }}
                                        </td>
                                    </tr>
                                @else
                                    <tr>
                                        <td colspan="4" class="px-3 py-4 text-center text-slate-500">
                                            Belum ada supplier utama yang dihubungkan ke produk ini.
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <p class="text-[11px] text-slate-500">
                        Ke depan, modul ini bisa dikembangkan untuk mendukung lebih dari satu supplier per produk,
                        termasuk lead time dan riwayat harga beli per supplier.
                    </p>
                </div>

                {{-- TAB: MOVEMENTS --}}
                <div x-show="activeTab === 'movements'" x-cloak class="space-y-4">
                    <h2 class="text-xs font-semibold text-slate-800 mb-1">Stock movements</h2>

                    {{-- Nanti dihubungkan ke modul transaksi masuk/keluar --}}
                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="min-w-full text-[11px]">
                            <thead class="bg-slate-50 text-slate-500">
                                <tr>
                                    <th class="px-3 py-2 text-left font-medium">Type</th>
                                    <th class="px-3 py-2 text-left font-medium">Date</th>
                                    <th class="px-3 py-2 text-left font-medium">Reference</th>
                                    <th class="px-3 py-2 text-left font-medium">From / To</th>
                                    <th class="px-3 py-2 text-left font-medium">Status</th>
                                    <th class="px-3 py-2 text-right font-medium">Quantity</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                {{-- Untuk saat ini belum ada data pergerakan, nanti akan diisi dari modul transaksi --}}
                                <tr>
                                    <td colspan="6" class="px-3 py-4 text-center text-slate-500">
                                        Belum ada riwayat pergerakan stok untuk produk ini.
                                        Data akan muncul setelah modul transaksi barang masuk/keluar diimplementasikan.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- TAB: ACTIVITY LOG --}}
                <div x-show="activeTab === 'activity'" x-cloak class="space-y-4">
                    <h2 class="text-xs font-semibold text-slate-800 mb-1">Activity log</h2>

                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="min-w-full text-[11px]">
                            <thead class="bg-slate-50 text-slate-500">
                                <tr>
                                    <th class="px-3 py-2 text-left font-medium">Date</th>
                                    <th class="px-3 py-2 text-left font-medium">Activity</th>
                                    <th class="px-3 py-2 text-left font-medium">User</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr>
                                    <td class="px-3 py-2">
                                        {{ $product->created_at->format('d/m/Y H:i:s') }}
                                    </td>
                                    <td class="px-3 py-2">
                                        Product has been created.
                                    </td>
                                    <td class="px-3 py-2">
                                        {{-- Untuk sekarang, belum ada audit user per event --}}
                                        System
                                    </td>
                                </tr>
                                @if($product->updated_at->ne($product->created_at))
                                    <tr>
                                        <td class="px-3 py-2">
                                            {{ $product->updated_at->format('d/m/Y H:i:s') }}
                                        </td>
                                        <td class="px-3 py-2">
                                            Product details have been updated.
                                        </td>
                                        <td class="px-3 py-2">
                                            System
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <p class="text-[11px] text-slate-500">
                        Activity log saat ini menggunakan informasi waktu dibuat & terakhir diubah dari tabel produk.
                        Jika nanti diperlukan, bisa ditambah tabel audit khusus untuk melacak perubahan field dan user yang mengubah.
                    </p>
                </div>
            </section>
        </div>
    </div>
@endsection
