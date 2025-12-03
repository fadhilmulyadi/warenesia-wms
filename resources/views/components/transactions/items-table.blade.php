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

    <div class="md:hidden bg-slate-50/50 p-3 space-y-3">
        <template x-for="(item, index) in items" :key="index">
            <div class="bg-white p-4 rounded-lg border border-slate-200 shadow-sm relative space-y-3">
                
                @if(!$readonly)
                    <button type="button" @click="removeItem(index)" 
                        class="absolute top-2 right-2 bg-rose-500 text-white p-1.5 rounded-lg hover:bg-rose-600 shadow-sm transition-colors z-10"
                        title="Hapus Item">
                        <x-lucide-trash-2 class="w-4 h-4" />
                    </button>
                @endif

                <div class="pr-6"> {{-- Padding right agar tidak tertutup tombol hapus --}}
                    <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Produk</label>
                    <x-custom-select
                        x-bind:data-name="'items[' + index + '][product_id]'"
                        :options="$productOptions"
                        placeholder="Pilih Produk"
                        x-model="item.product_id"
                        x-bind:value="String(item.product_id || '')"
                        :disabled="$readonly"
                        :required="true"
                        width="w-full"
                    />
                </div>

                {{-- Row 2: Grid Qty & Harga --}}
                <div class="grid grid-cols-2 gap-3">
                    {{-- Qty --}}
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">Qty</label>
                        <input 
                            type="number" 
                            :name="`items[${index}][quantity]`" 
                            x-model="item.quantity"
                            min="1"
                            class="w-full rounded-lg border-slate-200 text-sm focus:border-teal-500 focus:ring-teal-500 disabled:bg-slate-100"
                            :disabled="@js($readonly)"
                            required
                        >
                        <x-form-error :message="null" x-bind:message="stockError(index)" />
                    </div>

                    {{-- Harga --}}
                    <div>
                        <label class="block text-[10px] font-bold uppercase text-slate-500 mb-1">{{ $priceLabel }}</label>
                        <input 
                            type="number" 
                            :name="`items[${index}][{{ $priceField }}]`" 
                            x-model="item.{{ $priceField }}"
                            min="0"
                            class="w-full rounded-lg border-slate-200 text-sm text-right focus:border-teal-500 focus:ring-teal-500 disabled:bg-slate-100"
                            :disabled="@js($readonly)"
                        >
                    </div>
                </div>

                {{-- Row 3: Subtotal Card --}}
                <div class="pt-2 border-t border-slate-100 flex justify-between items-center">
                    <span class="text-xs text-slate-500 font-medium">Subtotal</span>
                    <span class="text-sm font-bold text-slate-700" 
                          x-text="new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(item.quantity * item.{{ $priceField }})">
                    </span>
                </div>
            </div>
        </template>

        {{-- Mobile Empty State --}}
        <template x-if="items.length === 0">
            <div class="text-center py-6 text-slate-400 text-xs">
                Belum ada item ditambahkan.
            </div>
        </template>

        {{-- Mobile Grand Total --}}
        <div class="bg-teal-50 rounded-lg p-3 border border-teal-100 flex justify-between items-center">
            <span class="text-xs font-bold uppercase text-teal-800">Grand Total</span>
            <span class="text-base font-bold text-teal-700"
                x-text="new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' })
                .format(items.reduce((acc, item) => acc + (item.quantity * item.{{ $priceField }}), 0))">
            </span>
        </div>
    </div>

    <div class="hidden md:block overflow-x-auto">
        <table class="min-w-full text-xs">
            <x-table.thead>
                <x-table.th class="w-1/2">Produk</x-table.th>
                <x-table.th align="right" class="min-w-[100px]">Qty</x-table.th>
                <x-table.th align="right" class="min-w-[160px]">{{ $priceLabel }}</x-table.th>
                <x-table.th align="right" class="w-36">Total</x-table.th>
                @if(!$readonly) <x-table.th class="w-10"></x-table.th> @endif
            </x-table.thead>

            <x-table.tbody>
                <template x-for="(item, index) in items" :key="index">
                    <tr class="group border-b border-slate-50 last:border-b-0 hover:bg-slate-50 transition-colors text-sm">
                        
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
                                class="w-full h-[42px] rounded-xl shadow-sm border-slate-300 text-sm text-right focus:border-teal-500 focus:ring-teal-500 disabled:bg-slate-100"
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
                                class="w-full h-[42px] rounded-xl shadow-sm border-slate-300 text-sm text-right focus:border-teal-500 focus:ring-teal-500 disabled:bg-slate-100"
                                :disabled="@js($readonly)"
                            >
                        </x-table.td>

                        {{-- Subtotal --}}
                        <x-table.td align="right">
                            <div class="py-2 font-medium text-slate-700 text-sm">
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