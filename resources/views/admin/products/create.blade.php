@extends('layouts.app')

@section('title', 'Tambah Produk')

@section('page-header')
    <div class="flex flex-col">
        <h1 class="text-base font-semibold text-slate-900">Tambah Produk</h1>
        <p class="text-xs text-slate-500">
            Daftarkan produk baru ke gudang Warenesia.
        </p>
    </div>

    <div class="flex items-center gap-2">
        <a href="{{ route('admin.products.index') }}"
           class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-xs text-slate-600 hover:bg-slate-50">
            Back to list
        </a>

        <button
            type="submit"
            form="product-form"
            class="inline-flex items-center rounded-lg bg-teal-500 px-4 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-teal-600">
            Save
        </button>
    </div>
@endsection

@section('content')
    @php
        $purchase      = (float) old('purchase_price', 0);
        $sale          = (float) old('sale_price', 0);
        $margin        = $sale - $purchase;
        $marginPercent = $purchase > 0 ? ($margin / $purchase) * 100 : null;

        $currentStock = (int) old('current_stock', 0);
        $minStock     = (int) old('min_stock', 0);
        $isLow        = $currentStock <= $minStock && $currentStock > 0;
        $isOut        = $currentStock === 0;
    @endphp

    <div class="max-w-6xl mx-auto space-y-4">
        @if($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700">
                <div class="font-semibold mb-1">Terjadi kesalahan:</div>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            id="product-form"
            method="POST"
            action="{{ route('admin.products.store') }}"
        >
            @csrf

            <div x-data="{ activeTab: 'general' }" class="space-y-4">
                {{-- Header status + label --}}
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700">
                            Active
                        </span>
                        <div class="flex flex-col">
                            <span class="text-xs uppercase tracking-wide text-slate-400">Product</span>
                            <span class="text-sm font-semibold text-slate-900">
                                {{ old('name') ?: 'New product' }}
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 text-[11px] text-slate-500">
                        <span>SKU:</span>
                        <span class="font-mono text-slate-800">
                            {{ old('sku') ?: 'Belum diisi' }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-[200px,1fr] gap-4">
                    {{-- TAB NAV --}}
                    <aside class="rounded-2xl border border-slate-200 bg-white p-2 text-xs">
                        <nav class="flex flex-col">
                            @foreach ([
                                'general'   => 'General',
                                'prices'    => 'Prices',
                                'stock'     => 'Stock',
                                'suppliers' => 'Suppliers',
                                'movements' => 'Movements',
                                'activity'  => 'Activity log',
                            ] as $tabKey => $tabLabel)
                                <button
                                    type="button"
                                    @click="activeTab = '{{ $tabKey }}'"
                                    :class="activeTab === '{{ $tabKey }}'
                                        ? 'bg-teal-50 text-teal-700 border-teal-200'
                                        : 'text-slate-600 hover:bg-slate-50 border-transparent'"
                                    class="flex items-center justify-between rounded-lg border px-3 py-2 mb-1 last:mb-0"
                                >
                                    <span>{{ $tabLabel }}</span>
                                </button>
                            @endforeach
                        </nav>
                    </aside>

                    {{-- MAIN PANEL --}}
                    <section class="rounded-2xl border border-slate-200 bg-white p-4 text-xs space-y-4">
                        {{-- TAB GENERAL --}}
                        <div x-show="activeTab === 'general'" x-cloak class="space-y-4">
                            <h2 class="text-xs font-semibold text-slate-800 mb-1">General information</h2>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                {{-- kolom 1 --}}
                                <div class="space-y-3">
                                    <div>
                                        <label class="text-[11px] text-slate-500 mb-1 block">SKU *</label>
                                        <input type="text" name="sku"
                                               value="{{ old('sku') }}"
                                               class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-[11px]">
                                    </div>

                                    <div>
                                        <label class="text-[11px] text-slate-500 mb-1 block">Product name *</label>
                                        <input type="text" name="name"
                                               value="{{ old('name') }}"
                                               class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-[11px]">
                                    </div>

                                    <div>
                                        <label class="text-[11px] text-slate-500 mb-1 block">Category *</label>
                                        <select name="category_id"
                                                class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-[11px]">
                                            <option value="">– Pilih kategori –</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}"
                                                    @selected(old('category_id') == $category->id)>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="text-[11px] text-slate-500 mb-1 block">Unit of measure *</label>
                                        <input type="text" name="unit"
                                               value="{{ old('unit', 'pcs') }}"
                                               class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-[11px]">
                                    </div>
                                </div>

                                {{-- kolom 2 --}}
                                <div class="space-y-3">
                                    <div>
                                        <span class="text-[11px] text-slate-500 mb-1 block">Status</span>
                                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-1.5">
                                            Active
                                        </div>
                                    </div>

                                    <div>
                                        <span class="text-[11px] text-slate-500 mb-1 block">Default warehouse</span>
                                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-1.5">
                                            Main warehouse
                                        </div>
                                    </div>

                                    <div>
                                        <label class="text-[11px] text-slate-500 mb-1 block">Rack location</label>
                                        <input type="text" name="rack_location"
                                               value="{{ old('rack_location') }}"
                                               class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-[11px]">
                                    </div>

                                    <div>
                                        <label class="text-[11px] text-slate-500 mb-1 block">Main supplier</label>
                                        <select name="supplier_id"
                                                class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-[11px]">
                                            <option value="">– Tidak ada –</option>
                                            @foreach($suppliers as $supplier)
                                                <option value="{{ $supplier->id }}"
                                                    @selected(old('supplier_id') == $supplier->id)>
                                                    {{ $supplier->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- kolom 3 --}}
                                <div class="space-y-3">
                                    <div>
                                        <label class="text-[11px] text-slate-500 mb-1 block">Current stock *</label>
                                        <input type="number" name="current_stock" min="0"
                                               value="{{ old('current_stock', 0) }}"
                                               class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-[11px]">
                                    </div>

                                    <div>
                                        <label class="text-[11px] text-slate-500 mb-1 block">Minimum stock level *</label>
                                        <input type="number" name="min_stock" min="0"
                                               value="{{ old('min_stock', 0) }}"
                                               class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-[11px]">
                                    </div>

                                    <div>
                                        <span class="text-[11px] text-slate-500 mb-1 block">Stock status</span>
                                        <div class="flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 px-2 py-1.5">
                                            <span class="font-semibold text-slate-900">
                                                {{ $currentStock }} {{ old('unit', 'pcs') }}
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
                                </div>
                            </div>

                            {{-- Deskripsi --}}
                            <div class="mt-4">
                                <label class="text-[11px] text-slate-500 mb-1 block">Description</label>
                                <textarea
                                    name="description"
                                    rows="3"
                                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-[11px]">{{ old('description') }}</textarea>
                            </div>
                        </div>

                        {{-- TAB PRICES --}}
                        <div x-show="activeTab === 'prices'" x-cloak class="space-y-4">
                            <h2 class="text-xs font-semibold text-slate-800 mb-1">Prices</h2>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label class="text-[11px] text-slate-500 mb-1 block">Purchase price (Rp) *</label>
                                    <input type="number" step="0.01" min="0" name="purchase_price"
                                           value="{{ old('purchase_price') }}"
                                           class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-[11px]">
                                </div>

                                <div>
                                    <label class="text-[11px] text-slate-500 mb-1 block">Sale price (Rp) *</label>
                                    <input type="number" step="0.01" min="0" name="sale_price"
                                           value="{{ old('sale_price') }}"
                                           class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-[11px]">
                                </div>
                            </div>

                            <div class="rounded-lg border border-slate-100 bg-slate-50 px-3 py-2 mt-3 text-[11px] text-slate-600 space-y-1">
                                <div>
                                    Current margin:
                                    <span class="font-semibold text-slate-900">
                                        Rp {{ number_format($margin, 0, ',', '.') }}
                                    </span>
                                    @if(!is_null($marginPercent))
                                        (<span class="font-semibold text-slate-900">
                                            {{ number_format($marginPercent, 1, ',', '.') }}%
                                        </span>)
                                    @endif
                                </div>
                                <div class="text-[10px] text-slate-500">
                                    Margin dihitung dari sale price – purchase price (berdasarkan nilai yang kamu isi di atas).
                                </div>
                            </div>
                        </div>

                        {{-- TAB STOCK (placeholder sebelum ada transaksi) --}}
                        <div x-show="activeTab === 'stock'" x-cloak class="space-y-4">
                            <h2 class="text-xs font-semibold text-slate-800 mb-1">Stock by location</h2>

                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 text-[11px] text-slate-600">
                                Data detail per lokasi akan muncul setelah produk disimpan
                                dan modul transaksi barang masuk/keluar diimplementasikan.
                                Saat ini, stok dianggap berada di <span class="font-semibold">Main warehouse</span>.
                            </div>
                        </div>

                        {{-- TAB SUPPLIERS (placeholder) --}}
                        <div x-show="activeTab === 'suppliers'" x-cloak class="space-y-4">
                            <h2 class="text-xs font-semibold text-slate-800 mb-1">Suppliers</h2>

                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 text-[11px] text-slate-600">
                                Hubungan detail dengan beberapa supplier akan bisa diatur
                                setelah produk disimpan. Untuk sekarang, pilih dulu
                                <span class="font-semibold">Main supplier</span> di tab General.
                            </div>
                        </div>

                        {{-- TAB MOVEMENTS (placeholder) --}}
                        <div x-show="activeTab === 'movements'" x-cloak class="space-y-4">
                            <h2 class="text-xs font-semibold text-slate-800 mb-1">Stock movements</h2>

                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 text-[11px] text-slate-600">
                                Riwayat pergerakan stok (purchase, sale, restock, adjustment)
                                akan muncul di sini setelah modul transaksi selesai dan
                                produk ini mulai digunakan dalam transaksi.
                            </div>
                        </div>

                        {{-- TAB ACTIVITY LOG (placeholder) --}}
                        <div x-show="activeTab === 'activity'" x-cloak class="space-y-4">
                            <h2 class="text-xs font-semibold text-slate-800 mb-1">Activity log</h2>

                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 text-[11px] text-slate-600">
                                Activity log akan mulai mencatat perubahan setelah produk disimpan
                                (misalnya perubahan harga, stok, kategori, dan lain-lain).
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </form>
    </div>
@endsection