@props([
    'products',
    'priceLabel' => 'Harga',
    'priceField' => 'price',
    'readonly' => false,
    'initialItems' => []
])

@php
    $masterPriceColumn = ($priceField === 'unit_cost') 
        ? 'purchase_price' 
        : 'sale_price';

    $pricesData = $products->pluck($masterPriceColumn, 'id');
    $stockData = $products->pluck('current_stock', 'id')->map(fn ($stock) => (int) $stock);
    
    $productOptions = $products->mapWithKeys(function($product) {
        return [$product->id => "{$product->name} (Stok: {$product->current_stock})"];
    })->toArray();

    $quantityErrorMap = collect($errors?->getMessages() ?? [])
        ->filter(fn ($messages, $key) => preg_match('/^items\.(\d+)\.quantity$/', $key))
        ->mapWithKeys(function ($messages, $key) {
            preg_match('/^items\.(\d+)\.quantity$/', $key, $matches);

            return [$matches[1] => $messages[0] ?? null];
        });
@endphp

<script>
function itemsTable(initialItems = []) {
    return {
        items: initialItems.length > 0 ? initialItems : [{ 
            product_id: '',
            quantity: 1,
            {{ $priceField }}: 0,
        }],
        productPrices: {{ \Illuminate\Support\Js::from($pricesData) }},
        productStocks: {{ \Illuminate\Support\Js::from($stockData) }},
        quantityErrors: {{ \Illuminate\Support\Js::from($quantityErrorMap) }},
        shouldCheckStock: @js($priceField === 'unit_price'),
        currentIndex: null,

        init() {
            window.addEventListener('custom-select-opened', (event) => {
                const inputName = event.detail;
                
                if (!inputName) {
                    this.currentIndex = null;
                    return;
                }

                const match = String(inputName).match(/items\[(\d+)\]/);
                this.currentIndex = match ? Number(match[1]) : null;
            });

            window.addEventListener('product-selected', (event) => {
                if (this.currentIndex === null) return;
                this.onProductChange(this.currentIndex, event.detail);
            });
        },

        addItem() {
            this.items.push({
                product_id: '',
                quantity: 1,
                {{ $priceField }}: 0,
            });
        },

        removeItem(index) {
            this.items.splice(index, 1);
        },

        onProductChange(index, productId) {
            this.items[index].product_id = productId;
            this.items[index].{{ $priceField }} = this.productPrices[productId] ?? 0;
        },

        stockError(index) {
            const backendError = this.quantityErrors && Object.prototype.hasOwnProperty.call(this.quantityErrors, index)
                ? this.quantityErrors[index]
                : '';

            if (! this.shouldCheckStock) {
                return backendError;
            }

            const item = this.items[index];

            if (! item || ! item.product_id) {
                return backendError;
            }

            const availableStock = Number(this.productStocks[item.product_id] ?? 0);
            const requestedQty = Number(item.quantity ?? 0);

            if (Number.isFinite(requestedQty) && requestedQty > availableStock) {
                return `Stok tidak mencukupi. Tersedia: ${availableStock}.`;
            }

            return backendError;
        }
    }
}
</script>

<div 
    class="rounded-xl border border-slate-200 overflow-hidden bg-white shadow-sm"
    x-data="itemsTable({{ \Illuminate\Support\Js::from($initialItems) }})"
    x-init="
        items.forEach((item, i) => {
            onProductChange(i, item.product_id);
        });
    "

>
    {{-- Header Toolbar --}}
    <div class="flex items-center justify-between p-3 bg-slate-50 border-b border-slate-200">
        <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Detail Item</h3>
        
        @if(!$readonly)
            <button type="button" @click="addItem()" 
                class="text-xs flex items-center gap-1 text-teal-600 hover:text-teal-700 font-semibold transition-colors">
                <x-lucide-plus class="w-3.5 h-3.5" /> 
                <span>Tambah Baris</span>
            </button>
        @endif
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="min-w-full text-xs">
            <x-table.thead>
                <x-table.th class="w-1/2">Produk</x-table.th>
                <x-table.th align="right" class="w-24">Qty</x-table.th>
                <x-table.th align="right" class="w-36">{{ $priceLabel }}</x-table.th>
                <x-table.th align="right" class="w-36">Total</x-table.th>
                @if(!$readonly) <x-table.th class="w-10"></x-table.th> @endif
            </x-table.thead>

            <x-table.tbody>
                <template x-for="(item, index) in items" :key="index">
                    <tr class="group border-b border-slate-50 last:border-b-0 hover:bg-slate-50 transition-colors">
                        
                        {{-- Produk --}}
                        <x-table.td>
                            <x-custom-select
                                x-bind:data-name="'items[' + index + '][product_id]'"
                                :options="$productOptions"
                                placeholder="Pilih Produk"
                                x-model="item.product_id"
                                x-bind:value="String(item.product_id || '')"
                                :disabled="$readonly"
                                :required="true"
                                width="w-full"
                                dropUp
                            />
                        </x-table.td>

                        {{-- Qty --}}
                        <x-table.td align="right">
                            <input 
                                type="number" 
                                :name="`items[${index}][quantity]`" 
                                x-model="item.quantity"
                                min="1"
                                class="w-full rounded-lg border-slate-200 text-xs text-right focus:border-teal-500 focus:ring-teal-500 disabled:bg-slate-100"
                                :disabled="@js($readonly)"
                                required
                            >
                            <x-form-error 
                                :message="null"
                                x-bind:message="stockError(index)"
                            />
                        </x-table.td>

                        {{-- Harga --}}
                        <x-table.td align="right">
                            <input 
                                type="number" 
                                :name="`items[${index}][{{ $priceField }}]`" 
                                x-model="item.{{ $priceField }}"
                                min="0"
                                class="w-full rounded-lg border-slate-200 text-xs text-right focus:border-teal-500 focus:ring-teal-500 disabled:bg-slate-100"
                                :disabled="@js($readonly)"
                            >
                        </x-table.td>

                        {{-- Subtotal --}}
                        <x-table.td align="right">
                            <div class="py-2 font-medium text-slate-700">
                                <span x-text="new Intl.NumberFormat('id-ID').format(item.quantity * item.{{ $priceField }})"></span>
                            </div>
                        </x-table.td>

                        {{-- Hapus --}}
                        @if(!$readonly)
                            <x-table.td align="center">
                                <button type="button" @click="removeItem(index)" 
                                    class="p-1 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded transition-colors"
                                    title="Hapus baris">
                                    <x-lucide-trash-2 class="w-4 h-4" />
                                </button>
                            </x-table.td>
                        @endif

                    </tr>
                </template>
            </x-table.tbody>

            {{-- Footer Total --}}
            <tfoot class="bg-slate-50 border-t border-slate-200 font-bold text-slate-700">
                <tr>
                    <td colspan="3" class="px-3 py-3 text-right uppercase text-[10px] tracking-wider text-slate-500">
                        Grand Total
                    </td>
                    <td class="px-3 py-3 text-right text-sm text-teal-700">
                        <span 
                            x-text="
                                new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' })
                                .format(items.reduce((acc, item) => acc + (item.quantity * item.{{ $priceField }}), 0))
                            "
                        ></span>
                    </td>
                    @if(!$readonly) <td></td> @endif
                </tr>
            </tfoot>
        </table>
    </div>
</div>
