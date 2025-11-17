@extends('layouts.app')

@section('title', 'Edit Produk')

@section('page-header')
    <div class="flex flex-col">
        <h1 class="text-base font-semibold text-slate-900">Product details</h1>
        <p class="text-xs text-slate-500">
            Kelola informasi lengkap produk di gudang Warenesia.
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
        $purchase = (float) $product->purchase_price;
        $sale     = (float) $product->sale_price;
        $margin   = $sale - $purchase;
        $marginPercent = $purchase > 0 ? ($margin / $purchase) * 100 : null;

        $currentStock = (int) $product->current_stock;
        $minStock     = (int) $product->min_stock;
        $isLow = $currentStock <= $minStock && $currentStock > 0;
        $isOut = $currentStock === 0;
    @endphp

    @if(session('success'))
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

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
        action="{{ route('admin.products.update', $product) }}"
    >
        @csrf
        @method('PUT')

        <div x-data="{ activeTab: 'general' }" class="space-y-4">
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
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-[200px,1fr] gap-4">
                {{-- TAB NAV --}}
                <aside class="rounded-2xl border border-slate-200 bg-white p-2 text-xs">
                    <nav class="flex flex-col">
                        @foreach (['general' => 'General', 'prices' => 'Prices', 'stock' => 'Stock', 'suppliers' => 'Suppliers', 'movements' => 'Movements', 'activity' => 'Activity log'] as $tabKey => $tabLabel)
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
                                           value="{{ old('sku', $product->sku) }}"
                                           class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-[11px]">
                                </div>

                                <div>
                                    <label class="text-[11px] text-slate-500 mb-1 block">Product name *</label>
                                    <input type="text" name="name"
                                           value="{{ old('name', $product->name) }}"
                                           class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-[11px]">
                                </div>

                                <div>
                                    <label class="text-[11px] text-slate-500 mb-1 block">Category *</label>
                                    <select name="category_id"
                                            class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-[11px]">
                                        <option value="">– Pilih kategori –</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}"
                                                @selected(old('category_id', $product->category_id) == $category->id)>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="text-[11px] text-slate-500 mb-1 block">Unit of measure *</label>
                                    <input type="text" name="unit"
                                           value="{{ old('unit', $product->unit) }}"
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
                                           value="{{ old('rack_location', $product->rack_location) }}"
                                           class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-[11px]">
                                </div>

                                <div>
                                    <label class="text-[11px] text-slate-500 mb-1 block">Main supplier</label>
                                    <select name="supplier_id"
                                            class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-[11px]">
                                        <option value="">– Tidak ada –</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}"
                                                @selected(old('supplier_id', $product->supplier_id) == $supplier->id)>
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
                                           value="{{ old('current_stock', $product->current_stock) }}"
                                           class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-[11px]">
                                </div>

                                <div>
                                    <label class="text-[11px] text-slate-500 mb-1 block">Minimum stock level *</label>
                                    <input type="number" name="min_stock" min="0"
                                           value="{{ old('min_stock', $product->min_stock) }}"
                                           class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-[11px]">
                                </div>

                                <div>
                                    <span class="text-[11px] text-slate-500 mb-1 block">Stock status</span>
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
                            </div>
                        </div>

                        {{-- Deskripsi --}}
                        <div class="mt-4">
                            <label class="text-[11px] text-slate-500 mb-1 block">Description</label>
                            <textarea
                                name="description"
                                rows="3"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-[11px]">{{ old('description', $product->description) }}</textarea>
                        </div>
                    </div>

                    {{-- TAB PRICES --}}
                    <div x-show="activeTab === 'prices'" x-cloak class="space-y-4">
                        <h2 class="text-xs font-semibold text-slate-800 mb-1">Prices</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="text-[11px] text-slate-500 mb-1 block">Purchase price (Rp) *</label>
                                <input type="number" step="0.01" min="0" name="purchase_price"
                                       value="{{ old('purchase_price', $product->purchase_price) }}"
                                       class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-[11px]">
                            </div>

                            <div>
                                <label class="text-[11px] text-slate-500 mb-1 block">Sale price (Rp) *</label>
                                <input type="number" step="0.01" min="0" name="sale_price"
                                       value="{{ old('sale_price', $product->sale_price) }}"
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
                                Margin dihitung dari sale price – purchase price.
                            </div>
                        </div>
                    </div>

                    {{-- TAB STOCK (READ ONLY RINGKASAN) --}}
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
                            Saat ini semua stok dianggap berada di <span class="font-medium">Main warehouse</span>.
                            Multi-gudang bisa ditambahkan di fase berikutnya.
                        </p>
                    </div>

                    {{-- TAB SUPPLIERS (RINGKASAN) --}}
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
                    </div>

                    {{-- TAB MOVEMENTS (placeholder sampai transaksi jadi) --}}
                    <div x-show="activeTab === 'movements'" x-cloak class="space-y-4">
                        <h2 class="text-xs font-semibold text-slate-800 mb-1">Stock movements</h2>

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

                    {{-- TAB ACTIVITY LOG --}}
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
                            Activity log saat ini menggunakan waktu dibuat & terakhir diubah dari tabel produk.
                            Jika nanti diperlukan, bisa ditambah tabel audit khusus untuk melacak perubahan detail & user.
                        </p>
                    </div>
                </section>
            </div>
        </div>
    </form>
@endsection