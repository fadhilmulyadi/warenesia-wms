@extends('layouts.app')

@section('title', 'Produk')

@section('page-header')
    <x-page-header
        title="Data Produk"
        description="Kelola informasi barang, harga, dan ketersediaan stok gudang."
    />
@endsection

@section('content')
    <div class="space-y-4">

        <x-toolbar>
            @php
                $filters = [
                    'category_id' => 'Kategori',
                    'stock_status' => 'Status Stok',
                ];
                $resetKeys = ['category_id', 'stock_status'];
            @endphp

            <x-filter-bar
                :action="route('products.index', ['per_page' => $perPage])"
                :search="$search"
                :sort="$sort"
                :direction="$direction"
                :filters="$filters"
                :resetKeys="$resetKeys"
                placeholder="Cari produk atau SKU..."
            >
                <x-slot:filter_category_id>
                    <x-filter.checkbox-list
                        name="category_id"
                        :options="$categories->map(fn ($cat) => ['value' => $cat->id, 'label' => $cat->name])"
                        :selected="request()->query('category_id', [])"
                    />
                </x-slot:filter_category_id>

                <x-slot:filter_stock_status>
                    <x-filter.checkbox-list
                        name="stock_status"
                        :options="[
                            ['value' => 'available', 'label' => 'Tersedia'],
                            ['value' => 'low', 'label' => 'Low Stock'],
                            ['value' => 'out', 'label' => 'Habis'],
                        ]"
                        :selected="request()->query('stock_status', [])"
                    />
                </x-slot:filter_stock_status>
            </x-filter-bar>

            <div class="flex flex-wrap flex-none gap-2 justify-end">
                @can('export', \App\Models\Product::class)
                    <x-action-button 
                        href="{{ route('products.export', request()->query()) }}"
                        variant="secondary"
                        icon="download"
                    >
                        Ekspor CSV
                    </x-action-button>
                @endcan
                
                @can('create', \App\Models\Product::class)
                    <x-action-button 
                        href="{{ route('products.create') }}"
                        variant="primary"
                        icon="plus"
                    >
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
                <x-table.th>Supplier</x-table.th>
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
                            {{ $product->supplier?->name ?? '-' }}
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
                    <x-product.empty-state />
                @endforelse
            </x-table.tbody>
        </x-table>

        {{-- PAGINATION --}}
        @if($products->hasPages() || $products->total() > 0)
            <x-advanced-pagination :paginator="$products" />
        @endif

        <x-confirm-delete-modal />
    </div>
@endsection
