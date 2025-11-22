@extends('layouts.app')

@section('title', 'Manager Dashboard')

@section('page-header')
    <div class="flex flex-col">
        <h1 class="text-base font-semibold text-slate-900">Manager dashboard</h1>
        <p class="text-xs text-slate-500">
            Approvals, stock health, and restock progress at a glance.
        </p>
    </div>
@endsection

@section('content')
    <div class="space-y-4 text-xs max-w-6xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-[11px] text-slate-500">Pending purchases</p>
                <p class="text-2xl font-semibold text-slate-900">{{ number_format($pendingPurchases) }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-[11px] text-slate-500">Pending sales approvals</p>
                <p class="text-2xl font-semibold text-slate-900">{{ number_format($pendingSales) }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-[11px] text-slate-500">Pending restocks</p>
                <p class="text-2xl font-semibold text-slate-900">{{ number_format($pendingRestocks) }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-[11px] font-semibold text-slate-800 uppercase tracking-wide">
                        Low stock products
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
                                        No products are below the minimum stock.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-[11px] font-semibold text-slate-800 uppercase tracking-wide">
                        Restocks in progress
                    </h2>
                </div>
                <div class="rounded-xl border border-slate-200 overflow-hidden">
                    <table class="min-w-full text-left text-xs">
                        <thead class="bg-slate-50 text-[11px] text-slate-500 uppercase tracking-wide">
                            <tr>
                                <th class="px-3 py-2">PO #</th>
                                <th class="px-3 py-2">Supplier</th>
                                <th class="px-3 py-2 w-24 text-right">Quantity</th>
                                <th class="px-3 py-2 w-28 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($restocksInProgress as $restock)
                                <tr>
                                    <td class="px-3 py-2 font-mono text-[11px] text-slate-800">
                                        {{ $restock->po_number }}
                                    </td>
                                    <td class="px-3 py-2 text-[11px] text-slate-800">
                                        {{ $restock->supplier->name ?? '-' }}
                                    </td>
                                    <td class="px-3 py-2 text-right text-[12px] text-slate-900">
                                        {{ number_format((int) $restock->total_quantity) }}
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        @include('admin.components.status-badge', [
                                            'status' => $restock->status,
                                            'label' => $restock->status_label,
                                        ])
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-3 py-4 text-center text-[11px] text-slate-500">
                                        No restocks are currently in transit or confirmed.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
