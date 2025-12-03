@extends('layouts.app')

@section('title', 'Detail Barang Keluar')

@section('page-header')
    <div class="hidden md:block">
        <x-page-header title="Detail Barang Keluar" :description="'Transaksi #' . $sale->transaction_number" />
    </div>

    <div class="md:hidden">
        <x-mobile-header title="Detail Barang Keluar" back="{{ route('transactions.index', ['tab' => 'outgoing']) }}" />
    </div>
@endsection

@section('content')
    <div class="max-w-6xl mx-auto">
        {{-- MOBILE --}}
        <div class="md:hidden space-y-3 pb-24">
            @php
                $statusLabel = match (true) {
                    $sale->isPending() => 'Pending',
                    $sale->isApproved() => 'Approved',
                    $sale->isShipped() => 'Shipped',
                    default => ucfirst($sale->status)
                };

                $statusVariant = match (true) {
                    $sale->isPending() => 'warning',
                    $sale->isApproved() => 'info',
                    $sale->isShipped() => 'success',
                    default => 'neutral'
                };

                $totalItems = $sale->total_items ?? $sale->items->count();
                $totalQty = $sale->total_quantity;
                $totalValue = $sale->total_amount;
            @endphp

            {{-- SUMMARY --}}
            <x-mobile.card>
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <div class="text-xs text-slate-400">No Transaksi</div>
                        <div class="text-sm font-semibold text-slate-900">
                            #{{ $sale->transaction_number }}
                        </div>
                        <div class="mt-1 text-[11px] text-slate-500">
                            Customer: {{ $sale->customer_name ?? '-' }}
                        </div>
                    </div>
                    <x-badge :variant="$statusVariant">
                        {{ $statusLabel }}
                    </x-badge>
                </div>

                <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                    <x-mobile.stat-row label="Tanggal" :value="$sale->transaction_date?->format('d M Y') ?? '-'" />
                    <x-mobile.stat-row label="Total Item" :value="number_format($totalItems, 0, ',', '.')" />
                    <x-mobile.stat-row label="Total Qty" :value="number_format($totalQty, 0, ',', '.')" />
                    <x-mobile.stat-row label="Total Nilai" prefix="Rp" :value="number_format($totalValue, 0, ',', '.')" />
                </div>
            </x-mobile.card>

            {{-- ACTION CARD --}}
            @canany(['approve', 'ship'], $sale)
                <x-mobile.card>
                    <div class="space-y-2 text-xs">
                        @can('approve', $sale)
                            <form method="POST" action="{{ route('sales.approve', $sale) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                    class="w-full h-9 rounded-lg bg-teal-600 text-white font-semibold flex items-center justify-center gap-2 hover:bg-teal-700"
                                    onclick="return confirm('Approve this transaction and reduce stock?')">
                                    <x-lucide-check class="w-4 h-4" />
                                    Approve & Kurangi Stok
                                </button>
                            </form>
                        @endcan

                        @can('ship', $sale)
                            <form method="POST" action="{{ route('sales.ship', $sale) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                    class="w-full h-9 rounded-lg bg-slate-900 text-white font-semibold flex items-center justify-center gap-2 hover:bg-black"
                                    onclick="return confirm('Mark this transaction as shipped?')">
                                    <x-lucide-send class="w-4 h-4" />
                                    Tandai Terkirim
                                </button>
                            </form>
                        @endcan
                    </div>
                </x-mobile.card>
            @endcanany

            {{-- INFO DETAIL --}}
            <x-mobile.card>
                <h2 class="text-sm font-semibold text-slate-900 mb-2">
                    Informasi Transaksi
                </h2>
                <div class="space-y-2 text-xs">
                    <x-mobile.stat-row label="Customer" :value="$sale->customer_name ?? '-'" />
                    <x-mobile.stat-row label="Disetujui oleh" :value="$sale->approvedBy?->name ?? '-'" />

                    @if($sale->notes)
                        <div class="pt-2 border-t border-slate-100 text-[11px] text-slate-500">
                            <div class="font-semibold text-slate-700 mb-1">Catatan</div>
                            <p class="leading-relaxed">
                                {!! nl2br(e($sale->notes)) !!}
                            </p>
                        </div>
                    @endif
                </div>
            </x-mobile.card>

            {{-- TABEL PRODUK --}}
            <x-mobile.card>
                <h2 class="text-sm font-semibold text-slate-900 mb-2">
                    Detail Produk
                </h2>
                <div class="overflow-x-auto -mx-4 px-4">
                    <x-table>
                        <x-table.thead>
                            <x-table.th>Product</x-table.th>
                            <x-table.th align="right">Qty</x-table.th>
                            <x-table.th align="right">Price</x-table.th>
                            <x-table.th align="right">Subtotal</x-table.th>
                        </x-table.thead>

                        <x-table.tbody>
                            @forelse($sale->items as $item)
                                <x-table.tr>
                                    <x-table.td>
                                        <div class="flex flex-col">
                                            <span class="font-medium text-slate-900">
                                                {{ optional($item->product)->name ?? '-' }}
                                            </span>
                                            <span class="text-xs text-slate-500">
                                                {{ optional($item->product)->sku ?? '-' }}
                                            </span>
                                        </div>
                                    </x-table.td>
                                    <x-table.td align="right" class="font-semibold text-slate-900">
                                        {{ number_format($item->quantity, 0, ',', '.') }}
                                    </x-table.td>
                                    <x-table.td align="right">
                                        {{ number_format($item->unit_price, 2, ',', '.') }}
                                    </x-table.td>
                                    <x-table.td align="right" class="font-semibold text-slate-900">
                                        {{ number_format($item->line_total, 2, ',', '.') }}
                                    </x-table.td>
                                </x-table.tr>
                            @empty
                                <x-table.tr>
                                    <x-table.td colspan="4" class="text-center text-slate-500">
                                        Tidak ada produk.
                                    </x-table.td>
                                </x-table.tr>
                            @endforelse

                            @if($sale->items->count() > 0)
                                <x-table.tr class="bg-slate-50 font-semibold">
                                    <x-table.td colspan="3" align="right" class="text-slate-900">Total</x-table.td>
                                    <x-table.td align="right" class="text-slate-900">
                                        Rp {{ number_format($sale->total_amount, 2, ',', '.') }}
                                    </x-table.td>
                                </x-table.tr>
                            @endif
                        </x-table.tbody>
                    </x-table>
                </div>
            </x-mobile.card>
        </div>

        {{-- DESKTOP --}}
        <div class="hidden md:block space-y-6 text-sm text-slate-700">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                <x-breadcrumbs :items="[
            'Transaksi' => route('transactions.index', ['tab' => 'outgoing']),
            'Detail Barang Keluar' => route('sales.show', $sale),
        ]" />

                <div class="flex flex-wrap items-center gap-2 justify-end">
                    <x-action-button href="{{ route('transactions.index', ['tab' => 'outgoing']) }}" variant="secondary"
                        icon="arrow-left">
                        Kembali
                    </x-action-button>

                    @if($sale->isPending())
                        <x-action-button href="{{ route('sales.edit', $sale) }}" variant="primary" icon="edit">
                            Edit Data
                        </x-action-button>
                    @endif
                </div>
            </div>

            @canany(['approve', 'ship'], $sale)
                <x-card class="p-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <p class="text-base font-semibold text-slate-900">Kelola Status</p>

                        <div class="flex flex-wrap items-center gap-2">
                            @can('approve', $sale)
                                <form method="POST" action="{{ route('sales.approve', $sale) }}">
                                    @csrf
                                    @method('PATCH')
                                    <x-action-button type="button" variant="primary" icon="check"
                                        onclick="return confirm('Approve this transaction and reduce stock?')">
                                        Approve & kurangi stok
                                    </x-action-button>
                                </form>
                            @endcan

                            @can('ship', $sale)
                                <form method="POST" action="{{ route('sales.ship', $sale) }}">
                                    @csrf
                                    @method('PATCH')
                                    <x-action-button type="button" variant="secondary" icon="send"
                                        onclick="return confirm('Mark this transaction as shipped?')">
                                        Tandai terkirim
                                    </x-action-button>
                                </form>
                            @endcan
                        </div>
                    </div>
                </x-card>
            @endcanany

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <x-card class="p-6 space-y-6 lg:col-span-2">
                    <div class="space-y-3">
                        <p class="text-base font-semibold text-slate-900">Informasi Customer</p>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="text-slate-500">Nama</div>
                            <div class="font-medium text-slate-900">{{ $sale->customer_name ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <p class="text-base font-semibold text-slate-900">Informasi Transaksi</p>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="text-slate-500">Nomor Transaksi</div>
                            <div class="font-medium text-slate-900">{{ $sale->transaction_number }}</div>

                            <div class="text-slate-500">Jenis</div>
                            <div class="font-medium text-slate-900">Barang Keluar</div>

                            <div class="text-slate-500">Tanggal</div>
                            <div class="font-medium text-slate-900">{{ $sale->transaction_date->format('d M Y') }}</div>

                            <div class="text-slate-500">Catatan</div>
                            <div class="text-slate-900">{{ $sale->notes ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <p class="text-base font-semibold text-slate-900">Informasi Tambahan</p>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="text-slate-500">Disetujui oleh</div>
                            <div class="font-medium text-slate-900">{{ optional($sale->approvedBy)->name ?? '-' }}</div>
                        </div>
                    </div>
                </x-card>

                <div class="space-y-3">
                    <x-card class="p-4">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-50 text-slate-600">
                                <x-lucide-package class="h-5 w-5" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-slate-500">Total Items</p>
                                <p class="text-base font-semibold text-slate-900">
                                    {{ $sale->total_items ?? $sale->items->count() }}
                                </p>
                            </div>
                        </div>
                    </x-card>

                    <x-card class="p-4">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-50 text-slate-600">
                                <x-lucide-layers class="h-5 w-5" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-slate-500">Total Qty</p>
                                <p class="text-base font-semibold text-slate-900">
                                    {{ number_format($sale->total_quantity, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </x-card>

                    <x-card class="p-4">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-50 text-slate-600">
                                <x-lucide-dollar-sign class="h-5 w-5" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-slate-500">Total Value</p>
                                <p class="text-base font-semibold text-slate-900">
                                    Rp {{ number_format($sale->total_amount, 2, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </x-card>

                    <x-card class="p-4">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-50 text-slate-600">
                                @if($sale->isPending())
                                    <x-lucide-clock class="h-5 w-5" />
                                @elseif($sale->isApproved())
                                    <x-lucide-check-circle class="h-5 w-5" />
                                @elseif($sale->isShipped())
                                    <x-lucide-check-circle-2 class="h-5 w-5" />
                                @endif
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-slate-500">Status</p>
                                <p class="text-base font-semibold text-slate-900">
                                    @if($sale->isPending())
                                        Pending
                                    @elseif($sale->isApproved())
                                        Approved
                                    @elseif($sale->isShipped())
                                        Shipped
                                    @endif
                                </p>
                            </div>
                        </div>
                    </x-card>
                </div>
            </div>

            <x-card class="p-6 space-y-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <p class="text-base font-semibold text-slate-900">Produk</p>
                </div>

                <x-table>
                    <x-table.thead>
                        <x-table.th>Product Name</x-table.th>
                        <x-table.th>SKU</x-table.th>
                        <x-table.th align="right">Qty</x-table.th>
                        <x-table.th align="right">Unit Price (Rp)</x-table.th>
                        <x-table.th align="right">Subtotal (Rp)</x-table.th>
                    </x-table.thead>

                    <x-table.tbody>
                        @forelse($sale->items as $item)
                            <x-table.tr>
                                <x-table.td>
                                    <p class="font-medium text-slate-900">
                                        {{ optional($item->product)->name ?? '-' }}
                                    </p>
                                </x-table.td>
                                <x-table.td class="text-slate-500">
                                    {{ optional($item->product)->sku ?? '-' }}
                                </x-table.td>
                                <x-table.td align="right" class="font-semibold text-slate-900">
                                    {{ number_format($item->quantity, 0, ',', '.') }}
                                </x-table.td>
                                <x-table.td align="right">
                                    {{ number_format($item->unit_price, 2, ',', '.') }}
                                </x-table.td>
                                <x-table.td align="right" class="font-semibold text-slate-900">
                                    {{ number_format($item->line_total, 2, ',', '.') }}
                                </x-table.td>
                            </x-table.tr>
                        @empty
                            <x-table.tr>
                                <x-table.td colspan="5" class="text-center text-slate-500">
                                    Tidak ada produk pada transaksi ini.
                                </x-table.td>
                            </x-table.tr>
                        @endforelse

                        @if($sale->items->count() > 0)
                            <x-table.tr class="bg-slate-50 font-semibold">
                                <x-table.td colspan="4" align="right" class="text-slate-900">Total</x-table.td>
                                <x-table.td align="right" class="text-slate-900">
                                    Rp {{ number_format($sale->total_amount, 2, ',', '.') }}
                                </x-table.td>
                            </x-table.tr>
                        @endif
                    </x-table.tbody>
                </x-table>
            </x-card>
        </div>
    </div>
@endsection