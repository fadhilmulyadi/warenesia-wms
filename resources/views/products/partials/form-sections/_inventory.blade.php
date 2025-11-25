<div class="p-6">
    <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-2">
        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Inventaris</h3>
        
        {{-- Status Stok Visual --}}
        @if($product->exists)
            @php
                $stock = (int) $product->current_stock;
                $min = (int) $product->min_stock;
            @endphp
            @if($stock <= 0)
                <span class="bg-red-100 text-red-700 text-[10px] font-bold px-2 py-1 rounded uppercase">Out of Stock</span>
            @elseif($stock <= $min)
                <span class="bg-amber-100 text-amber-700 text-[10px] font-bold px-2 py-1 rounded uppercase">Low Stock</span>
            @else
                <span class="bg-emerald-100 text-emerald-700 text-[10px] font-bold px-2 py-1 rounded uppercase">In Stock</span>
            @endif
        @endif
    </div>

    <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Stok --}}
            <div>
                <x-input-label for="current_stock" :value="$product->exists ? 'Stok Saat Ini (Terkunci) *' : 'Stok Awal *'" />
                
                <input 
                    type="number" 
                    id="current_stock"
                    name="current_stock" 
                    value="{{ old('current_stock', $product->current_stock) }}"
                    {{ $product->exists ? 'readonly' : '' }}
                    @class([
                        'mt-1 block w-full rounded-lg text-sm',
                        'bg-slate-100 text-slate-500 border-slate-200 cursor-not-allowed' => $product->exists,
                        'border-slate-300 focus:border-teal-500 focus:ring-teal-500' => !$product->exists
                    ])
                >
                @if($product->exists)
                    <p class="text-[10px] text-slate-400 mt-1 flex items-center gap-1">
                        <x-lucide-lock class="w-3 h-3"/> Stok dikelola via transaksi.
                    </p>
                @endif
                <x-input-error class="mt-2" :messages="$errors->get('current_stock')" />
            </div>

            {{-- Min Stok --}}
            <div>
                <x-input-label for="min_stock" value="Stok Minimum (Alert)" />
                <input 
                    type="number" 
                    id="min_stock"
                    name="min_stock" 
                    value="{{ old('min_stock', $product->min_stock) }}"
                    class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm"
                >
            </div>
        </div>

        {{-- Lokasi Rak --}}
        <div>
            <x-input-label for="rack_location" value="Lokasi Rak Gudang" />
            <div class="flex mt-1">
                <span class="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-slate-300 bg-slate-50 text-slate-500">
                    <x-lucide-map-pin class="w-4 h-4" />
                </span>
                <input 
                    type="text" 
                    id="rack_location"
                    name="rack_location" 
                    value="{{ old('rack_location', $product->rack_location) }}"
                    class="block w-full rounded-r-lg border-slate-300 focus:border-teal-500 focus:ring-teal-500 sm:text-sm uppercase placeholder:normal-case"
                    placeholder="Contoh: A-01-ROW-2"
                >
            </div>
        </div>
    </div>
</div>