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
                <x-input-label for="current_stock" :value="$product->exists ? 'Stok Saat Ini (Terkunci) *' : 'Stok Awal *'" class="text-sm font-semibold text-slate-700" />
                
                <input 
                    type="number" 
                    id="current_stock"
                    name="current_stock" 
                    value="{{ old('current_stock', $product->current_stock) }}"
                    @if($product->exists || $readonly) readonly @endif
                    
                    @class([
                        'mt-1 block w-full rounded-lg text-sm',
                        'bg-slate-50 text-slate-500 border-slate-200 cursor-not-allowed focus:ring-0' => $product->exists || $readonly,
                        'border-slate-300 focus:border-teal-500 focus:ring-teal-500' => !$product->exists && !$readonly
                    ])
                >
                @if($product->exists)
                    <p class="text-[10px] text-slate-400 mt-1 flex items-center gap-1">
                        <x-lucide-lock class="w-3 h-3"/> Stok dikelola via transaksi.
                    </p>
                @endif
                @unless($readonly)
                    <x-input-error class="mt-2" :messages="$errors->get('current_stock')" />
                @endunless
            </div>

            {{-- Min Stok --}}
            <div>
                <x-input-label for="min_stock" value="Stok Minimum (Alert) *" class="text-sm font-semibold text-slate-700" />
                <input 
                    type="number" 
                    id="min_stock"
                    name="min_stock" 
                    value="{{ old('min_stock', $product->min_stock) }}"
                    required
                    @disabled($readonly)
                    @class([
                        'mt-1 block w-full rounded-lg text-sm',
                        'bg-slate-50 text-slate-500 border-slate-200 cursor-not-allowed focus:ring-0' => $readonly,
                        'border-slate-300 focus:border-teal-500 focus:ring-teal-500 shadow-sm' => !$readonly
                    ])
                >
            </div>
        </div>

        {{-- Lokasi Rak --}}
        <div
            x-data="rackLocationField('{{ old('rack_location', $product->rack_location ?? '') }}')"
            class="space-y-1"
        >
            <x-input-label for="rack_location" value="Lokasi Rak Gudang *" class="text-sm font-semibold text-slate-700" />
            <div class="flex mt-1">
                <span class="inline-flex items-center px-3 rounded-l-lg border border-r-0 border-slate-300 bg-slate-50 text-slate-500">
                    <x-lucide-map-pin class="w-4 h-4" />
                </span>
                <input
                    type="text"
                    id="rack_location"
                    name="rack_location"
                    x-model="value"
                    x-on:input="format()"
                    maxlength="7"
                    required
                    autocomplete="off"
                    placeholder="Contoh: A12-03"
                    @disabled($readonly)
                    @class([
                        'block w-full rounded-r-lg text-sm uppercase placeholder:normal-case',
                        'bg-slate-50 text-slate-500 border-slate-200 cursor-not-allowed focus:ring-0' => $readonly,
                        'border-slate-300 focus:border-teal-500 focus:ring-teal-500' => !$readonly
                    ])
                >
            </div>
            <p class="text-[11px] text-slate-500">Format: ZRR-BB (Zone-Rack-Bin), contoh: <span class="font-mono">A12-03</span>.</p>
            @unless($readonly)
                <x-input-error class="mt-1" :messages="$errors->get('rack_location')" />
            @endunless
        </div>
    </div>
</div>
