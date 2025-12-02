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
        default => 'success',
    };

    $stockLabel = match ($stockStatus) {
        'out_of_stock' => 'Out of Stock',
        'low_stock' => 'Low Stock',
        default => 'In Stock',
    };
@endphp

<x-mobile.card>
    <div class="flex items-center justify-between">
        <div class="font-semibold text-slate-900">{{ $item->name }}</div>
        <x-badge :variant="$stockVariant">{{ $stockLabel }}</x-badge>
    </div>

    <div class="text-xs text-slate-500">
        {{ $item->sku }}
    </div>

    <div class="text-xs">
        <x-badge variant="neutral">{{ $item->category->name ?? '-' }}</x-badge>
    </div>

    <div class="pt-2 flex items-center justify-between text-xs text-slate-500 border-t border-slate-50 mt-1">
        <span>Harga: Rp {{ number_format($item->sale_price, 0, ',', '.') }}</span>
        <span>Stok: {{ $item->current_stock }} {{ $item->unit->name ?? '' }}</span>
    </div>

    <div class="pt-3 flex gap-2">
        <a href="{{ route('products.show', $item) }}"
            class="flex-1 h-9 rounded-lg bg-slate-100 text-slate-700 text-xs flex items-center justify-center gap-2 hover:bg-slate-200 transition">
            <x-lucide-eye class="w-4 h-4" /> Detail
        </a>

        <a href="{{ route('products.edit', $item) }}"
            class="w-9 h-9 flex items-center justify-center rounded-lg bg-teal-50 text-teal-600 hover:bg-teal-100 transition">
            <x-lucide-pencil class="w-4 h-4" />
        </a>

        @if(auth()->user()->can('delete', $item))
            <button x-on:click="$dispatch('open-delete-modal', { 
                            action: '{{ route('products.destroy', $item) }}',
                            title: 'Hapus Produk',
                            message: 'Yakin ingin menghapus produk ini?',
                            itemName: '{{ addslashes($item->name) }}'
                        })"
                class="w-9 h-9 flex items-center justify-center rounded-lg bg-rose-100 text-rose-600 hover:bg-rose-200 transition">
                <x-lucide-trash class="w-4 h-4" />
            </button>
        @endif
    </div>
</x-mobile.card>