@extends('layouts.app')

@section('title', 'Products')

@section('page-header')
    <x-page-header
        title="Data Produk"
        description="Kelola informasi barang, harga, dan ketersediaan stok gudang."
    />
@endsection

@section('content')
    <div class="space-y-4">
        {{-- @if(session('status'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-800">
            {{ session('status') }}
        </div>
        @endif --}}

        {{-- Toolbar --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <form method="GET" action="{{ route('products.index') }}" class="flex-1 max-w-sm">
                <div class="relative">
                    <input type="text" name="q" placeholder="Cari produk atau SKU..." value="{{ $search }}"
                        class="w-full h-9 rounded-lg border-slate-200 pl-8 pr-3 py-2 text-xs focus:border-teal-500 focus:ring-teal-500">
                    <x-lucide-search class="h-3 w-3 text-slate-400 absolute left-2 top-2.5" />
                </div>
            </form>

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
        </div>

        {{-- Tabel --}}
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table class="min-w-full text-xs">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            SKU
                        </th>

                        <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Produk
                        </th>

                        <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Kategori
                        </th>

                        <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Supplier
                        </th>

                        <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Stok
                        </th>

                        <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">
                            Harga Jual
                        </th>

                        @can('create', \App\Models\Product::class)
                            <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Aksi
                            </th>
                        @endcan
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $product)
                        <tr @click="window.location.href='{{ route('products.show', $product) }}'"
                            class="group hover:bg-slate-50 transition-colors cursor-pointer">
                            {{-- SKU --}}
                            <td
                                class="px-3 py-3 align-top font-mono text-xs text-slate-600 group-hover:text-slate-900 whitespace-nowrap">
                                {{ $product->sku }}
                            </td>

                            {{-- Produk info --}}
                            <td class="px-3 py-3 align-top">
                                <div class="flex items-center gap-3">
                                    {{-- Gambar / Placeholder --}}
                                    <div
                                        class="h-9 w-9 shrink-0 rounded-lg bg-slate-100 flex items-center justify-center text-[10px] text-slate-500 border border-slate-200 overflow-hidden">
                                        @if($product->image_path)
                                            <img src="{{ $product->image_path }}" alt="{{ $product->name }}"
                                                class="h-full w-full object-cover">
                                        @else
                                            {{ strtoupper(substr($product->name, 0, 2)) }}
                                        @endif
                                    </div>

                                    <div class="flex flex-col">
                                        {{-- Nama Produk --}}
                                        <span
                                            class="text-sm font-semibold text-slate-900 group-hover:text-teal-700 transition-colors line-clamp-1">
                                            {{ $product->name }}
                                        </span>

                                        {{-- Lokasi Rak --}}
                                        <div class="flex items-center gap-1 mt-0.5 text-xs text-slate-500">
                                            <x-lucide-map-pin class="w-3 h-3 text-slate-400" />
                                            <span>
                                                {{ $product->rack_location ?: 'No Rak' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Kategori --}}
                            <td class="px-3 py-3 align-top text-xs text-slate-600">
                                {{ $product->category?->name ?? '-' }}
                            </td>

                            {{-- Supplier --}}
                            <td class="px-3 py-3 align-top text-xs text-slate-600">
                                {{ $product->supplier?->name ?? '-' }}
                            </td>

                            {{-- Stok --}}
                            <td class="px-3 py-3 align-top text-right tabular-nums">
                                @php
                                    $isLow = $product->current_stock <= $product->min_stock && $product->current_stock > 0;
                                    $isOut = $product->current_stock == 0;
                                @endphp

                                <div class="flex flex-col items-end gap-0.5">
                                    <span @class([
                                        'text-[13px] font-bold leading-none',
                                        'text-slate-700' => !$isLow && !$isOut,
                                        'text-amber-600' => $isLow,
                                        'text-rose-600' => $isOut,
                                    ])>
                                        {{ $product->current_stock }}
                                        <span class="text-[10px] font-normal text-slate-500 ml-0.5 uppercase">
                                            {{ $product->unit }}
                                        </span>
                                    </span>

                                    <span class="text-[11px] text-slate-400 font-medium mt-0.5">
                                        Min: {{ $product->min_stock }}
                                    </span>
                                </div>
                            </td>

                            {{-- Harga --}}
                            <td
                                class="px-3 py-3 align-top text-right text-sm font-medium text-slate-700 tabular-nums whitespace-nowrap">
                                Rp {{ number_format($product->sale_price, 0, ',', '.') }}
                            </td>

                            {{-- Action --}}
                            <td class="px-3 py-3 align-top text-right relative" @click.stop>
                                @can('create', \App\Models\Product::class) {{-- Cek permission --}}
                                    <div x-data="{ open: false }" class="relative inline-block text-left">

                                        {{-- Tombol Titik Tiga --}}
                                        <button @click="open = !open" @click.outside="open = false" type="button"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-200 transition-colors">
                                            <x-lucide-more-vertical class="h-4 w-4" />
                                        </button>

                                        {{-- Dropdown Menu --}}
                                        <div x-show="open" x-transition:enter="transition ease-out duration-100"
                                            x-transition:enter-start="opacity-0 scale-95"
                                            x-transition:enter-end="opacity-100 scale-100"
                                            x-transition:leave="transition ease-in duration-75"
                                            x-transition:leave-start="opacity-100 scale-100"
                                            x-transition:leave-end="opacity-0 scale-95"
                                            class="absolute right-0 mt-2 w-40 origin-top-right rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-50"
                                            style="display: none;">
                                            <div class="py-1 divide-y divide-slate-100">

                                                {{-- Edit --}}
                                                @can('update', $product)
                                                    <a href="{{ route('products.edit', $product) }}"
                                                        class="group flex items-center px-4 py-2 text-[12px] text-slate-700 hover:bg-slate-50">
                                                        <x-lucide-pencil
                                                            class="mr-3 h-3.5 w-3.5 text-slate-400 group-hover:text-teal-600" />
                                                        Edit Produk
                                                    </a>
                                                @endcan

                                                {{-- Barcode --}}
                                                @can('viewBarcode', $product)
                                                    <a href="{{ route('products.barcode.label', $product) }}" target="_blank"
                                                        class="group flex items-center px-4 py-2 text-[12px] text-slate-700 hover:bg-slate-50">
                                                        <x-lucide-printer
                                                            class="mr-3 h-3.5 w-3.5 text-slate-400 group-hover:text-indigo-600" />
                                                        Cetak Barcode
                                                    </a>
                                                @endcan

                                                {{-- Hapus --}}
                                                @can('delete', $product)
                                                    <button type="button"
                                                        @click="$dispatch('open-delete-modal', { 
                                                            action: '{{ route('products.destroy', $product) }}',
                                                            title: 'Hapus Produk',
                                                            itemName: @js($product->name)
                                                        })"
                                                        class="group flex w-full items-center px-4 py-2 text-[12px] text-red-600 hover:bg-red-50"
                                                    >
                                                        <x-lucide-trash-2
                                                            class="mr-3 h-3.5 w-3.5 text-red-400 group-hover:text-red-600" />
                                                        Hapus
                                                    </button>
                                                @endcan
                                            </div>
                                        </div>
                                    </div>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-3 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="h-12 w-12 rounded-full bg-slate-100 flex items-center justify-center mb-3">
                                        <x-lucide-box class="h-6 w-6 text-slate-400" />
                                    </div>
                                    <p class="text-sm font-medium text-slate-900">Belum ada produk</p>
                                    <p class="text-xs text-slate-500 mt-1">Mulai dengan menambahkan produk baru ke gudang.</p>
                                    @can('create', \App\Models\Product::class)
                                        <a href="{{ route('products.create') }}"
                                            class="mt-4 inline-flex items-center rounded-lg bg-teal-600 px-3 py-2 text-xs font-semibold text-white hover:bg-teal-700">
                                            <x-lucide-plus class="h-3.5 w-3.5 mr-1.5" />
                                            Tambah Produk
                                        </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($products->hasPages() || $products->total() > 0)
            <x-advanced-pagination :paginator="$products" />
        @endif

        <x-confirm-delete-modal />
@endsection