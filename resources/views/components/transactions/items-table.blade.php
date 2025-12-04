@props([
    'products',
    'priceLabel' => 'Harga',
    'priceField' => 'price',
    'readonly' => false,
    'initialItems' => [],
    'hidePrice' => false
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
if (! window.submitFormWithValidation) {
    window.submitFormWithValidation = function(formId) {
        const form = document.getElementById(formId);

        if (! form) return;

        if (typeof form.requestSubmit === 'function') {
            form.requestSubmit();
            return;
        }

        const submitEvent = new Event('submit', { cancelable: true, bubbles: true });

        if (form.dispatchEvent(submitEvent)) {
            form.submit();
        }
    };
}

function itemsTable(config) {
    const {
        initialItems = [],
        productPrices = {},
        productStocks = {},
        quantityErrors = {},
        priceField = 'price',
        shouldCheckStock = false
    } = config;

    return {
        items: initialItems.length > 0 ? initialItems : [{ 
            product_id: '',
            quantity: 1,
            [priceField]: 0,
        }],
        productPrices,
        productStocks,
        quantityErrors,
        shouldCheckStock,
        priceField,
        currentIndex: null,

        init() {
            const parentForm = this.$el.closest('form');
            if (parentForm) {
                parentForm.addEventListener('submit', (event) => this.validateBeforeSubmit(event));
            }

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
                [this.priceField]: 0,
            });
        },

        removeItem(index) {
            this.items.splice(index, 1);
        },

        onProductChange(index, productId) {
            this.items[index].product_id = productId;
            this.items[index][this.priceField] = this.productPrices[productId] ?? 0;
        },

        isStockInsufficient(index) {
            if (! this.shouldCheckStock) {
                return false;
            }

            const item = this.items[index];

            if (! item || ! item.product_id) {
                return false;
            }

            const availableStock = Number(this.productStocks[item.product_id] ?? 0);
            const requestedQty = Number(item.quantity ?? 0);

            return Number.isFinite(requestedQty) && Number.isFinite(availableStock) && requestedQty > availableStock;
        },

        validateBeforeSubmit(event) {
            if (! this.shouldCheckStock) {
                return;
            }

            const hasShortage = this.items.some((_, idx) => this.isStockInsufficient(idx));

            if (hasShortage) {
                event.preventDefault();
            }
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

            if (this.isStockInsufficient(index)) {
                const availableStock = Number(this.productStocks[item.product_id] ?? 0);
                return `Stok tidak mencukupi. Tersedia: ${availableStock}.`;
            }

            return backendError;
        }
    }
}
</script>

<div 
    class="rounded-xl border border-slate-200 overflow-hidden bg-white shadow-sm"
    x-data="itemsTable({
        initialItems: {{ \Illuminate\Support\Js::from($initialItems) }},
        productPrices: {{ \Illuminate\Support\Js::from($pricesData) }},
        productStocks: {{ \Illuminate\Support\Js::from($stockData) }},
        quantityErrors: {{ \Illuminate\Support\Js::from($quantityErrorMap) }},
        priceField: '{{ $priceField }}',
        shouldCheckStock: @js($priceField === 'unit_price')
    })"
    x-init="
        items.forEach((item, i) => {
            onProductChange(i, item.product_id);
        });
    "
>
    {{-- Header Toolbar (Shared) --}}
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

    {{-- MOBILE VERSION (REPLACEMENT) --}}
    <div class="md:hidden bg-slate-50 min-h-[300px] flex flex-col relative">
        
        {{-- LIST ITEM CONTAINER --}}
        <div class="p-3 space-y-3 pb-24"> {{-- Padding bottom besar agar tidak tertutup sticky footer --}}
            
            <template x-for="(item, index) in items" :key="index">
                <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-sm relative animate-fade-in-up"
                     :class="{ 'bg-yellow-50 border-yellow-200': item.original_quantity && item.quantity != item.original_quantity }">
                    
                    {{-- HEADER CARD: Item Number & Delete --}}
                    <div class="flex justify-between items-start mb-2 pb-2 border-b border-slate-50"
                         :class="{ 'border-yellow-100': item.original_quantity && item.quantity != item.original_quantity }">
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Item #<span x-text="index + 1"></span></div>
                        
                        @if(!$readonly)
                            <button type="button" @click="removeItem(index)" 
                                class="text-rose-400 hover:text-rose-600 p-1 -mr-1" title="Hapus Item">
                                <x-lucide-x class="w-4 h-4" />
                            </button>
                        @endif
                    </div>

                    {{-- INPUT FIELDS --}}
                    <div class="space-y-3">
                        
                        {{-- 1. Produk (Full Width) --}}
                        <div>
                            <x-custom-select
                                x-bind:data-name="'items[' + index + '][product_id]'"
                                :options="$productOptions"
                                placeholder="Pilih Produk"
                                x-model="item.product_id"
                                x-bind:value="String(item.product_id || '')"
                                :disabled="$readonly"
                                :required="true"
                                width="w-full"
                                class="text-sm"
                            />
                            <template x-if="item.original_quantity && item.quantity != item.original_quantity">
                                <div class="text-[10px] text-yellow-600 mt-1 font-medium flex items-center gap-1">
                                    <x-lucide-alert-triangle class="w-3 h-3" />
                                    <span>PO Qty: <span x-text="item.original_quantity"></span></span>
                                </div>
                            </template>
                        </div>

                        {{-- 2. Grid Qty & Harga (Side by Side) --}}
                        <div class="flex gap-2">
                            {{-- Qty --}}
                            <div class="w-1/3">
                                <div class="relative">
                                    <input 
                                        type="number" inputmode="numeric"
                                        :name="`items[${index}][quantity]`" 
                                        x-model="item.quantity"
                                        min="1"
                                        placeholder="Qty"
                                        class="w-full pl-2 pr-1 py-2 rounded-lg border-slate-200 text-sm font-semibold text-center focus:border-teal-500 focus:ring-teal-500 disabled:bg-slate-100"
                                        :class="{ 'border-yellow-400 focus:border-yellow-500 focus:ring-yellow-500': item.original_quantity && item.quantity != item.original_quantity }"
                                        :disabled="@js($readonly)"
                                        required
                                    >
                                    <span class="absolute top-0 right-1 text-[9px] text-slate-400 h-full flex items-center pointer-events-none">pcs</span>
                                </div>
                                {{-- Error message container --}}
                                <div class="text-[10px] text-rose-500 leading-tight mt-1" x-text="stockError(index)"></div>
                            </div>

                            {{-- Harga --}}
                            @if(!$hidePrice)
                            <div class="flex-1">
                                <div class="relative">
                                    <span class="absolute top-0 left-2 text-slate-400 h-full flex items-center pointer-events-none text-xs">Rp</span>
                                    <input 
                                        type="number" inputmode="numeric"
                                        :name="`items[${index}][{{ $priceField }}]`" 
                                        x-model="item.{{ $priceField }}"
                                        min="0"
                                        class="w-full pl-8 pr-2 py-2 rounded-lg border-slate-200 text-sm text-right font-medium focus:border-teal-500 focus:ring-teal-500 disabled:bg-slate-100"
                                        :disabled="@js($readonly)"
                                    >
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- FOOTER CARD: Subtotal Highlight --}}
                    @if(!$hidePrice)
                    <div class="mt-3 bg-slate-50 -mx-3 -mb-3 px-3 py-2 rounded-b-xl flex justify-between items-center border-t border-slate-100"
                         :class="{ 'bg-yellow-50/50 border-yellow-100': item.original_quantity && item.quantity != item.original_quantity }">
                        <span class="text-[10px] uppercase font-bold text-slate-500">Subtotal</span>
                        <span class="text-sm font-bold text-teal-700" 
                              x-text="new Intl.NumberFormat('id-ID').format(item.quantity * item.{{ $priceField }})">
                        </span>
                    </div>
                    @endif
                </div>
            </template>

            {{-- Empty State --}}
            <template x-if="items.length === 0">
                <div class="flex flex-col items-center justify-center py-10 text-slate-400 border-2 border-dashed border-slate-200 rounded-xl bg-slate-50/50">
                    <x-lucide-package-open class="w-8 h-8 mb-2 opacity-50" />
                    <span class="text-xs">Belum ada item</span>
                </div>
            </template>
            
            {{-- Tombol Tambah (Di dalam flow scroll) --}}
            @if(!$readonly)
                <button type="button" @click="addItem()" 
                    class="w-full py-3 border-2 border-dashed border-teal-200 text-teal-600 rounded-xl hover:bg-teal-50 hover:border-teal-300 transition-all font-semibold text-sm flex items-center justify-center gap-2">
                    <x-lucide-plus class="w-4 h-4" />
                    Tambah Baris Item
                </button>
            @endif
        </div>

        {{-- STICKY FOOTER GRAND TOTAL --}}
        @if(!$hidePrice)
        <div class="sticky bottom-0 left-0 right-0 bg-white border-t border-slate-200 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] z-20 p-4">
            <div class="flex justify-between items-end">
                <div class="text-xs font-medium text-slate-500 mb-1">Total Estimasi</div>
                <div class="text-lg font-bold text-slate-900 leading-none"
                     x-text="new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 })
                     .format(items.reduce((acc, item) => acc + (item.quantity * item.{{ $priceField }}), 0))">
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="hidden md:block overflow-x-auto">
        <table class="min-w-full text-xs">
            <x-table.thead>
                <x-table.th class="w-1/2">Produk</x-table.th>
                <x-table.th align="right" class="min-w-[100px]">Qty</x-table.th>
                @if(!$hidePrice)
                    <x-table.th align="right" class="min-w-[160px]">{{ $priceLabel }}</x-table.th>
                    <x-table.th align="right" class="w-36">Total</x-table.th>
                @endif
                @if(!$readonly) <x-table.th class="w-10"></x-table.th> @endif
            </x-table.thead>

            <x-table.tbody>
                <template x-for="(item, index) in items" :key="index">
                    <tr class="group border-b border-slate-50 last:border-b-0 hover:bg-slate-50 transition-colors text-sm"
                        :class="{ 'bg-yellow-50 hover:bg-yellow-50': item.original_quantity && item.quantity != item.original_quantity }">
                        
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
                            <template x-if="item.original_quantity && item.quantity != item.original_quantity">
                                <div class="text-[10px] text-yellow-600 mt-1 font-medium flex items-center gap-1">
                                    <x-lucide-alert-triangle class="w-3 h-3" />
                                    <span>PO Qty: <span x-text="item.original_quantity"></span></span>
                                </div>
                            </template>
                        </x-table.td>

                        {{-- Qty --}}
                        <x-table.td align="right">
                            <input 
                                type="number" 
                                :name="`items[${index}][quantity]`" 
                                x-model="item.quantity"
                                min="1"
                                class="w-full h-[42px] rounded-xl shadow-sm border-slate-300 text-sm text-right focus:border-teal-500 focus:ring-teal-500 disabled:bg-slate-100"
                                :class="{ 'border-yellow-400 focus:border-yellow-500 focus:ring-yellow-500': item.original_quantity && item.quantity != item.original_quantity }"
                                :disabled="@js($readonly)"
                                required
                            >
                            <x-form-error 
                                :message="null"
                                x-bind:message="stockError(index)"
                            />
                        </x-table.td>

                        {{-- Harga --}}
                        @if(!$hidePrice)
                        <x-table.td align="right">
                            <input 
                                type="number" 
                                :name="`items[${index}][{{ $priceField }}]`" 
                                x-model="item.{{ $priceField }}"
                                min="0"
                                class="w-full h-[42px] rounded-xl shadow-sm border-slate-300 text-sm text-right focus:border-teal-500 focus:ring-teal-500 disabled:bg-slate-100"
                                :disabled="@js($readonly)"
                            >
                        </x-table.td>
                        @endif

                        {{-- Subtotal --}}
                        @if(!$hidePrice)
                        <x-table.td align="right">
                            <div class="py-2 font-medium text-slate-700 text-sm">
                                <span x-text="new Intl.NumberFormat('id-ID').format(item.quantity * item.{{ $priceField }})"></span>
                            </div>
                        </x-table.td>
                        @endif

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
            @if(!$hidePrice)
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
            @endif
        </table>
    </div>
</div>