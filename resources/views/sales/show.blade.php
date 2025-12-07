@extends('layouts.app')

@section('title', 'Detail Barang Keluar')

@section('page-header')
    {{-- PAGE HEADER: Desktop --}}
    <div class="hidden md:block">
        <x-page-header title="Detail Barang Keluar" :description="'Bukti transaksi pengeluaran #' . $sale->transaction_number" />
    </div>

    {{-- PAGE HEADER: Mobile --}}
    <div class="md:hidden">
        <x-mobile-header title="Detail Barang Keluar" back="{{ route('transactions.index', ['tab' => 'outgoing']) }}" />
    </div>
@endsection

@section('content')
    <div class="max-w-6xl mx-auto">
        {{-- MOBILE CONTENT --}}
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

            {{-- SECTION: Summary --}}
            <x-mobile.card>
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <div class="text-xs text-slate-500">No Transaksi</div>
                        <div class="text-base font-medium text-slate-900">
                            #{{ $sale->transaction_number }}
                        </div>
                        <div class="mt-1 text-sm text-slate-900">
                            Customer: <span class="font-medium">{{ $sale->customer_name ?? '-' }}</span>
                        </div>
                    </div>
                    <x-badge :variant="$statusVariant" class="text-xs">
                        {{ $statusLabel }}
                    </x-badge>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-y-4 gap-x-2 text-xs">
                    <x-mobile.stat-row label="Tanggal" :value="$sale->transaction_date?->format('d M Y') ?? '-'" />
                    <x-mobile.stat-row label="Total Item" :value="number_format($totalItems, 0, ',', '.')" />
                    <x-mobile.stat-row label="Total Qty" :value="number_format($totalQty, 0, ',', '.')" />
                    <x-mobile.stat-row label="Total Nilai" :value="'Rp ' . number_format($totalValue, 0, ',', '.')" />
                </div>
            </x-mobile.card>

            {{-- MOBILE ACTIONS --}}
            @canany(['approve', 'ship'], $sale)
                <x-mobile.card>
                    <div class="space-y-3">
                        @can('approve', $sale)
                            <button type="button" x-data @click="$dispatch('open-confirm-modal', {
                                                                                    action: '{{ route('sales.approve', $sale) }}',
                                                                                    method: 'PATCH',
                                                                                    title: 'Approve Transaksi?',
                                                                                    message: 'Apakah Anda yakin ingin menyetujui transaksi ini? Stok akan dikurangi secara otomatis.',
                                                                                    btnText: 'Ya, Approve',
                                                                                    type: 'success'
                                                                                })"
                                class="w-full h-11 rounded-lg bg-teal-600 text-white text-sm font-semibold flex items-center justify-center gap-2 hover:bg-teal-700">
                                <x-lucide-check class="w-5 h-5" />
                                Approve & Kurangi Stok
                            </button>
                        @endcan

                        @can('ship', $sale)
                            <button type="button" x-data @click="$dispatch('open-confirm-modal', {
                                                                                    action: '{{ route('sales.ship', $sale) }}',
                                                                                    method: 'PATCH',
                                                                                    title: 'Tandai Terkirim?',
                                                                                    message: 'Apakah Anda yakin ingin menandai transaksi ini sebagai terkirim?',
                                                                                    btnText: 'Ya, Tandai Terkirim',
                                                                                    type: 'info'
                                                                                })"
                                class="w-full h-11 rounded-lg bg-slate-900 text-white text-sm font-semibold flex items-center justify-center gap-2 hover:bg-black">
                                <x-lucide-send class="w-5 h-5" />
                                Tandai Terkirim
                            </button>
                        @endcan
                    </div>
                </x-mobile.card>
            @endcanany

            {{-- SECTION: Info Detail --}}
            <x-mobile.card>
                <h2 class="text-sm font-semibold text-slate-900 mb-3">
                    Informasi Transaksi
                </h2>
                <div class="space-y-4 text-xs">
                    <x-mobile.stat-row label="Customer" :value="$sale->customer_name ?? '-'" />
                    <x-mobile.stat-row label="Disetujui oleh" :value="$sale->approvedBy?->name ?? '-'" />

                    @if($sale->notes)
                        <div class="pt-3 border-t border-slate-100">
                            <div class="text-xs text-slate-500 mb-1">Catatan</div>
                            <p class="leading-relaxed text-sm text-slate-900">
                                {!! nl2br(e($sale->notes)) !!}
                            </p>
                        </div>
                    @endif
                </div>
            </x-mobile.card>

            {{-- MOBILE LIST --}}
            <x-mobile.card>
                <h2 class="text-sm font-semibold text-slate-900 mb-4">
                    Detail Produk
                </h2>
                <div class="space-y-4 divide-y divide-slate-100">
                    @forelse($sale->items as $item)
                        <div class="{{ $loop->first ? '' : 'pt-4' }}">
                            <div class="font-medium text-slate-900 text-sm mb-1">
                                {{ optional($item->product)->name ?? '-' }}
                            </div>

                            <div class="flex justify-between items-start">
                                <div class="text-xs text-slate-500 space-y-0.5">
                                    <div>SKU: {{ optional($item->product)->sku ?? '-' }}</div>
                                </div>

                                <div class="text-right">
                                    <div class="text-xs text-slate-500 mt-0.5">
                                        {{ number_format($item->quantity, 0, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-slate-500 py-4 text-sm">
                            Tidak ada produk.
                        </div>
                    @endforelse
                </div>
            </x-mobile.card>
        </div>

        {{-- DESKTOP CONTENT --}}
        <div class="hidden md:block space-y-6 text-sm text-slate-700">
            {{-- TOOLBAR --}}
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                <x-breadcrumbs :items="[
            'Transaksi' => route('transactions.index'),
            'Barang Keluar' => route('transactions.index', ['tab' => 'outgoing']),
            '#'.$sale->transaction_number => route('sales.show', $sale),
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

            {{-- ACTIONS --}}
            @canany(['approve', 'ship'], $sale)
                <x-card class="p-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <p class="text-base font-semibold text-slate-900">Kelola Status</p>

                        <div class="flex flex-wrap items-center gap-2">
                            @can('approve', $sale)
                                <x-action-button type="button" variant="primary" icon="check" x-data @click="$dispatch('open-confirm-modal', {
                                                                                        action: '{{ route('sales.approve', $sale) }}',
                                                                                        method: 'PATCH',
                                                                                        title: 'Approve Transaksi?',
                                                                                        message: 'Apakah Anda yakin ingin menyetujui transaksi ini? Stok akan dikurangi secara otomatis.',
                                                                                        btnText: 'Ya, Approve',
                                                                                        type: 'success'
                                                                                    })">
                                    Approve & kurangi stok
                                </x-action-button>
                            @endcan

                            @can('ship', $sale)
                                <x-action-button type="button" variant="secondary" icon="send" x-data @click="$dispatch('open-confirm-modal', {
                                                                                        action: '{{ route('sales.ship', $sale) }}',
                                                                                        method: 'PATCH',
                                                                                        title: 'Tandai Terkirim?',
                                                                                        message: 'Apakah Anda yakin ingin menandai transaksi ini sebagai terkirim?',
                                                                                        btnText: 'Ya, Tandai Terkirim',
                                                                                        type: 'info'
                                                                                    })">
                                    Tandai terkirim
                                </x-action-button>
                            @endcan
                        </div>
                    </div>
                </x-card>
            @endcanany

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                {{-- SECTION: Info --}}
                <x-card class="p-6 space-y-6 lg:col-span-2">
                    <div class="space-y-3">
                        <p class="text-base font-semibold text-slate-900">Informasi Customer</p>

                        <div class="space-y-2">
                            <x-description-item label="Nama" :value="$sale->customer_name ?? '-'" icon="user" />
                        </div>
                    </div>

                    <div class="space-y-3">
                        <p class="text-base font-semibold text-slate-900">Informasi Transaksi</p>

                        <div class="space-y-2">
                            <x-description-item label="Nomor Transaksi" :value="$sale->transaction_number" icon="hash" />
                            <x-description-item label="Jenis" value="Barang Keluar" icon="log-out" />
                            <x-description-item label="Tanggal" :value="$sale->transaction_date->format('d M Y')" icon="calendar" />
                            <x-description-item label="Catatan" :value="$sale->notes ?? '-'" icon="notebook-pen" />
                        </div>
                    </div>

                    <div class="space-y-3">
                        <p class="text-base font-semibold text-slate-900">Informasi Tambahan</p>

                        <div class="space-y-2">
                            <x-description-item label="Disetujui oleh" :value="optional($sale->approvedBy)->name ?? '-'" icon="shield-check" />
                        </div>
                    </div>
                </x-card>

                {{-- STATS CARDS --}}
                <div class="space-y-3">
                    <x-card class="p-4">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-50 text-slate-600">
                                <x-lucide-package class="h-5 w-5" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-slate-500">Total Item</p>
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
                                <x-lucide-wallet class="h-5 w-5" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-slate-500">Total Nilai</p>
                                <p class="text-base font-semibold text-slate-900">
                                    Rp {{ number_format($sale->total_amount, 0, ',', '.') }}
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

            {{-- SECTION: Products --}}
            <x-card class="p-6 space-y-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <p class="text-base font-semibold text-slate-900">Produk</p>
                </div>

                <x-table>
                    <x-table.thead>
                        <x-table.th>Produk</x-table.th>
                        <x-table.th>SKU</x-table.th>
                        <x-table.th align="right">Qty</x-table.th>
                        <x-table.th align="right">Harga Jual</x-table.th>
                        <x-table.th align="right">Subtotal</x-table.th>
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
                                <x-table.td align="right">
                                    {{ number_format($item->quantity, 0, ',', '.') }}
                                </x-table.td>
                                <x-table.td align="right">
                                    <x-money :value="$item->unit_price" />
                                </x-table.td>
                                <x-table.td align="right" class="font-semibold text-slate-900">
                                    <x-money :value="$item->unit_price * $item->quantity" />
                                </x-table.td>
                            </x-table.tr>
                        @empty
                            <x-table.tr>
                                <x-table.td colspan="5" class="text-center text-slate-500">
                                    Tidak ada produk pada transaksi ini.
                                </x-table.td>
                            </x-table.tr>
                        @endforelse
                    </x-table.tbody>
                </x-table>
            </x-card>
        </div>
    </div>

    <x-confirm-modal />
@endsection
