@props(['product'])

<div class="flex items-center gap-3">
    {{-- Thumbnail --}}
    @php
        $imageUrl = $product->image_path
            ? \Illuminate\Support\Facades\Storage::url($product->image_path)
            : null;
    @endphp
    <x-thumbnail :src="$imageUrl" :alt="$product->name" />

    <div class="flex flex-col">
        {{-- Nama Produk --}}
        <span class="text-xs font-semibold text-slate-900 group-hover:text-teal-700 transition-colors line-clamp-1">
            {{ $product->name }}
        </span>
    </div>
</div>