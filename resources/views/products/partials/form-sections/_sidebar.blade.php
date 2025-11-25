@php
    $categoryOptions = $categories->pluck('name', 'id')->toArray();
    
    $supplierOptions = $suppliers->mapWithKeys(function ($item) {
        return [$item->id => $item->name . ($item->contact_person ? ' (' . $item->contact_person . ')' : '')];
    })->toArray();

    $units = ['Pcs', 'Box', 'Kg', 'Liter', 'Unit', 'Pack', 'Roll', 'Set', 'Lembar', 'Botol'];
    $unitOptions = array_combine($units, $units);
@endphp

<div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm h-full flex flex-col">
    <h3 class="text-sm font-bold text-slate-800 mb-6 flex items-center gap-2 border-b border-slate-100 pb-4">
        Atribut & Relasi
    </h3>
    
    <div class="space-y-6 flex-1">
        
        {{-- Kategori --}}
        <div class="relative">
            <x-input-label for="category_id" value="Kategori" class="mb-1.5" />
            
            <div class="flex items-center gap-2">
                <x-custom-select 
                    name="category_id" 
                    class="flex-1"
                    :options="$categoryOptions" 
                    :value="old('category_id', $product->category_id)" 
                    placeholder="Pilih kategori..."
                />

                <x-quick-action-button 
                    title="Tambah Kategori Baru"
                    class="shrink-0"
                />
            </div>

            <x-input-error class="mt-2" :messages="$errors->get('category_id')" />
        </div>

        {{-- Supplier --}}
        <div class="relative">
            <x-input-label for="supplier_id" value="Supplier Utama" class="mb-1.5" />
            
            <div class="flex items-center gap-2">
                <x-custom-select 
                    name="supplier_id" 
                    class="flex-1"
                    :options="$supplierOptions" 
                    :value="old('supplier_id', $product->supplier_id)" 
                    placeholder="Cari supplier..."
                />

                <x-quick-action-button 
                    title="Tambah Supplier Baru"
                    class="shrink-0"
                />
            </div>
            
            <p class="text-[10px] text-slate-400 mt-1.5 leading-relaxed">
                Kosongkan jika barang internal.
            </p>
            <x-input-error class="mt-2" :messages="$errors->get('supplier_id')" />
        </div>

        {{-- Satuan --}}
        <div class="relative">
            <x-input-label for="unit" value="Satuan Dasar" class="mb-1.5" />
            
            <div class="flex items-center gap-2">
                <x-custom-select 
                    name="unit" 
                    class="flex-1"
                    :options="$unitOptions" 
                    :value="old('unit', $product->unit)" 
                    placeholder="Pilih satuan..."
                />

                <x-quick-action-button 
                    title="Tambah Satuan Baru"
                    class="shrink-0"
                />
            </div>

            <x-input-error class="mt-2" :messages="$errors->get('unit')" />
        </div>
    </div>
</div>