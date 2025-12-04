@props([
    'products',
    'priceLabel' => 'Harga',
    'priceField' => 'price',
    'readonly' => false,
    'initialItems' => [],
    'hidePrice' => false
])

@php
    $stockData = $products->pluck('current_stock', 'id')->map(fn ($stock) => (int) $stock);
    $skusData = $products->pluck('sku', 'id');
    
    $productOptions = $products->mapWithKeys(function($product) {
        return [$product->id => "{$product->name}"];
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
        productStocks = {},
        productSkus = {},
        quantityErrors = {},
        shouldCheckStock = false
    } = config;

    return {
        items: initialItems.length > 0 ? initialItems : [{ 
            product_id: '',
            quantity: 1,
        }],
        productStocks,
        productSkus,
        quantityErrors,
        shouldCheckStock,
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
            });
        },

        removeItem(index) {
            this.items.splice(index, 1);
        },

        onProductChange(index, productId) {
            this.items[index].product_id = productId;
        },

        getProductStock(productId) {
            if (!productId) return null;
            return Number(this.productStocks[productId] ?? 0);
        },

        getStockClass(productId) {
            const stock = this.getProductStock(productId);
            if (stock === null) return 'text-slate-300 bg-slate-50 border-slate-200'; // Belum pilih
            if (stock === 0) return 'text-rose-600 bg-rose-50 border-rose-100 font-bold'; // Habis
            if (stock < 5) return 'text-amber-600 bg-amber-50 border-amber-100 font-bold'; // Sekarat
            return 'text-slate-600 bg-slate-100 border-slate-200'; // Aman
        },

        isStockInsufficient(index) {
            if (! this.shouldCheckStock) {
                return false;
            }

            const item = this.items[index];

            if (! item || ! item.product_id) {
                return false;
            }

            const availableStock = this.getProductStock(item.product_id);
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
                const availableStock = this.getProductStock(item.product_id);
                return `Stok tidak mencukupi. Tersedia: ${availableStock}.`;
            }

            return backendError;
        },

        getProductSku(productId) {
            return this.productSkus[productId] ?? '-';
        }
    }
}
</script>

<div 
    class="rounded-xl border border-slate-200 overflow-hidden bg-white shadow-sm max-w-6xl mx-auto"
    x-data="itemsTable({
        initialItems: {{ \Illuminate\Support\Js::from($initialItems) }},
        productStocks: {{ \Illuminate\Support\Js::from($stockData) }},
        productSkus: {{ \Illuminate\Support\Js::from($skusData) }},
        quantityErrors: {{ \Illuminate\Support\Js::from($quantityErrorMap) }},
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

    {{-- MOBILE VERSION --}}
    <div class="md:hidden bg-slate-50 min-h-[300px] flex flex-col relative">
        
        {{-- LIST ITEM CONTAINER --}}
        <div class="p-3 space-y-3 pb-24">
            
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
                        
                        {{-- Produk --}}
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
                                :is-static="true"
                            />
                            {{-- Pesan error stok --}}
                            <div class="text-[10px] text-rose-500 mt-1.5 font-medium flex items-center gap-1" x-show="stockError(index)">
                                <x-lucide-alert-circle class="w-3 h-3" />
                                <span x-text="stockError(index)"></span>
                            </div>
                            <template x-if="item.original_quantity && item.quantity != item.original_quantity">
                                <div class="text-[10px] text-yellow-600 mt-1 font-medium flex items-center gap-1">
                                    <x-lucide-alert-triangle class="w-3 h-3" />
                                    <span>PO Qty: <span x-text="item.original_quantity"></span></span>
                                </div>
                            </template>
                        </div>

                        {{-- SKU --}}
                        <div class="flex gap-2">
                            {{-- SKU --}}
                            <div class="w-1/2">
                                <label class="text-[10px] text-slate-400 font-medium mb-1 block">SKU</label>
                                <input 
                                    type="text" 
                                    readonly
                                    :value="getProductSku(item.product_id)"
                                    class="w-full px-2 py-1.5 rounded-xl border-slate-200 bg-slate-50 text-slate-500 text-xs font-mono shadow-sm focus:ring-0"
                                >
                            </div>
                            
                            {{-- Stock --}}
                            <div class="w-1/2">
                                <label class="text-[10px] text-slate-400 font-medium mb-1 block">Stok</label>
                                <input 
                                    type="text" 
                                    readonly
                                    :value="getProductStock(item.product_id) !== null ? getProductStock(item.product_id) : '-'"
                                    class="w-full px-2 py-1.5 rounded-xl border-slate-200 bg-slate-50 text-slate-500 shadow-sm text-xs text-center focus:ring-0"
                                    :class="getStockClass(item.product_id)"
                                >
                            </div>
                        </div>

                        {{-- Qty Input --}}
                        <div>
                             <label class="text-[10px] text-slate-400 font-medium mb-1 block">Quantity</label>
                            <div class="relative">
                                <input 
                                    type="number" inputmode="numeric"
                                    :name="`items[${index}][quantity]`" 
                                    x-model="item.quantity"
                                    min="1"
                                    :max="shouldCheckStock ? (getProductStock(item.product_id) || 999999) : 999999"
                                    placeholder="Qty"
                                    class="w-full pl-2 pr-1 py-2 rounded-lg border-slate-200 text-sm font-semibold text-center focus:border-teal-500 focus:ring-teal-500 disabled:bg-slate-100"
                                    :class="{ 'border-yellow-400 focus:border-yellow-500 focus:ring-yellow-500': item.original_quantity && item.quantity != item.original_quantity }"
                                    :disabled="@js($readonly)"
                                    required
                                >
                                <span class="absolute top-0 right-1 text-[9px] text-slate-400 h-full flex items-center pointer-events-none">pcs</span>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Empty State --}}
            <template x-if="items.length === 0">
                <div class="flex flex-col items-center justify-center py-10 text-slate-400 border-2 border-dashed border-slate-200 rounded-xl bg-slate-50/50">
                    <x-lucide-package-open class="w-8 h-8 mb-2 opacity-50" />
                    <span class="text-xs">Belum ada item</span>
                </div>
            </template>
            
            {{-- Tombol Tambah --}}
            @if(!$readonly)
                <button type="button" @click="addItem()" 
                    class="w-full py-3 border-2 border-dashed border-teal-200 text-teal-600 rounded-xl hover:bg-teal-50 hover:border-teal-300 transition-all font-semibold text-sm flex items-center justify-center gap-2">
                    <x-lucide-plus class="w-4 h-4" />
                    Tambah Baris Item
                </button>
            @endif
        </div>
    </div>

    {{-- DESKTOP VERSION --}}
    <div class="hidden md:block overflow-x-visible">
        <table class="w-full text-sm text-left">
            <thead class="text-xs text-slate-500 uppercase bg-slate-50 border-b border-slate-200">
                <tr>
                    {{-- Produk --}}
                    <th scope="col" class="px-4 py-3 font-semibold pl-6 w-auto">Produk</th>
                    
                    {{-- SKU --}}
                    <th scope="col" class="px-4 py-3 font-semibold w-40">SKU</th>

                    {{-- Stok --}}
                    <th scope="col" class="px-4 py-3 font-semibold w-32 text-center">Stok</th>
                    
                    {{-- Qty --}}
                    <th scope="col" class="px-4 py-3 font-semibold w-40 text-center">Qty Input</th>
                    
                    @if(!$readonly)
                        <th scope="col" class="px-4 py-3 w-16"></th>
                    @endif
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100">
                <template x-for="(item, index) in items" :key="index">
                    <tr class="group hover:bg-slate-50 transition-colors"
                        :class="{ 'bg-yellow-50 hover:bg-yellow-50': item.original_quantity && item.quantity != item.original_quantity }">
                        
                        {{-- Input Produk --}}
                        <td class="px-4 py-3 pl-6 align-top">
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
                            <div class="text-[10px] text-rose-500 mt-1.5 font-medium flex items-center gap-1" x-show="stockError(index)">
                                <x-lucide-alert-circle class="w-3 h-3" />
                                <span x-text="stockError(index)"></span>
                            </div>
                            <template x-if="item.original_quantity && item.quantity != item.original_quantity">
                                <div class="text-[10px] text-yellow-600 mt-1 font-medium flex items-center gap-1">
                                    <x-lucide-alert-triangle class="w-3 h-3" />
                                    <span>PO Qty: <span x-text="item.original_quantity"></span></span>
                                </div>
                            </template>
                        </td>

                        {{-- SKU --}}
                        <td class="px-4 py-3 align-top">
                            <input 
                                type="text" 
                                readonly
                                :value="getProductSku(item.product_id)"
                                class="w-full rounded-xl h-[42px] bg-slate-50 text-slate-500 border-slate-200 text-sm font-mono shadow-sm focus:ring-0 "
                            >
                        </td>

                        {{-- Stok --}}
                        <td class="px-4 py-3 align-top">
                            <input 
                                type="text" 
                                readonly
                                :value="getProductStock(item.product_id) !== null ? getProductStock(item.product_id) : '-'"
                                class="w-full text-center rounded-xl h-[42px] bg-slate-50 text-slate-500 border-slate-200 shadow-sm text-sm font-bold focus:ring-0"
                                :class="getStockClass(item.product_id)"
                            >
                        </td>

                        {{-- Quantity Input --}}
                        <td class="px-4 py-3 align-top">
                            <div class="relative w-28 mx-auto">
                                <input 
                                    type="number" 
                                    :name="`items[${index}][quantity]`" 
                                    x-model="item.quantity"
                                    min="1"
                                    :max="shouldCheckStock ? (getProductStock(item.product_id) || 999999) : 999999"
                                    class="w-full text-center h-[42px] rounded-xl border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm font-bold text-slate-700"
                                    :class="{ 
                                        'border-rose-300 ring-rose-200 bg-rose-50 text-rose-700': stockError(index),
                                        'border-yellow-400 focus:border-yellow-500 focus:ring-yellow-500': item.original_quantity && item.quantity != item.original_quantity,
                                        'bg-slate-100': !item.product_id 
                                    }"
                                    :disabled="@js($readonly) || !item.product_id"
                                    required
                                >
                            </div>
                        </td>

                        {{-- Delete --}}
                        @if(!$readonly)
                            <td class="px-4 py-3 align-top pt-3 text-center">
                                <button type="button" @click="removeItem(index)" 
                                    class="text-slate-400 hover:text-rose-600 transition-colors p-1">
                                    <x-lucide-trash-2 class="w-4 h-4" />
                                </button>
                            </td>
                        @endif
                    </tr>
                </template>

                {{-- Empty State --}}
                 <template x-if="items.length === 0">
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-400 border-dashed border-b border-slate-200">
                           <x-lucide-package-open class="w-8 h-8 mb-2 opacity-50 mx-auto" />
                           <span class="text-sm">Belum ada item</span>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</div>