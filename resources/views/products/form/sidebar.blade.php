@php
    /** @var \App\Models\Product $product */
    $product = $product ?? new \App\Models\Product();
    $readonly = $readonly ?? false;
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
            <x-input-label for="category_id" value="Kategori" class="mb-1.5 text-sm font-semibold text-slate-700" />
            
            <div class="flex items-center gap-2">
                <x-custom-select 
                    name="category_id" 
                    class="flex-1"
                    x-ref="categorySelect"
                    :options="$categoryOptions" 
                    :value="old('category_id', $product->category_id)" 
                    placeholder="Pilih kategori..."
                    :disabled="$readonly"
                    x-model="form.category_id"
                    x-on:input="updateSkuHint()"
                />

                @unless($readonly)
                    <x-quick-action-button title="Tambah Kategori" class="shrink-0" x-on:click.prevent="openQuickCategory()" />
                @endunless
            </div>
            @unless($readonly)
                <x-input-error class="mt-1" :messages="$errors->get('category_id')" />
            @endunless
        </div>

        {{-- Supplier --}}
        <div class="relative space-y-2">
            <x-input-label for="supplier_id" value="Supplier Utama" class="mb-1.5 text-sm font-semibold text-slate-700" />
            
            <div class="flex items-center gap-2">
                <x-custom-select 
                    name="supplier_id" 
                    class="flex-1"
                    :options="$supplierOptions" 
                    :value="old('supplier_id', $product->supplier_id)" 
                    placeholder="Cari supplier..."
                    :disabled="$readonly"
                />
            </div>
            
            @unless($readonly)
                <p class="text-[10px] text-slate-400 leading-relaxed">
                    Kosongkan jika barang internal.
                </p>
                <x-input-error class="mt-1" :messages="$errors->get('supplier_id')" />
            @endunless
        </div>

        {{-- Satuan --}}
        <div class="relative space-y-2">
            <x-input-label for="unit_id" value="Satuan Dasar" class="mb-1.5 text-sm font-semibold text-slate-700" />
            
            <div class="flex items-center gap-2">
                <x-custom-select 
                    name="unit_id" 
                    class="flex-1"
                    x-ref="unitSelect"
                    :options="$unitOptions" 
                    :value="old('unit_id', $product->unit_id)" 
                    placeholder="Pilih satuan..."
                    :disabled="$readonly"
                    dropUp
                />

                @unless($readonly)
                    <x-quick-action-button title="Tambah Satuan" class="shrink-0" x-on:click.prevent="openQuickUnit()" />
                @endunless
            </div>
            @unless($readonly)
                <x-input-error class="mt-1" :messages="$errors->get('unit_id')" />
            @endunless
        </div>
    </div>
</div>
