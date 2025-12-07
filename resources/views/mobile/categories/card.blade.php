<x-mobile.card>
    {{-- HEADER --}}
    <div class="flex items-start justify-between gap-3">
        <div class="text-base font-semibold text-slate-900 leading-snug line-clamp-2">
            {{ $item->name }}
        </div>
        {{-- Badge Produk Count --}}
        <x-badge variant="neutral" class="shrink-0 bg-slate-100 text-slate-600 border-slate-200">
            {{ $item->products_count }} Produk
        </x-badge>
    </div>

    {{-- BODY CONTENT --}}
    <div class="mt-3 space-y-1.5">
        {{-- SKU --}}
        <div class="flex items-center gap-2">
            <span class="text-xs font-medium text-slate-400 uppercase tracking-wide">Prefix</span>
            <span class="text-sm font-mono font-medium text-slate-700 bg-slate-50 px-1.5 rounded">
                {{ $item->sku_prefix }}
            </span>
        </div>

        {{-- Deskripsi --}}
        @if($item->description)
            <p class="text-sm text-slate-500 leading-relaxed line-clamp-2">
                {{ $item->description }}
            </p>
        @else
            <p class="text-sm text-slate-400 italic">Tidak ada deskripsi</p>
        @endif
    </div>

    {{-- ACTION BUTTONS --}}
    <div class="mt-4 pt-3 flex gap-3 border-t border-slate-50">
        <a href="{{ route('categories.edit', $item) }}"
           class="flex-1 h-11 rounded-xl bg-slate-100 text-slate-700 text-sm font-medium flex items-center justify-center gap-2 hover:bg-slate-200 active:scale-95 transition">
            <x-lucide-pencil class="w-4 h-4" /> Edit
        </a>

        {{-- Tombol Hapus --}}
        @if(auth()->user()->can('delete', $item))
            <button x-on:click="$dispatch('open-delete-modal', { 
                        action: '{{ route('categories.destroy', $item) }}',
                        title: 'Hapus Kategori',
                        message: 'Yakin ingin menghapus kategori ini?',
                        itemName: '{{ addslashes($item->name) }}'
                    })"
                class="w-11 h-11 flex items-center justify-center rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 active:scale-95 transition">
                <x-lucide-trash class="w-5 h-5" />
            </button>
        @endif
    </div>
</x-mobile.card>