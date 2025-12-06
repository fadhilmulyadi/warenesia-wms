@extends('layouts.app')

@section('title', 'Produk')

@section('page-header')
    <x-page-header title="Data Produk" description="Kelola informasi barang, harga, dan ketersediaan stok" />
@endsection

@section('content')
    @php
        $mobileIndexConfig = \App\Support\MobileIndexConfig::products($categories);
    @endphp

    {{-- MOBILE LIST --}}
    <div class="md:hidden">
        <x-mobile.index :items="$products" :config="$mobileIndexConfig" card-view="mobile.products.card" />
    </div>

    {{-- PAGE CONTENT --}}
    <div class="hidden md:block space-y-4" x-data="productIndexScanner"
        @scan-success.window="handleScan($event.detail.code)">

        {{-- TOOLBAR --}}
        <x-toolbar>
            @php
                $filters = [
                    'category_id' => 'Kategori',
                    'stock_status' => 'Status Stok',
                ];
                $resetKeys = ['category_id', 'stock_status'];
            @endphp

            <x-filter-bar :action="route('products.index', ['per_page' => $perPage])" :search="$search" :sort="$sort"
                :direction="$direction" :filters="$filters" :resetKeys="$resetKeys" placeholder="Cari produk atau SKU...">

                {{-- Hidden Barcode Input --}}
                <input type="hidden" name="barcode" x-model="scannedBarcode" />

                <x-slot:filter_category_id>
                    <x-filter.checkbox-list name="category_id" :options="$categories->map(fn($cat) => ['value' => $cat->id, 'label' => $cat->name])" :selected="request()->query('category_id', [])" />
                </x-slot:filter_category_id>

                <x-slot:filter_stock_status>
                    <x-filter.checkbox-list name="stock_status" :options="[
            ['value' => 'available', 'label' => 'Tersedia'],
            ['value' => 'low', 'label' => 'Low Stock'],
            ['value' => 'out', 'label' => 'Habis'],
        ]"
                        :selected="request()->query('stock_status', [])" />
                </x-slot:filter_stock_status>
            </x-filter-bar>

            <div class="flex flex-wrap flex-none gap-2 justify-end">
                {{-- SCAN BUTTON --}}
                <x-action-button type="button" variant="secondary" icon="scan-line" @click="openScanModal('search')">
                    Scan Barcode
                </x-action-button>

                @can('export', \App\Models\Product::class)
                    <x-action-button href="{{ route('products.export', request()->query()) }}" variant="secondary"
                        icon="download">
                        Ekspor CSV
                    </x-action-button>
                @endcan

                @can('create', \App\Models\Product::class)
                    <x-action-button href="{{ route('products.create') }}" variant="primary" icon="plus">
                        Tambah Produk
                    </x-action-button>
                @endcan
            </div>
        </x-toolbar>

        {{-- TABLE --}}
        <x-table>
            <x-table.thead>
                <x-table.th sortable name="sku">SKU</x-table.th>
                <x-table.th sortable name="name">Produk</x-table.th>
                <x-table.th>Kategori</x-table.th>
                <x-table.th>Lokasi Rak</x-table.th>
                <x-table.th align="right" sortable name="current_stock">Stok</x-table.th>
                <x-table.th align="right">Harga Jual</x-table.th>
                @can('create', \App\Models\Product::class)
                    <x-table.th align="right">Aksi</x-table.th>
                @endcan
            </x-table.thead>

            <x-table.tbody>
                @forelse($products as $product)
                    <x-table.tr :href="route('products.show', $product)">

                        <x-table.td class="font-mono whitespace-nowrap">
                            {{ $product->sku }}
                        </x-table.td>

                        <x-table.td>
                            <x-product.info :product="$product" />
                        </x-table.td>

                        <x-table.td>
                            {{ $product->category?->name ?? '-' }}
                        </x-table.td>

                        <x-table.td>
                            {{ $product->rack_location ?: '-' }}
                        </x-table.td>

                        <x-table.td align="right">
                            <x-product.stock-badge :product="$product" />
                        </x-table.td>

                        <x-table.td align="right">
                            <x-money :value="$product->sale_price" />
                        </x-table.td>

                        @can('create', \App\Models\Product::class)
                            <x-table.td align="right">
                                <x-product.actions :product="$product" />
                            </x-table.td>
                        @endcan

                    </x-table.tr>
                @empty
                    @if(request()->query('barcode'))
                        <tr>
                            <td colspan="100%">
                                <div class="flex flex-col items-center justify-center py-12 text-center">
                                    <div class="bg-amber-50 p-4 rounded-full mb-3 text-amber-500">
                                        <x-lucide-scan-line class="w-8 h-8" />
                                    </div>
                                    <h3 class="text-base font-semibold text-slate-900">Produk tidak ditemukan</h3>
                                    <p class="text-sm text-slate-500 mt-1 max-w-xs mx-auto">
                                        Produk dengan barcode <span
                                            class="font-mono font-bold">{{ request()->query('barcode') }}</span> tidak terdaftar di
                                        sistem.
                                    </p>
                                    <x-action-button href="{{ route('products.index') }}" variant="secondary" size="sm"
                                        class="mt-4">
                                        Reset Filter
                                    </x-action-button>
                                </div>
                            </td>
                        </tr>
                    @else
                        <x-table.tr>
                            <x-table.td colspan="7" class="py-10">
                                <x-empty-state
                                    title="Tidak ada produk ditemukan"
                                    description="Silakan tambahkan produk baru atau reset filter pencarian."
                                    icon="box"
                                >
                                    <x-slot name="actions">
                                        @can('create', \App\Models\Product::class)
                                            <x-action-button href="{{ route('products.create') }}" variant="primary" icon="plus">
                                                Tambah Produk
                                            </x-action-button>
                                        @endcan
                                    </x-slot>
                                </x-empty-state>
                            </x-table.td>
                        </x-table.tr>
                    @endif
                @endforelse
            </x-table.tbody>
        </x-table>

        {{-- PAGINATION --}}
        @if($products->hasPages() || $products->total() > 0)
            <x-advanced-pagination :paginator="$products" />
        @endif

        {{-- SCAN MODAL --}}
        <x-dashboard.scan-modal />

    </div>

    <x-confirm-delete-modal />

@endsection
