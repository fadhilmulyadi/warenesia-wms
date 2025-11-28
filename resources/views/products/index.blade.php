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

        {{-- TOOLBAR & FILTER --}}
        <x-toolbar>
            <x-filter-bar
                :action="route('products.index')"
                :search="$search"
                :sort="$sort"
                :direction="$direction"
                placeholder="Cari produk atau SKU..."
                :filters="[
                    'category_id' => 'Kategori',
                    'stock_status' => 'Status Stok',
                ]"
            >                
                {{-- Slot Kategori --}}
                <x-slot:filter_category_id>
                    <div class="flex flex-col gap-1.5">
                        @foreach($categories as $cat)
                            <label class="flex items-center gap-2 cursor-pointer group p-1 rounded">
                                <input 
                                    type="checkbox" 
                                    name="category_id[]" 
                                    value="{{ $cat->id }}" 
                                    @checked(in_array($cat->id, (array)request()->query('category_id', [])))
                                    class="rounded border-slate-300 text-teal-600 shadow-sm focus:ring-teal-500 w-3.5 h-3.5"
                                >
                                <span class="text-xs text-slate-600 group-hover:text-slate-900">{{ $cat->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </x-slot:filter_category_id>
                
                {{-- Slot Stok --}}
                <x-slot:filter_stock_status>
                    <div class="flex flex-col gap-1.5">
                        @php
                            $stockOptions = [
                                'available' => 'Tersedia',
                                'low' => 'Low Stock',
                                'out' => 'Habis'
                            ];
                            $currentStocks = (array)request()->query('stock_status', []);
                        @endphp
                        
                        @foreach($stockOptions as $val => $lbl)
                            <label class="flex items-center gap-2 cursor-pointer group p-1 rounded">
                                <input 
                                    type="checkbox" 
                                    name="stock_status[]" 
                                    value="{{ $val }}" 
                                    @checked(in_array($val, $currentStocks))
                                    class="rounded border-slate-300 text-teal-600 shadow-sm focus:ring-teal-500 w-3.5 h-3.5"
                                >
                                <span class="text-xs text-slate-600 group-hover:text-slate-900">{{ $lbl }}</span>
                            </label>
                        @endforeach
                    </div>
                </x-slot:filter_stock_status>
            </x-filter-bar>

            <div class="flex items-center gap-2">
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
                <x-table.th>SKU</x-table.th>
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
