@extends('layouts.app')

@section('title', 'Products')

@section('page-header')
<div class="flex flex-col">
    <h1 class="text-base font-semibold text-slate-900">Inventory & Products</h1>
    <p class="text-xs text-slate-500">Kelola master produk gudang Warenesia.</p>
</div>
@endsection

@section('content')
<div class="space-y-4">
    @if(session('status'))
    <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-800">
        {{ session('status') }}
    </div>
    @endif

    {{-- Toolbar --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <form method="GET" action="{{ route('admin.products.index') }}" class="flex-1 max-w-sm">
            <div class="relative">
                <input type="text" name="q" placeholder="Cari produk atau SKU..."
                    value="{{ $search }}"
                    class="w-full rounded-lg border-slate-200 pl-8 pr-3 py-2 text-xs focus:border-teal-500 focus:ring-teal-500">
                <x-lucide-search class="h-3 w-3 text-slate-400 absolute left-2 top-2.5" />
            </div>
        </form>

        <a href="{{ route('admin.products.create') }}"
            class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-3 py-2 text-xs font-semibold text-white hover:bg-teal-700">
            <x-lucide-plus class="h-3 w-3" />
            Tambah Produk
        </a>
    </div>

    {{-- Tabel --}}
    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <table class="min-w-full text-xs">
            <thead class="bg-slate-50 text-slate-500">
                <tr>
                    <th class="px-3 py-2 text-left font-medium">SKU</th>
                    <th class="px-3 py-2 text-left font-medium">Produk</th>
                    <th class="px-3 py-2 text-left font-medium">Kategori</th>
                    <th class="px-3 py-2 text-left font-medium">Supplier</th>
                    <th class="px-3 py-2 text-right font-medium">Stok</th>
                    <th class="px-3 py-2 text-right font-medium">Harga Jual</th>
                    <th class="px-3 py-2 text-right font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($products as $product)
                <tr class="hover:bg-slate-50/70">
                    <td class="px-3 py-2 align-top font-mono text-[11px] text-slate-600">
                        {{ $product->sku }}
                    </td>
                    <td class="px-3 py-2 align-top">
                        <div class="flex items-center gap-2">
                            <div class="h-8 w-8 rounded-lg bg-slate-100 flex items-center justify-center text-[10px] text-slate-500">
                                @if($product->image_path)
                                <img src="{{ $product->image_path }}" alt="{{ $product->name }}" class="h-8 w-8 rounded-lg object-cover">
                                @else
                                {{ strtoupper(substr($product->name, 0, 2)) }}
                                @endif
                            </div>
                            <div class="flex flex-col">
                                <span class="text-[11px] font-medium text-slate-900">
                                    {{ $product->name }}
                                </span>
                                <span class="text-[10px] text-slate-500">
                                    {{ $product->unit }} • Rak: {{ $product->rack_location ?: '-' }}
                                </span>
                            </div>
                        </div>
                    </td>
                    <td class="px-3 py-2 align-top text-[11px] text-slate-700">
                        {{ $product->category?->name ?? '-' }}
                    </td>
                    <td class="px-3 py-2 align-top text-[11px] text-slate-700">
                        {{ $product->supplier?->name ?? '-' }}
                    </td>
                    <td class="px-3 py-2 align-top text-right text-[11px]">
                        @php
                        $isLow = $product->current_stock <= $product->min_stock && $product->current_stock > 0;
                            $isOut = $product->current_stock == 0;
                            @endphp
                            <span @class([ 'inline-flex items-center rounded-full px-2 py-0.5 gap-1' , 'bg-emerald-50 text-emerald-700'=> !$isLow && !$isOut,
                                'bg-amber-50 text-amber-700' => $isLow,
                                'bg-red-50 text-red-700' => $isOut,
                                ])>
                                <span class="font-semibold">
                                    {{ $product->current_stock }}
                                </span>
                                <span class="text-[10px] text-slate-500">
                                    / {{ $product->min_stock }} min
                                </span>
                            </span>
                    </td>
                    <td class="px-3 py-2 align-top text-right text-[11px] text-slate-800">
                        Rp {{ number_format($product->sale_price, 0, ',', '.') }}
                    </td>
                    <td class="px-3 py-2 align-top text-right">
                        <div class="inline-flex items-center gap-1">
                            <a href="{{ route('admin.products.show', $product) }}"
                                class="inline-flex items-center rounded-md border border-slate-200 px-2 py-1 text-[10px] text-slate-600 hover:bg-slate-50">
                                Detail
                            </a>
                            <a href="{{ route('admin.products.edit', $product) }}"
                                class="inline-flex items-center rounded-md border border-slate-200 px-2 py-1 text-[10px] text-teal-700 hover:bg-teal-50">
                                Edit
                            </a>
                            <form action="{{ route('admin.products.destroy', $product) }}"
                                method="POST"
                                onsubmit="return confirm('Hapus produk ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="inline-flex items-center rounded-md border border-red-200 px-2 py-1 text-[10px] text-red-600 hover:bg-red-50">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-3 py-6 text-center text-[11px] text-slate-500">
                        Belum ada produk. Tambahkan produk pertama Anda.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="flex items-center justify-between text-[11px] text-slate-500">
        <div>
            Menampilkan
            <span class="font-semibold">{{ $products->firstItem() ?? 0 }}</span>
            -
            <span class="font-semibold">{{ $products->lastItem() ?? 0 }}</span>
            dari
            <span class="font-semibold">{{ $products->total() }}</span>
            produk
        </div>
        <div>
            {{ $products->links() }}
        </div>
    </div>
</div>
@endsection