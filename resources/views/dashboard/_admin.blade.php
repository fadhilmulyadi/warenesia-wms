@php
    $canViewProducts = auth()->user()?->can('viewAny', \App\Models\Product::class);
    $canViewCategories = auth()->user()?->can('viewAny', \App\Models\Category::class);
    $canViewSuppliers = auth()->user()?->can('viewAny', \App\Models\Supplier::class);
    $canViewRestocks = auth()->user()?->can('viewAny', \App\Models\RestockOrder::class);
    $canViewSales = auth()->user()?->can('viewAny', \App\Models\OutgoingTransaction::class);
@endphp

<div class="space-y-6">
    {{-- Quick links --}}
    @if($canViewProducts || $canViewCategories || $canViewSuppliers)
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3 text-sm">
            @if($canViewProducts)
                <a href="{{ route('products.index') }}"
                   class="group rounded-xl border border-slate-200 bg-white px-3 py-3 hover:border-teal-200 hover:bg-teal-50 flex flex-col gap-1">
                    <span class="text-xs text-slate-500">Products</span>
                    <span class="text-2xl font-semibold text-slate-900 group-hover:text-teal-700">
                        {{ number_format($quickLinks['products']) }}
                    </span>
                </a>
            @endif

            @if($canViewCategories)
                <a href="{{ route('categories.index') }}"
                   class="group rounded-xl border border-slate-200 bg-white px-3 py-3 hover:border-teal-200 hover:bg-teal-50 flex flex-col gap-1">
                    <span class="text-xs text-slate-500">Categories</span>
                    <span class="text-2xl font-semibold text-slate-900 group-hover:text-teal-700">
                        {{ number_format($quickLinks['categories']) }}
                    </span>
                </a>
            @endif

            @if($canViewSuppliers)
                <a href="{{ route('suppliers.index') }}"
                   class="group rounded-xl border border-slate-200 bg-white px-3 py-3 hover:border-teal-200 hover:bg-teal-50 flex flex-col gap-1">
                    <span class="text-xs text-slate-500">Suppliers</span>
                    <span class="text-2xl font-semibold text-slate-900 group-hover:text-teal-700">
                        {{ number_format($quickLinks['suppliers']) }}
                    </span>
                </a>
            @endif

            <div class="rounded-xl border border-slate-200 bg-white px-3 py-3 flex flex-col gap-1">
                <span class="text-xs text-slate-500">Customers</span>
                <span class="text-2xl font-semibold text-slate-900">
                    {{ number_format($quickLinks['customers']) }}
                </span>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white px-3 py-3 flex flex-col gap-1">
                <span class="text-xs text-slate-500">Users</span>
                <span class="text-2xl font-semibold text-slate-900">
                    {{ number_format($quickLinks['users']) }}
                </span>
            </div>
        </div>
    @endif

    {{-- KPI --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-4 space-y-3">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-sm font-semibold text-slate-800">Key performance indicators</h2>
                <p class="text-xs text-slate-500">
                    Period: {{ $period['start']->format('d M') }} - {{ $period['end']->format('d M Y') }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
            <div class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-2">
                <p class="text-[11px] text-slate-500">Revenue (Rp)</p>
                <p class="text-lg font-semibold text-slate-900">{{ number_format($kpi['revenue'], 2, ',', '.') }}</p>
            </div>
            <div class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-2">
                <p class="text-[11px] text-slate-500">Net (Revenue - Inflow)</p>
                <p class="text-lg font-semibold text-slate-900">{{ number_format($kpi['net'], 2, ',', '.') }}</p>
            </div>
            <div class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-2">
                <p class="text-[11px] text-slate-500">Pending sales</p>
                <p class="text-lg font-semibold text-slate-900">{{ number_format($kpi['pendingOrders']) }}</p>
            </div>
            <div class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-2">
                <p class="text-[11px] text-slate-500">Pending purchases</p>
                <p class="text-lg font-semibold text-slate-900">{{ number_format($kpi['pendingPurchases']) }}</p>
            </div>
            <div class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-2">
                <p class="text-[11px] text-slate-500">Due orders</p>
                <p class="text-lg font-semibold text-slate-900">
                    {{ number_format($kpi['dueOrders']) }}
                </p>
                <p class="text-[10px] text-slate-500">TODO: enable when due date tracking is added.</p>
            </div>
            <div class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-2">
                <p class="text-[11px] text-slate-500">Overdue</p>
                <p class="text-lg font-semibold text-slate-900">
                    {{ number_format($kpi['overdueOrders']) }}
                </p>
                <p class="text-[10px] text-slate-500">TODO: enable when due date tracking is added.</p>
            </div>
            <div class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-2">
                <p class="text-[11px] text-slate-500">Inflow (Rp)</p>
                <p class="text-lg font-semibold text-slate-900">{{ number_format($kpi['inflow'], 2, ',', '.') }}</p>
            </div>
            <div class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-2">
                <p class="text-[11px] text-slate-500">Outflow (Rp)</p>
                <p class="text-lg font-semibold text-slate-900">{{ number_format($kpi['outflow'], 2, ',', '.') }}</p>
            </div>
        </div>
    </div>

    {{-- Stock health + low stock --}}
    @if($canViewProducts)
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 space-y-2 md:col-span-1">
                <h2 class="text-[11px] font-semibold text-slate-800 uppercase tracking-wide">
                    Stock health
                </h2>
                <div class="space-y-2 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Total SKUs</span>
                        <span class="font-semibold text-slate-900">{{ number_format($stockHealth['totalSkus']) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Low stock items</span>
                        <span class="font-semibold text-amber-600">{{ number_format($stockHealth['lowStock']) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Out of stock</span>
                        <span class="font-semibold text-rose-600">{{ number_format($stockHealth['outOfStock']) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Total on-hand quantity</span>
                        <span class="font-semibold text-slate-900">{{ number_format($stockHealth['totalOnHand']) }}</span>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4 space-y-3 md:col-span-2">
                <div class="flex items-center justify-between">
                    <h2 class="text-[11px] font-semibold text-slate-800 uppercase tracking-wide">
                        Low stock items
                    </h2>
                </div>
                <div class="rounded-xl border border-slate-200 overflow-hidden">
                    <table class="min-w-full text-left text-xs">
                        <thead class="bg-slate-50 text-[11px] text-slate-500 uppercase tracking-wide">
                            <tr>
                                <th class="px-3 py-2">Product</th>
                                <th class="px-3 py-2 w-24 text-right">Current</th>
                                <th class="px-3 py-2 w-24 text-right">Min</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($lowStockProducts as $product)
                                <tr>
                                    <td class="px-3 py-2">
                                        <div class="flex flex-col">
                                            <span class="text-[12px] font-medium text-slate-900">{{ $product->name }}</span>
                                            <span class="text-[11px] text-slate-500">SKU: {{ $product->sku }}</span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2 text-right text-[12px] text-slate-900">
                                        {{ number_format((int) $product->current_stock) }}
                                    </td>
                                    <td class="px-3 py-2 text-right text-[12px] text-slate-900">
                                        {{ number_format((int) $product->min_stock) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-3 py-4 text-center text-[11px] text-slate-500">
                                        No products are below the minimum stock level.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- Restocks + sales --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @if($canViewRestocks)
            <div class="rounded-2xl border border-slate-200 bg-white p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-[11px] font-semibold text-slate-800 uppercase tracking-wide">
                        Purchases / restocks
                    </h2>
                    <a href="{{ route('restocks.index') }}" class="text-[11px] text-teal-700 font-semibold hover:underline">
                        View all
                    </a>
                </div>
                <div class="rounded-xl border border-slate-200 overflow-hidden">
                    <table class="min-w-full text-left text-xs">
                        <thead class="bg-slate-50 text-[11px] text-slate-500 uppercase tracking-wide">
                            <tr>
                                <th class="px-3 py-2">PO #</th>
                                <th class="px-3 py-2">Supplier</th>
                                <th class="px-3 py-2 w-28 text-right">Amount (Rp)</th>
                                <th class="px-3 py-2 w-28 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($recentRestocks as $restock)
                                <tr>
                                    <td class="px-3 py-2 font-mono text-[11px] text-slate-800">
                                        {{ $restock->po_number }}
                                    </td>
                                    <td class="px-3 py-2 text-[11px] text-slate-800">
                                        {{ $restock->supplier->name ?? '-' }}
                                    </td>
                                    <td class="px-3 py-2 text-right text-[12px] text-slate-900">
                                        {{ number_format((float) $restock->total_amount, 2, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        @include('components.status-badge', [
                                            'status' => $restock->status,
                                            'label' => $restock->status_label,
                                        ])
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-3 py-4 text-center text-[11px] text-slate-500">
                                        No recent restock orders.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if($canViewSales)
            <div class="rounded-2xl border border-slate-200 bg-white p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-[11px] font-semibold text-slate-800 uppercase tracking-wide">
                        Sales overview
                    </h2>
                    <a href="{{ route('sales.index') }}" class="text-[11px] text-teal-700 font-semibold hover:underline">
                        View all
                    </a>
                </div>
                <div class="rounded-xl border border-slate-200 overflow-hidden">
                    <table class="min-w-full text-left text-xs">
                        <thead class="bg-slate-50 text-[11px] text-slate-500 uppercase tracking-wide">
                            <tr>
                                <th class="px-3 py-2">SO #</th>
                                <th class="px-3 py-2">Order date</th>
                                <th class="px-3 py-2 w-24 text-right">Amount (Rp)</th>
                                <th class="px-3 py-2 w-28 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($recentSales as $sale)
                                @php
                                    $saleStatus = ucfirst(str_replace('_', ' ', $sale->status));
                                @endphp
                                <tr>
                                    <td class="px-3 py-2 font-mono text-[11px] text-slate-800">
                                        {{ $sale->transaction_number }}
                                    </td>
                                    <td class="px-3 py-2 text-[11px] text-slate-800">
                                        {{ $sale->transaction_date?->format('d M Y') ?? '-' }}
                                    </td>
                                    <td class="px-3 py-2 text-right text-[12px] text-slate-900">
                                        {{ number_format((float) $sale->total_amount, 2, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-[10px] font-semibold bg-slate-50 text-slate-700 border-slate-200">
                                            {{ $saleStatus }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-3 py-4 text-center text-[11px] text-slate-500">
                                        No recent sales orders.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    {{-- Product overview --}}
    @if($canViewProducts)
        <div class="rounded-2xl border border-slate-200 bg-white p-4 space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="text-[11px] font-semibold text-slate-800 uppercase tracking-wide">
                    Product overview
                </h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="rounded-xl border border-slate-200 overflow-hidden">
                    <table class="min-w-full text-left text-xs">
                        <thead class="bg-slate-50 text-[11px] text-slate-500 uppercase tracking-wide">
                            <tr>
                                <th class="px-3 py-2">Top stock</th>
                                <th class="px-3 py-2 w-28 text-right">On hand</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($topProducts as $product)
                                <tr>
                                    <td class="px-3 py-2">
                                        <div class="flex flex-col">
                                            <span class="text-[12px] font-medium text-slate-900">{{ $product->name }}</span>
                                            <span class="text-[11px] text-slate-500">SKU: {{ $product->sku }}</span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2 text-right text-[12px] text-slate-900">
                                        {{ number_format((int) $product->current_stock) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-3 py-4 text-center text-[11px] text-slate-500">
                                        No products to display.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="rounded-xl border border-slate-200 overflow-hidden">
                    <table class="min-w-full text-left text-xs">
                        <thead class="bg-slate-50 text-[11px] text-slate-500 uppercase tracking-wide">
                            <tr>
                                <th class="px-3 py-2">Low stock</th>
                                <th class="px-3 py-2 w-28 text-right">On hand</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($lowStockProducts as $product)
                                <tr>
                                    <td class="px-3 py-2">
                                        <div class="flex flex-col">
                                            <span class="text-[12px] font-medium text-slate-900">{{ $product->name }}</span>
                                            <span class="text-[11px] text-slate-500">SKU: {{ $product->sku }}</span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2 text-right text-[12px] text-slate-900">
                                        {{ number_format((int) $product->current_stock) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-3 py-4 text-center text-[11px] text-slate-500">
                                        No products to display.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
