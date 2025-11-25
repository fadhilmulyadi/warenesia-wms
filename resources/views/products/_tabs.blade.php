@php
    /** @var \App\Models\Product|null $product */
    /** @var \Illuminate\Support\Collection|\App\Models\Category[] $categories */
    /** @var \Illuminate\Support\Collection|\App\Models\Supplier[] $suppliers */

    $formMode = $mode ?? 'create'; // 'create' | 'edit' | 'show'
    $isReadOnly = $formMode === 'show';

    $productNameValue = old('name', optional($product)->name);
    $productSkuValue = old('sku', optional($product)->sku);

    $productCategoryIdFromModel = optional($product)->category_id;
    $selectedCategoryId = old('category_id', session('newCategoryId', $productCategoryIdFromModel));

    $purchasePriceRaw = old('purchase_price', optional($product)->purchase_price ?? 0);
    $salePriceRaw = old('sale_price', optional($product)->sale_price ?? 0);

    $purchasePrice = (float) $purchasePriceRaw;
    $salePrice = (float) $salePriceRaw;
    $priceMargin = $salePrice - $purchasePrice;
    $priceMarginPercent = $purchasePrice > 0 ? ($priceMargin / $purchasePrice) * 100 : null;

    $currentStockRaw = old('current_stock', optional($product)->current_stock ?? 0);
    $minimumStockRaw = old('min_stock', optional($product)->min_stock ?? 0);

    $currentStock = (int) $currentStockRaw;
    $minimumStock = (int) $minimumStockRaw;

    $isOutOfStock = $currentStock === 0;
    $isLowStock = $currentStock > 0 && $currentStock <= $minimumStock;

    $defaultUnit = optional($product)->unit ?? 'pcs';
    $unitValue = old('unit', $defaultUnit);

    $canManageCategories = auth()->user()?->can('create', \App\Models\Category::class);

    $showBarcodeSection = $product !== null && $formMode !== 'create';
@endphp

<div
    x-data="{
        activeTab: 'general',
        isCategoryModalOpen: false
    }"
    class="space-y-4"
>
    {{-- Header status + label kecil --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700">
                Active
            </span>
            <div class="flex flex-col">
                <span class="text-[10px] uppercase tracking-wide text-slate-400">Product</span>
                <span class="text-sm font-semibold text-slate-900">
                    {{ $productNameValue ?: ($formMode === 'create' ? 'New product' : 'Unnamed product') }}
                </span>
            </div>
        </div>

        <div class="flex flex-col items-end gap-1 text-[11px] text-slate-500">
            <div class="flex items-center gap-2">
                <span>SKU:</span>
                <span class="font-mono text-slate-800">
                    {{ $productSkuValue ?: 'Not set' }}
                </span>
            </div>
            @if($formMode !== 'create' && $product)
                <div class="text-[10px] text-slate-400">
                    Last updated: {{ $product->updated_at?->format('d M Y H:i') }}
                </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-[200px,1fr] gap-4">
        {{-- TAB NAV --}}
        <aside class="rounded-2xl border border-slate-200 bg-white p-2 text-xs">
            <nav class="flex flex-col">
                @foreach ([
                    'general'   => 'General',
                    'prices'    => 'Prices',
                    'stock'     => 'Stock',
                    'suppliers' => 'Suppliers',
                    'movements' => 'Movements',
                    'activity'  => 'Activity log',
                ] as $tabKey => $tabLabel)
                    <button
                        type="button"
                        @click="activeTab = '{{ $tabKey }}'"
                        :class="activeTab === '{{ $tabKey }}'
                            ? 'bg-teal-50 text-teal-700 border-teal-200'
                            : 'text-slate-600 hover:bg-slate-50 border-transparent'"
                        class="flex items-center justify-between rounded-lg border px-3 py-2 mb-1 last:mb-0"
                    >
                        <span>{{ $tabLabel }}</span>
                    </button>
                @endforeach
            </nav>
        </aside>

        {{-- MAIN PANEL --}}
        <section class="rounded-2xl border border-slate-200 bg-white p-4 text-xs space-y-4">
            {{-- TAB GENERAL --}}
            <div x-show="activeTab === 'general'" x-cloak class="space-y-4">
                <h2 class="text-xs font-semibold text-slate-800 mb-1">General information</h2>

                <div @class([
                    'grid grid-cols-1 gap-4',
                    'md:grid-cols-[auto,1fr] items-start' => $showBarcodeSection,
                ])>
                    @if($showBarcodeSection)
                        @can('viewBarcode', $product)
                            <div class="flex flex-col items-center gap-2">
                                <div class="rounded-xl border border-slate-200 bg-white p-3">
                                    <img
                                        src="{{ route('products.barcode', $product) }}"
                                        alt="QR code for {{ $product->name }}"
                                        class="h-32 w-32 object-contain"
                                    >
                                </div>
                                <div class="text-[11px] text-slate-600 text-center">
                                    {{ $product->getBarcodeLabel() }}
                                </div>
                                <a
                                    href="{{ route('products.barcode.label', $product) }}"
                                    target="_blank"
                                    class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-[11px] text-slate-700 hover:bg-slate-50"
                                >
                                    <x-lucide-printer class="h-3 w-3 mr-1" />
                                    Print label
                                </a>
                            </div>
                        @endcan
                    @endif

                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    {{-- Column 1 --}}
                    <div class="space-y-3">
                        <div>
                            <label class="text-[11px] text-slate-500 mb-1 block">SKU *</label>
                            <input
                                type="text"
                                name="sku"
                                value="{{ $productSkuValue }}"
                                class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-[11px]"
                                @disabled($isReadOnly)
                            >
                        </div>

                        <div>
                            <label class="text-[11px] text-slate-500 mb-1 block">Product name *</label>
                            <input
                                type="text"
                                name="name"
                                value="{{ $productNameValue }}"
                                class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-[11px]"
                                @disabled($isReadOnly)
                            >
                        </div>

                        <div>
                            <label class="text-[11px] text-slate-500 mb-1 block">Category *</label>
                            <div class="flex items-strech gap-2">
                                <select
                                    name="category_id"
                                    class="w-full h-9 rounded-lg border border-slate-200 px-2 text-[11px]"
                                    @disabled($isReadOnly)
                                >
                                    <option value="">- Select category -</option>
                                    @foreach($categories as $category)
                                        <option
                                            value="{{ $category->id }}"
                                            @selected((int) $selectedCategoryId === (int) $category->id)
                                        >
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>

                                @if($canManageCategories && !$isReadOnly)
                                    <button
                                        type="button"
                                        class="h-9 inline-flex items-center justify-center rounded-lg border border-slate-200 px-3 text-[11px] text-slate-700 hover:bg-slate-50"
                                        @click="isCategoryModalOpen = true"
                                        title="Quick add category"
                                    >
                                        <x-lucide-plus class="h-3 w-3" />
                                    </button>
                                @endif
                            </div>
                        </div>

                        <div>
                            <label class="text-[11px] text-slate-500 mb-1 block">Unit of measure *</label>
                            <input
                                type="text"
                                name="unit"
                                value="{{ $unitValue }}"
                                class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-[11px]"
                                @disabled($isReadOnly)
                            >
                        </div>
                    </div>

                    {{-- Column 2 --}}
                    <div class="space-y-3">
                        <div>
                            <span class="text-[11px] text-slate-500 mb-1 block">Status</span>
                            <div class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-1.5">
                                Active
                            </div>
                        </div>

                        <div>
                            <span class="text-[11px] text-slate-500 mb-1 block">Default warehouse</span>
                            <div class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-1.5">
                                Main warehouse
                            </div>
                        </div>

                        <div>
                            <label class="text-[11px] text-slate-500 mb-1 block">Rack location</label>
                            <input
                                type="text"
                                name="rack_location"
                                value="{{ old('rack_location', optional($product)->rack_location) }}"
                                class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-[11px]"
                                @disabled($isReadOnly)
                            >
                        </div>

                        <div>
                            <label class="text-[11px] text-slate-500 mb-1 block">Main supplier</label>
                            <select
                                name="supplier_id"
                                class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-[11px]"
                                @disabled($isReadOnly)
                            >
                                <option value="">- None -</option>
                                @foreach($suppliers as $supplier)
                                    <option
                                        value="{{ $supplier->id }}"
                                        @selected((int) old(
                                            'supplier_id',
                                            optional($product)->supplier_id
                                        ) === (int) $supplier->id)
                                    >
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Column 3 --}}
                    <div class="space-y-3">
                        <div>
                            <label class="text-[11px] text-slate-500 mb-1 block">Current stock *</label>
                            <input
                                type="number"
                                name="current_stock"
                                min="0"
                                value="{{ $currentStock }}"
                                class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-[11px]"
                                @disabled($isReadOnly)
                            >
                        </div>

                        <div>
                            <label class="text-[11px] text-slate-500 mb-1 block">Minimum stock level *</label>
                            <input
                                type="number"
                                name="min_stock"
                                min="0"
                                value="{{ $minimumStock }}"
                                class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-[11px]"
                                @disabled($isReadOnly)
                            >
                        </div>

                            <div>
                                <span class="text-[11px] text-slate-500 mb-1 block">Stock status</span>
                                <div class="flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 px-2 py-1.5">
                                    <span class="font-semibold text-slate-900">
                                        {{ $currentStock }} {{ $unitValue }}
                                    </span>
                                    <span @class([
                                        'inline-flex items-center rounded-full px-2 py-0.5 text-[10px]',
                                        'bg-emerald-50 text-emerald-700' => !$isLowStock && !$isOutOfStock,
                                        'bg-amber-50 text-amber-700' => $isLowStock,
                                        'bg-red-50 text-red-700' => $isOutOfStock,
                                    ])>
                                        @if($isOutOfStock)
                                            Out of stock
                                        @elseif($isLowStock)
                                            Low stock
                                        @else
                                            Stock OK
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="text-[11px] text-slate-500 mb-1 block">Description</label>
                        <textarea
                            name="description"
                            rows="3"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-[11px]"
                            @disabled($isReadOnly)
                        >{{ old('description', optional($product)->description) }}</textarea>
                    </div>
                </div>
            </div>
            </div>

            {{-- TAB PRICES --}}
            <div x-show="activeTab === 'prices'" x-cloak class="space-y-4">
                <h2 class="text-xs font-semibold text-slate-800 mb-1">Prices</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="text-[11px] text-slate-500 mb-1 block">Purchase price (Rp) *</label>
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            name="purchase_price"
                            value="{{ $purchasePriceRaw }}"
                            class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-[11px]"
                            @disabled($isReadOnly)
                        >
                    </div>

                    <div>
                        <label class="text-[11px] text-slate-500 mb-1 block">Sale price (Rp) *</label>
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            name="sale_price"
                            value="{{ $salePriceRaw }}"
                            class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-[11px]"
                            @disabled($isReadOnly)
                        >
                    </div>
                </div>

                <div class="rounded-lg border border-slate-100 bg-slate-50 px-3 py-2 mt-3 text-[11px] text-slate-600 space-y-1">
                    <div>
                        Current margin:
                        <span class="font-semibold text-slate-900">
                            Rp {{ number_format($priceMargin, 0, ',', '.') }}
                        </span>
                        @if(!is_null($priceMarginPercent))
                            (<span class="font-semibold text-slate-900">
                                {{ number_format($priceMarginPercent, 1, ',', '.') }}%
                            </span>)
                        @endif
                    </div>
                    <div class="text-[10px] text-slate-500">
                        Margin is calculated as sale price minus purchase price based on the values above.
                    </div>
                </div>
            </div>

            {{-- TAB STOCK --}}
            <div x-show="activeTab === 'stock'" x-cloak class="space-y-4">
                <h2 class="text-xs font-semibold text-slate-800 mb-1">Stock by location</h2>

                <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 text-[11px] text-slate-600">
                    Detailed stock per warehouse location will appear here after
                    transaction modules (incoming and outgoing) are implemented.
                    For now, all stock is assumed to be stored in
                    <span class="font-semibold">Main warehouse</span>.
                </div>
            </div>

            {{-- TAB SUPPLIERS --}}
            <div x-show="activeTab === 'suppliers'" x-cloak class="space-y-4">
                <h2 class="text-xs font-semibold text-slate-800 mb-1">Suppliers</h2>

                <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 text-[11px] text-slate-600">
                    In the future, you can link this product with multiple suppliers
                    including lead time, MOQ, and preferred supplier rules.
                    For now, set the <span class="font-semibold">Main supplier</span> in the General tab.
                </div>
            </div>

            {{-- TAB MOVEMENTS --}}
            <div x-show="activeTab === 'movements'" x-cloak class="space-y-4">
                <h2 class="text-xs font-semibold text-slate-800 mb-1">Stock movements</h2>

                <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 text-[11px] text-slate-600">
                    Stock movement history (purchases, sales, restock, adjustments)
                    will be displayed here once transaction modules are completed
                    and this product is used in transactions.
                </div>
            </div>

            {{-- TAB ACTIVITY LOG --}}
            <div x-show="activeTab === 'activity'" x-cloak class="space-y-4">
                <h2 class="text-xs font-semibold text-slate-800 mb-1">Activity log</h2>

                <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 text-[11px] text-slate-600">
                    Activity log will start recording changes after the product is
                    saved (for example, price changes, stock adjustments, or category updates).
                </div>
            </div>
        </section>
    </div>
</div>