@php
    /** @var \App\Models\Category $category */
    $category = $category ?? new \App\Models\Category();
    $readonly = $readonly ?? false;
    $imageUrl = $category->image_path ? \Illuminate\Support\Facades\Storage::url($category->image_path) : null;
@endphp

<div
    class="space-y-4"
    x-data="{
        preview: @js($imageUrl),
        handleImage(event) {
            const file = event.target.files[0];
            if (file) {
                this.preview = URL.createObjectURL(file);
            }
        },
        clearImage() {
            this.preview = null;
            if (this.$refs.imagePath) {
                this.$refs.imagePath.value = '';
            }
        }
    }"
>
    <div class="space-y-3">
        <p class="text-sm font-semibold text-slate-700">Gambar Kategori (opsional)</p>
        <div class="border border-dashed border-slate-300 rounded-xl p-4 bg-slate-50">
            <div class="aspect-video rounded-lg bg-white border border-slate-200 flex items-center justify-center overflow-hidden">
                <template x-if="preview">
                    <img :src="preview" alt="Preview" class="h-full w-full object-cover">
                </template>
                <template x-if="!preview">
                    <div class="text-center text-slate-400 text-sm">
                        <x-lucide-image class="w-8 h-8 mx-auto mb-2" />
                        <p>Belum ada gambar</p>
                    </div>
                </template>
            </div>

            @unless($readonly)
                <div class="flex items-center gap-2 mt-3">
                    <x-action-button type="button" variant="secondary" class="w-full justify-center" x-on:click="$refs.imagePath.click()">
                        Pilih Gambar
                    </x-action-button>
                    <x-action-button type="button" variant="ghost" class="w-auto px-3" x-on:click="clearImage()">
                        Reset
                    </x-action-button>
                </div>
                <input
                    x-ref="imagePath"
                    type="file"
                    name="image_path"
                    class="hidden"
                    accept="image/*"
                    @change="handleImage"
                />
                <x-input-error class="mt-2" :messages="$errors->get('image_path')" />
            @endunless
        </div>
    </div>

    <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
        <p class="text-xs text-slate-600 leading-relaxed">
            Pastikan prefix berbeda untuk setiap kategori. Sistem akan menggunakannya sebagai awalan SKU produk secara otomatis.
        </p>
    </div>
</div>
