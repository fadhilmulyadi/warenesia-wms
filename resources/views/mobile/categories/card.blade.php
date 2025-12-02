<x-mobile.card>
    <div class="flex items-center justify-between">
        <div class="font-semibold text-slate-900">{{ $item->name }}</div>
        <x-badge variant="neutral">{{ $item->products_count }} Produk</x-badge>
    </div>

    <div class="text-xs text-slate-500">
        {{ $item->sku_prefix }}
    </div>

    <div class="text-xs text-slate-500 mt-1">
        {{ Str::limit($item->description, 50) }}
    </div>

    <div class="pt-3 flex gap-2">
        <a href="{{ route('categories.edit', $item) }}"
            class="flex-1 h-9 rounded-lg bg-slate-100 text-slate-700 text-xs flex items-center justify-center gap-2 hover:bg-slate-200 transition">
            <x-lucide-pencil class="w-4 h-4" /> Edit
        </a>

        @if(auth()->user()->can('delete', $item))
            <button x-on:click="$dispatch('open-delete-modal', { 
                        action: '{{ route('categories.destroy', $item) }}',
                        title: 'Hapus Kategori',
                        message: 'Yakin ingin menghapus kategori ini?',
                        itemName: '{{ addslashes($item->name) }}'
                    })"
                class="w-9 h-9 flex items-center justify-center rounded-lg bg-rose-100 text-rose-600 hover:bg-rose-200 transition">
                <x-lucide-trash class="w-4 h-4" />
            </button>
        @endif
    </div>
</x-mobile.card>