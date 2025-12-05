@php
    $stockStatus = 'in_stock';
    if ($item->current_stock == 0) {
        $stockStatus = 'out_of_stock';
    } elseif ($item->current_stock <= $item->min_stock) {
        $stockStatus = 'low_stock';
    }

    $stockVariant = match ($stockStatus) {
        'out_of_stock' => 'danger',
        'low_stock' => 'warning',
        'in_stock' => 'success',
    };

    $stockLabel = match ($stockStatus) {
        'out_of_stock' => 'Out of Stock',
        'low_stock' => 'Low Stock',
        'in_stock' => 'In Stock',
    };
@endphp

<x-mobile.card>
    {{-- HEADER --}}
    <div class="flex items-start justify-between gap-3">
        {{-- Font Judul: Pastikan text-base (16px) agar jelas --}}
        <div class="text-base font-semibold text-slate-900 leading-snug line-clamp-2">
            {{ $item->name }}
        </div>
        <x-badge :variant="$stockVariant" class="shrink-0">
            {{ $stockLabel }}
        </x-badge>
    </div>

    {{-- META DATA --}}
    <div class="mt-2 flex items-center flex-wrap gap-2">
        {{-- SKU naik ke text-sm (14px) --}}
        <span class="text-sm text-slate-500 font-mono">{{ $item->sku }}</span>
        
        {{-- Badge Kategori --}}
        @if($item->category)
            <x-badge variant="neutral" class="text-[11px] px-2 py-0.5">
                {{ $item->category->name }}
            </x-badge>
        @endif
    </div>

    {{-- HARGA & STOK --}}
    <div class="mt-3 pt-3 flex items-center justify-between border-t border-slate-100">
        <span class="text-sm font-bold text-slate-900">
            Rp {{ number_format($item->sale_price, 0, ',', '.') }}
        </span>
        <span class="text-sm text-slate-500">
            Stok: <span class="font-medium text-slate-700">{{ $item->current_stock }}</span> {{ $item->unit->name ?? '' }}
        </span>
    </div>

    {{-- ACTION BUTTONS --}}
    <div class="mt-4 flex gap-3">
        {{-- Tombol Detail: Naik dari h-9 ke h-11 (44px) --}}
        <a href="{{ route('products.show', $item) }}"
           class="flex-1 h-11 rounded-xl bg-slate-100 text-slate-700 text-sm font-medium flex items-center justify-center gap-2 hover:bg-slate-200 active:scale-95 transition">
            <x-lucide-eye class="w-5 h-5" /> {{-- Icon naik ke w-5 h-5 --}}
            Detail
        </a>

        {{-- Tombol Edit --}}
        <a href="{{ route('products.edit', $item) }}"
           class="w-11 h-11 flex items-center justify-center rounded-xl bg-teal-50 text-teal-600 hover:bg-teal-100 active:scale-95 transition">
            <x-lucide-pencil class="w-5 h-5" />
        </a>

        {{-- Tombol Hapus --}}
        @if(auth()->user()->can('delete', $item))
            <button x-on:click="$dispatch('open-delete-modal', { 
                        action: '{{ route('products.destroy', $item) }}',
                        title: 'Hapus Produk',
                        message: 'Yakin ingin menghapus produk ini?',
                        itemName: '{{ addslashes($item->name) }}'
                    })"
                class="w-11 h-11 flex items-center justify-center rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 active:scale-95 transition">
                <x-lucide-trash class="w-5 h-5" />
            </button>
        @endif
    </div>
</x-mobile.card>