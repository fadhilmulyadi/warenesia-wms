@props([
    'supplierOptions' => [],
    'productOptions' => [],
    'productStocks' => [],
    'prefilledType' => 'purchases',
    'prefilledSupplierId' => null,
    'prefilledProductId' => null,
    'prefilledCustomerName' => '',
    'prefilledQuantity' => 1,
])

<x-dashboard.card
    title="Quick Entry Transaksi"
    subtitle="Catat barang masuk atau keluar dengan cepat."
>
    <form
        method="GET"
        x-data="{
            type: '{{ $prefilledType ?: 'purchases' }}',
            productId: '{{ $prefilledProductId ?: '' }}',
            quantity: {{ (int) ($prefilledQuantity ?? 1) }},
            productStocks: @js($productStocks),
            stockMessage: '',
            action() {
                return this.type === 'purchases'
                    ? '{{ route('purchases.create') }}'
                    : '{{ route('sales.create') }}';
            },
            refreshStockMessage() {
                if (this.type !== 'sales') {
                    this.stockMessage = '';
                    return;
                }

                const productId = Number(this.productId || 0);
                const quantity = Number(this.quantity || 0);

                if (! productId || ! Number.isFinite(quantity) || quantity <= 0) {
                    this.stockMessage = '';
                    return;
                }

                const available = this.productStocks[productId] ?? this.productStocks[String(productId)] ?? null;

                if (! Number.isFinite(Number(available))) {
                    this.stockMessage = '';
                    return;
                }

                if (quantity > Number(available)) {
                    this.stockMessage = `Stok tidak mencukupi. Tersedia: ${available}.`;
                    return;
                }

                this.stockMessage = '';
            },
        }"
        x-bind:action="action()"
        x-effect="refreshStockMessage()"
        
        class="space-y-4"
    >
        {{-- Tipe transaksi --}}
        <div>
            <x-input-label value="Tipe Transaksi" class="mb-1" />
            <x-custom-select
                name="type"
                :options="['purchases' => 'Barang Masuk', 'sales' => 'Barang Keluar']"
                :value="$prefilledType"
                x-model="type"
                placeholder="Pilih tipe"
            />
        </div>

        {{-- Partner dinamis (supplier / customer) --}}
        <div x-show="type === 'purchases'">
            <x-input-label value="Supplier" class="mb-1" />
            <x-custom-select
                name="supplier_id"
                :options="$supplierOptions"
                :value="$prefilledSupplierId"
                placeholder="Pilih Supplier"
            />
        </div>

        <div x-show="type === 'sales'">
            <x-input-label value="Customer" class="mb-1" />
            <x-text-input
                name="customer_name"
                type="text"
                class="w-full"
                :value="$prefilledCustomerName"
                placeholder="Nama Customer"
            />
        </div>

        {{-- Produk --}}
        <div>
            <x-input-label value="Produk" class="mb-1" />
            <x-custom-select
                name="product_id"
                :options="$productOptions"
                :value="$prefilledProductId"
                x-model="productId"
                placeholder="Pilih Produk"
            />
        </div>

        {{-- Quantity --}}
        <div>
            <x-input-label value="Jumlah" class="mb-1" />
            <x-text-input
                name="quantity"
                type="number"
                min="1"
                class="w-full"
                :value="$prefilledQuantity ?? 1"
                x-model.number="quantity"
            />
            <x-form-error :message="null" x-bind:message="stockMessage" />
        </div>

        <div class="pt-2">
            <x-action-button type="submit" variant="primary" icon="edit">
                Catat Transaksi
            </x-action-button>
        </div>
    </form>
</x-dashboard.card>