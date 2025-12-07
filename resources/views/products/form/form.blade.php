@php
    /** @var \App\Models\Product $product */
    $readonly = $readonly ?? false; 
    $target = $product ?? new \App\Models\Product();
    $categories = $categories ?? collect();
    $suppliers = $suppliers ?? collect();
    $units = $units ?? collect();

    $categoryOptions = $categoryOptions ?? $categories->pluck('name', 'id')->toArray();

    $supplierOptions = $supplierOptions ?? $suppliers->mapWithKeys(function ($item) {
        return [$item->id => $item->name . ($item->contact_person ? ' (' . $item->contact_person . ')' : '')];
    })->toArray();

    $unitOptions = $unitOptions ?? $units->pluck('name', 'id')->toArray();

    $categoryPrefixes = $categoryPrefixes ?? $categories->pluck('sku_prefix', 'id')->filter()->toArray();
    $initialSku = old('sku', $target->sku);
    $initialSkuNumber = is_string($initialSku)
        ? preg_replace('/^[^-]*-/', '', $initialSku)
        : '';
    $imageUrl = $target->image_path
        ? \Illuminate\Support\Facades\Storage::url($target->image_path)
        : null;
@endphp

<div x-data="productForm({
        readonly: @js($readonly),
        initialSku: @js($initialSku),
        initialSkuNumber: @js($initialSkuNumber),
        initialCategory: @js(old('category_id', $target->category_id)),
        initialUnit: @js(old('unit_id', $target->unit_id)),
        categories: @js($categoryOptions),
        units: @js($unitOptions),
        categoryPrefixes: @js($categoryPrefixes),
        categoryEndpoint: @js(route('categories.quick-store')),
        unitEndpoint: @js(route('units.quick-store')),
        csrf: @js(csrf_token()),
        initialImage: @js($imageUrl),
    })"
    class="pb-20 space-y-6"
>
    @if (!$readonly && $errors->any())
        <div class="mb-6 rounded-2xl border border-red-100 bg-red-50 p-4">
            <div class="flex items-start gap-3">
                <x-lucide-alert-triangle class="h-5 w-5 text-red-600 shrink-0" />
                <div>
                    <h3 class="text-sm font-bold text-red-800">Mohon periksa kembali inputan Anda</h3>
                    <ul class="mt-1 list-disc list-inside text-xs text-red-700 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="flex flex-col md:grid md:grid-cols-2 lg:grid-cols-3 gap-6 items-stretch">
        
        {{-- KOLOM KIRI --}}
        <div class="order-1 lg:col-span-2 flex flex-col gap-6">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm divide-y divide-slate-100 overflow-hidden h-full">
                
                @include('products.form.general', ['product' => $target, 'readonly' => $readonly])
                @include('products.form.pricing', ['product' => $target, 'readonly' => $readonly])
                @include('products.form.inventory', ['product' => $target, 'readonly' => $readonly])

            </div>
        </div>

        {{-- KOLOM KANAN --}}
        <div class="order-2 flex flex-col gap-6 h-full">
            <div class="shrink-0 order-2 md:order-1">
                @include('products.form.media', ['product' => $target, 'readonly' => $readonly])
            </div>
            
            <div class="flex-1 min-h-0 order-1 md:order-2">
                @include('products.form.sidebar', [
                    'product' => $target,
                    'readonly' => $readonly,
                    'categoryOptions' => $categoryOptions,
                    'supplierOptions' => $supplierOptions,
                    'unitOptions' => $unitOptions,
                ])
            </div>
        </div>

    </div>

    {{-- Quick Add Category Modal --}}
    <x-modal name="quick-category-modal" maxWidth="lg">
        <div class="p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-semibold text-slate-900">Tambah Kategori Cepat</h3>
                <button type="button" class="text-slate-400 hover:text-slate-600" x-on:click="$dispatch('close-modal', 'quick-category-modal')">
                    <x-lucide-x class="w-5 h-5" />
                </button>
            </div>

            <div class="space-y-4">
                <div class="space-y-2">
                    <x-input-label value="Nama Kategori" class="text-sm font-semibold text-slate-700" />
                    <input type="text" x-model="categoryQuick.name" x-on:input="syncCategoryPrefix()" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500" placeholder="Contoh: Peralatan Dapur">
                    <template x-if="errors.name">
                        <p class="text-xs text-red-600 mt-1" x-text="errors.name[0]"></p>
                    </template>
                </div>
                <div class="space-y-2">
                    <x-input-label value="SKU Prefix" class="text-sm font-semibold text-slate-700" />
                    <input type="text" x-model="categoryQuick.prefix" readonly class="w-full rounded-xl border-slate-200 text-sm shadow-sm bg-slate-50 tracking-[0.2em] font-semibold uppercase">
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <x-action-button type="button" variant="secondary" x-on:click="$dispatch('close-modal', 'quick-category-modal')">Batal</x-action-button>
                <x-action-button type="button" variant="primary" icon="save" x-on:click="saveQuickCategory()">Simpan</x-action-button>
            </div>
        </div>
    </x-modal>

    {{-- Quick Add Unit Modal --}}
    <x-modal name="quick-unit-modal" maxWidth="md">
        <div class="p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-semibold text-slate-900">Tambah Satuan Cepat</h3>
                <button type="button" class="text-slate-400 hover:text-slate-600" x-on:click="$dispatch('close-modal', 'quick-unit-modal')">
                    <x-lucide-x class="w-5 h-5" />
                </button>
            </div>

            <div class="space-y-2">
                <x-input-label value="Nama Satuan" class="text-sm font-semibold text-slate-700" />
                <input type="text" x-model="unitQuick.name" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500" placeholder="Contoh: Box, Pcs, Liter">
                <template x-if="errors.name">
                    <p class="text-xs text-red-600 mt-1" x-text="errors.name[0]"></p>
                </template>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <x-action-button type="button" variant="secondary" x-on:click="$dispatch('close-modal', 'quick-unit-modal')">Batal</x-action-button>
                <x-action-button type="button" variant="primary" icon="save" x-on:click="saveQuickUnit()">Simpan</x-action-button>
            </div>
        </div>
    </x-modal>
</div>
