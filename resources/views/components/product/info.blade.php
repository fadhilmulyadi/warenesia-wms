@props(['product'])

<div class="flex items-center gap-3">
    {{-- Thumbnail --}}
    <x-thumbnail :src="$product->image_path" :alt="$product->name" />

    <div class="flex flex-col">
        {{-- Nama Produk --}}
        <span class="text-xs font-semibold text-slate-900 group-hover:text-teal-700 transition-colors line-clamp-1">
            {{ $product->name }}
        </span>

        {{-- Lokasi Rak --}}
        <div class="flex items-center gap-1 mt-0.5 text-[10px] text-slate-500">
            <x-lucide-map-pin class="w-3 h-3 text-slate-400" />
            <span>
                {{ $product->rack_location ?: 'No Rak' }}
            </span>
        </div>
    </div>
</div>