@csrf

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    {{-- Nama & SKU --}}
    <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">Nama Produk<span class="text-red-500">*</span></label>
        <input type="text" name="name"
               value="{{ old('name', $product->name) }}"
               class="w-full rounded-lg border-slate-200 text-sm focus:border-teal-500 focus:ring-teal-500">
        @error('name')
            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">SKU<span class="text-red-500">*</span></label>
        <input type="text" name="sku"
               value="{{ old('sku', $product->sku) }}"
               class="w-full rounded-lg border-slate-200 text-sm focus:border-teal-500 focus:ring-teal-500">
        @error('sku')
            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Kategori & Supplier --}}
    <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">Kategori<span class="text-red-500">*</span></label>
        <select name="category_id"
                class="w-full rounded-lg border-slate-200 text-sm focus:border-teal-500 focus:ring-teal-500">
            <option value="">-- pilih kategori --</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}"
                    @selected(old('category_id', $product->category_id) == $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        @error('category_id')
            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">Supplier</label>
        <select name="supplier_id"
                class="w-full rounded-lg border-slate-200 text-sm focus:border-teal-500 focus:ring-teal-500">
            <option value="">-- tidak ada / langsung gudang --</option>
            @foreach($suppliers as $supplier)
                <option value="{{ $supplier->id }}"
                    @selected(old('supplier_id', $product->supplier_id) == $supplier->id)>
                    {{ $supplier->name }}
                </option>
            @endforeach
        </select>
        @error('supplier_id')
            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Harga --}}
    <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">Harga Beli (Rp)<span class="text-red-500">*</span></label>
        <input type="number" step="0.01" name="purchase_price"
               value="{{ old('purchase_price', $product->purchase_price) }}"
               class="w-full rounded-lg border-slate-200 text-sm focus:border-teal-500 focus:ring-teal-500">
        @error('purchase_price')
            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">Harga Jual (Rp)<span class="text-red-500">*</span></label>
        <input type="number" step="0.01" name="sale_price"
               value="{{ old('sale_price', $product->sale_price) }}"
               class="w-full rounded-lg border-slate-200 text-sm focus:border-teal-500 focus:ring-teal-500">
        @error('sale_price')
            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Stok --}}
    <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">Stok Minimum<span class="text-red-500">*</span></label>
        <input type="number" name="min_stock"
               value="{{ old('min_stock', $product->min_stock) }}"
               class="w-full rounded-lg border-slate-200 text-sm focus:border-teal-500 focus:ring-teal-500">
        @error('min_stock')
            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">Stok Saat Ini<span class="text-red-500">*</span></label>
        <input type="number" name="current_stock"
               value="{{ old('current_stock', $product->current_stock) }}"
               class="w-full rounded-lg border-slate-200 text-sm focus:border-teal-500 focus:ring-teal-500">
        @error('current_stock')
            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Unit & Rak --}}
    <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">Unit<span class="text-red-500">*</span></label>
        <input type="text" name="unit"
               value="{{ old('unit', $product->unit ?: 'pcs') }}"
               class="w-full rounded-lg border-slate-200 text-sm focus:border-teal-500 focus:ring-teal-500">
        @error('unit')
            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">Lokasi Rak</label>
        <input type="text" name="rack_location"
               value="{{ old('rack_location', $product->rack_location) }}"
               class="w-full rounded-lg border-slate-200 text-sm focus:border-teal-500 focus:ring-teal-500">
        @error('rack_location')
            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Image path (optional) --}}
    <div class="md:col-span-2">
        <label class="block text-xs font-medium text-slate-600 mb-1">Image path (opsional)</label>
        <input type="text" name="image_path"
               placeholder="contoh: /storage/products/sku-001.jpg"
               value="{{ old('image_path', $product->image_path) }}"
               class="w-full rounded-lg border-slate-200 text-sm focus:border-teal-500 focus:ring-teal-500">
        @error('image_path')
            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Deskripsi --}}
    <div class="md:col-span-2">
        <label class="block text-xs font-medium text-slate-600 mb-1">Deskripsi</label>
        <textarea name="description" rows="3"
                  class="w-full rounded-lg border-slate-200 text-sm focus:border-teal-500 focus:ring-teal-500">{{ old('description', $product->description) }}</textarea>
        @error('description')
            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="mt-6 flex items-center justify-between">
    <a href="{{ route('admin.products.index') }}"
       class="inline-flex items-center text-xs font-medium text-slate-500 hover:text-slate-700">
        ← Kembali ke daftar produk
    </a>

    <button type="submit"
            class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2 text-xs font-semibold text-white hover:bg-teal-700">
        @if($mode === 'create')
            Simpan Produk
        @else
            Update Produk
        @endif
    </button>
</div>
