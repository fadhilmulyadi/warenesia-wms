@php
    /** @var \App\Models\Product $product */
    $product = $product ?? new \App\Models\Product();
    $readonly = $readonly ?? false;
@endphp

<div class="p-6">
    <h3 class="text-sm font-bold text-slate-800 mb-4 uppercase tracking-wider border-b border-slate-100 pb-2">Informasi
        Dasar</h3>

    <div class="space-y-4">
        {{-- Nama Produk --}}
        <div>
            <x-input-label for="name" value="Nama Produk *" class="text-sm font-semibold text-slate-700" />
            <x-text-input id="name" name="name" type="text" :value="old('name', $product->name)" required
                placeholder="Contoh: Kemeja Flannel Premium" :disabled="$readonly"
                class="mt-1 block w-full {{ $readonly ? 'bg-slate-50 text-slate-500 border-slate-200 cursor-not-allowed focus:ring-0' : 'border-slate-300 focus:border-teal-500 focus:ring-teal-500 shadow-sm' }}" />
            @unless($readonly)
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            @endunless
        </div>

        {{-- SKU --}}
        <div class="space-y-1">
            <div class="flex justify-between items-center mb-1">
                <x-input-label for="sku" value="SKU (Stock Keeping Unit)"
                    class="text-sm font-semibold text-slate-700" />
                <span class="text-[11px] text-slate-500">Dibuat otomatis dari kategori</span>
            </div>

            <input type="text" id="sku" name="sku" x-model="form.sku" :readonly="true"
                :class="'bg-slate-50 text-slate-700 cursor-not-allowed border-slate-200 focus:border-slate-200 focus:ring-0'"
                class="block w-full rounded-xl text-sm font-mono transition-colors duration-200"
                x-bind:placeholder="skuHint">
            @unless($readonly)
                <x-input-error class="mt-1.5" :messages="$errors->get('sku')" />
            @endunless
        </div>

        {{-- Deskripsi --}}
        <div>
            <x-input-label for="description" value="Deskripsi *" class="text-sm font-semibold text-slate-700" />
            <textarea id="description" name="description" rows="4" required @disabled($readonly) @class([
                'mt-1 block w-full rounded-xl border-slate-300 shadow-sm sm:text-sm placeholder:text-slate-400',
                'bg-slate-50 text-slate-500 border-slate-200 cursor-not-allowed resize-none focus:ring-0' => $readonly,
                'focus:border-teal-500 focus:ring-teal-500' => !$readonly
            ])
                placeholder="Detail spesifikasi produk...">{{ old('description', $product->description) }}</textarea>
        </div>
    </div>
</div>
