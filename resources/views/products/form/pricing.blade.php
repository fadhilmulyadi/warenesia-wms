<div class="p-6">
    <h3 class="text-sm font-bold text-slate-800 mb-4 uppercase tracking-wider border-b border-slate-100 pb-2">Harga</h3>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Harga Beli --}}
        <div>
            <x-input-label for="purchase_price" value="Harga Beli (HPP) *"
                class="text-sm font-semibold text-slate-700" />
            <div class="mt-1">
                <x-currency-input
                    id="purchase_price"
                    name="purchase_price"
                    :value="old('purchase_price', $product->purchase_price)"
                    :disabled="$readonly"
                    @class([
                        'bg-slate-50 text-slate-500 border-slate-200 cursor-not-allowed focus:ring-0' => $readonly,
                        'border-slate-300 focus:border-teal-500 focus:ring-teal-500' => !$readonly,
                    ])
                />
            </div>
            @unless($readonly)
                <x-input-error class="mt-2" :messages="$errors->get('purchase_price')" />
            @endunless
        </div>

        {{-- Harga Jual --}}
        <div>
            <x-input-label for="sale_price" value="Harga Jual *" class="text-sm font-semibold text-slate-700" />
            <div class="mt-1">
                <x-currency-input
                    id="sale_price"
                    name="sale_price"
                    :value="old('sale_price', $product->sale_price)"
                    :disabled="$readonly"
                    @class([
                        'font-bold text-slate-800',
                        'bg-slate-50 text-slate-500 border-slate-200 cursor-not-allowed focus:ring-0' => $readonly,
                        'border-slate-300 focus:border-teal-500 focus:ring-teal-500' => !$readonly,
                    ])
                />
            </div>
            @unless($readonly)
                <x-input-error class="mt-2" :messages="$errors->get('sale_price')" />
            @endunless
        </div>
    </div>
</div>
