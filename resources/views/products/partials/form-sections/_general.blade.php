<div class="p-6">
    <h3 class="text-sm font-bold text-slate-800 mb-4 uppercase tracking-wider border-b border-slate-100 pb-2">Informasi Dasar</h3>
    
    <div class="space-y-4">
        {{-- Nama Produk --}}
        <div>
            <x-input-label for="name" value="Nama Produk *" />
            <x-text-input 
                id="name" 
                name="name" 
                type="text" 
                :value="$product->name"
                required 
                placeholder="Contoh: Kemeja Flannel Premium"
                :disabled="$readonly"
                class="mt-1 block w-full {{ $readonly ? 'bg-slate-50 text-slate-500 border-slate-200 cursor-not-allowed focus:ring-0' : '' }}"
            />
            @unless($readonly)
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            @endunless
        </div>

        {{-- SKU --}}
        <div>
            <div class="flex justify-between items-center mb-1">
                <x-input-label for="sku" value="SKU (Stock Keeping Unit) *" />
                
                @if(!$product->exists && !$readonly)
                    <label class="flex items-center cursor-pointer gap-2 select-none">
                        <input type="checkbox" x-model="autoSku" class="w-3.5 h-3.5 text-teal-600 border-gray-300 rounded focus:ring-teal-500">
                        <span class="text-[11px] font-medium text-slate-500">Auto-generate SKU</span>
                    </label>
                @endif
            </div>
            
            <input 
                type="text" 
                id="sku"
                name="sku" 
                value="{{ old('sku', $product->sku) }}"
                @blur="checkSku()"
                :readonly="autoSku || isReadOnly"
                :class="autoSku || isReadOnly
                    ? 'bg-slate-50 text-slate-500 cursor-not-allowed border-slate-200 focus:border-slate-200 focus:ring-0' 
                    : 'bg-white border-slate-300 focus:border-teal-500 focus:ring-teal-500'"
                class="block w-full rounded-lg text-sm font-mono transition-colors duration-200"
                :placeholder="autoSku ? 'Kode akan dibuat otomatis oleh sistem' : 'Masukkan kode unik SKU'"
            >
            @unless($readonly)
                <x-input-error class="mt-2" :messages="$errors->get('sku')" />
            @endunless
        </div>

        {{-- Deskripsi --}}
        <div>
            <x-input-label for="description" value="Deskripsi" />
            <textarea 
                id="description"
                name="description" 
                rows="4" 
                @disabled($readonly)
                @class([
                    'mt-1 block w-full rounded-lg border-slate-300 shadow-sm sm:text-sm',
                    'bg-slate-50 text-slate-500 border-slate-200 cursor-not-allowed resize-none focus:ring-0' => $readonly,
                    'focus:border-teal-500 focus:ring-teal-500' => !$readonly
                ])
                placeholder="Detail spesifikasi produk..."
            >{{ old('description', $product->description) }}</textarea>
        </div>
    </div>
</div>