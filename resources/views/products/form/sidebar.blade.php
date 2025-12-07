@php
    /** @var \App\Models\Product $product */
    $product = $product ?? new \App\Models\Product();
    $categoryOptions = (array) $categoryOptions;
    $supplierOptions = (array) $supplierOptions;
    $unitOptions = (array) $unitOptions;
@endphp

<div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm h-full flex flex-col">
    <h3 class="text-sm font-bold text-slate-800 mb-6 flex items-center gap-2 border-b border-slate-100 pb-4">
        Atribut & Relasi
    </h3>

    <div class="space-y-6 flex-1">

        {{-- Kategori --}}
        <div class="relative space-y-2">
            <x-input-label for="category_id" value="Kategori *" class="mb-1.5 text-sm font-semibold text-slate-700" />

            <div class="flex items-center gap-2">
                <div class="flex-1 min-w-0">
                    <x-custom-select
                    name="category_id"
                    x-ref="categorySelect"
                    :options="$categoryOptions"
                    :value="old('category_id', $product->category_id)"
                    placeholder="Pilih kategori..."
                    x-model="form.category_id"
                    x-on:input="updateSkuHint()" />
                </div>

                <x-quick-action-button title="Tambah Kategori" class="shrink-0"
                    x-on:click.prevent="openQuickCategory()" />
            </div>
            
            <x-input-error class="mt-1" :messages="$errors->get('category_id')" />
        </div>

        {{-- Supplier --}}
        <div class="relative space-y-2">
            <x-input-label for="supplier_id" value="Supplier Utama *"
                class="mb-1.5 text-sm font-semibold text-slate-700" />

            <div class="flex items-center gap-2">
                <div class="flex-1 min-w-0">
                    <x-custom-select name="supplier_id" :options="$supplierOptions"
                        :value="old('supplier_id', $product->supplier_id)" placeholder="Cari supplier..." />
                </div>
            </div>

            <x-input-error class="mt-1" :messages="$errors->get('supplier_id')" />
        </div>

        {{-- Satuan --}}
        <div class="relative space-y-2">
            <x-input-label for="unit_id" value="Satuan Dasar *" class="mb-1.5 text-sm font-semibold text-slate-700" />

            <div class="flex items-center gap-2">
                <div class="flex-1 min-w-0">
                    <x-custom-select name="unit_id" x-ref="unitSelect" :options="$unitOptions"
                        :value="old('unit_id', $product->unit_id)" placeholder="Pilih satuan..." dropUp />
                </div>

                <x-quick-action-button title="Tambah Satuan" class="shrink-0" x-on:click.prevent="openQuickUnit()" />
            </div>
            
            <x-input-error class="mt-1" :messages="$errors->get('unit_id')" />
        </div>
    </div>
</div>