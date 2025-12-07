<div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
    <h3 class="text-sm font-bold text-slate-800 mb-4">Foto Produk *</h3>

    <div @class([
        'relative w-full aspect-square rounded-xl border-2 bg-slate-50 flex flex-col items-center justify-center text-center overflow-hidden',
        'border-dashed border-slate-300 hover:bg-slate-100 cursor-pointer group transition' => !$readonly,
        'border-slate-100' => $readonly
    ])>

        @unless($readonly)
            <input type="file" name="image" accept="image/*" @if(empty($product->image_path)) required @endif
                class="absolute inset-0 w-full h-full opacity-0 z-50 cursor-pointer" @change="handleImage">
        @endunless

        <div x-show="!imagePreview" class="p-4">
            <div @class([
                'mx-auto w-12 h-12 bg-white rounded-full shadow-sm border border-slate-200 flex items-center justify-center mb-3',
                'group-hover:scale-110 transition-transform' => !$readonly
            ])>
                @if($readonly)
                    <x-lucide-image class="w-6 h-6 text-slate-300" />
                @else
                    <x-lucide-upload-cloud class="w-6 h-6 text-teal-600" />
                @endif
            </div>

            @unless($readonly)
                <p class="text-xs font-medium text-slate-700">Klik untuk upload</p>
                <p class="text-[10px] text-slate-400 mt-1">atau drag file ke sini</p>
            @else
                <p class="text-xs font-medium text-slate-400">Tidak ada foto</p>
            @endunless
        </div>

        <div x-show="imagePreview" x-cloak class="absolute inset-0 w-full h-full bg-white">
            <img :src="imagePreview" class="w-full h-full object-cover">

            @unless($readonly)
                <div
                    class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center z-20">
                    <span class="text-white text-xs font-medium bg-black/50 px-3 py-1 rounded-full">Ganti Foto</span>
                </div>
            @endunless
        </div>
    </div>

    @unless($readonly)
        <p class="text-[10px] text-slate-400 mt-2 text-center">Format: JPG, PNG. Maks 2MB.</p>
        <x-input-error class="mt-2" :messages="$errors->get('image')" />
    @endunless
</div>