@extends('layouts.app')

@section('title', 'Detail Barang Masuk')

@section('page-header')
    {{-- PAGE HEADER: Desktop --}}
    <div class="hidden md:block">
        <x-page-header title="Detail Barang Masuk" :description="'Bukti transaksi penerimaan #' . $purchase->transaction_number" />
    </div>

    {{-- PAGE HEADER: Mobile --}}
    <div class="md:hidden">
        <x-mobile-header title="Detail Barang Masuk" back="{{ route('transactions.index', ['tab' => 'incoming']) }}" />
    </div>
@endsection

@section('content')
    <div class="max-w-6xl mx-auto">
        {{-- MOBILE CONTENT --}}
        <div class="md:hidden space-y-3 pb-24">
            @php
                $statusLabel = match (true) {
                    $purchase->isPending() => 'Pending',
                    $purchase->isVerified() => 'Verified',
                    $purchase->isCompleted() => 'Completed',
                    $purchase->isRejected() => 'Rejected',
                    default => ucfirst($purchase->status)
                };

                $statusVariant = match (true) {
                    $purchase->isPending() => 'warning',
                    $purchase->isVerified() => 'info',
                    $purchase->isCompleted() => 'success',
                    $purchase->isRejected() => 'danger',
                    default => 'neutral'
                };

                $totalItems = $purchase->total_items ?? $purchase->items->count();
                $totalQty = $purchase->total_quantity;
                $totalValue = $purchase->total_amount;
            @endphp

            {{-- SECTION: Summary --}}
            <x-mobile.card>
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <div class="text-xs text-slate-500">No Transaksi</div>
                        <div class="text-base font-medium text-slate-900">
                            #{{ $purchase->transaction_number }}
                        </div>
                        <div class="mt-1 text-sm text-slate-900">
                            Supplier: <span class="font-medium">{{ $purchase->supplier?->name ?? '-' }}</span>
                        </div>
                    </div>
                    <x-badge :variant="$statusVariant" class="text-xs">
                        {{ $statusLabel }}
                    </x-badge>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-y-4 gap-x-2 text-xs">
                    <x-mobile.stat-row label="Tanggal" :value="$purchase->transaction_date?->format('d M Y') ?? '-'" />
                    <x-mobile.stat-row label="Total Item" :value="number_format($totalItems, 0, ',', '.')" />
                    <x-mobile.stat-row label="Total Qty" :value="number_format($totalQty, 0, ',', '.')" />
                </div>
            </x-mobile.card>

            {{-- MOBILE ACTIONS --}}
            @canany(['verify', 'reject', 'complete'], $purchase)
                <x-mobile.card>
                    <div class="space-y-3">
                        @can('verify', $purchase)
                            <button type="button" x-data @click="$dispatch('open-confirm-modal', {
                                                                        action: '{{ route('purchases.verify', $purchase) }}',
                                                                        method: 'PATCH',
                                                                        title: 'Verifikasi Transaksi?',
                                                                        message: 'Verifikasi transaksi ini dan perbarui stok?',
                                                                        btnText: 'Ya, Verifikasi',
                                                                        type: 'success'
                                                                    })"
                                class="w-full h-11 rounded-lg bg-teal-600 text-white text-sm font-semibold flex items-center justify-center gap-2 hover:bg-teal-700">
                                <x-lucide-check class="w-5 h-5" />
                                Verifikasi
                            </button>
                        @endcan

                        @can('complete', $purchase)
                            <button type="button" x-data @click="$dispatch('open-confirm-modal', {
                                                                        action: '{{ route('purchases.complete', $purchase) }}',
                                                                        method: 'PATCH',
                                                                        title: 'Tandai Selesai?',
                                                                        message: 'Apakah Anda yakin ingin menandai transaksi ini sebagai selesai?',
                                                                        btnText: 'Ya, Tandai Selesai',
                                                                        type: 'success'
                                                                    })"
                                class="w-full h-11 rounded-lg bg-slate-900 text-white text-sm font-semibold flex items-center justify-center gap-2 hover:bg-black">
                                <x-lucide-check-circle class="w-5 h-5" />
                                Tandai Selesai
                            </button>
                        @endcan

                        @can('reject', $purchase)
                            <button type="button" x-data @click="$dispatch('open-confirm-modal', {
                                                                        action: '{{ route('purchases.reject', $purchase) }}',
                                                                        method: 'PATCH',
                                                                        title: 'Tolak Transaksi?',
                                                                        message: 'Apakah Anda yakin ingin menolak transaksi ini?',
                                                                        btnText: 'Ya, Tolak',
                                                                        type: 'danger',
                                                                        inputName: 'reason',
                                                                        inputLabel: 'Alasan Penolakan',
                                                                        inputPlaceholder: 'Masukkan alasan penolakan (opsional)'
                                                                    })"
                                class="w-full h-11 rounded-lg bg-rose-100 text-rose-700 text-sm font-semibold flex items-center justify-center gap-2 hover:bg-rose-200">
                                <x-lucide-x class="w-5 h-5" />
                                Tolak
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
                    <x-mobile.stat-row label="Supplier" :value="$purchase->supplier?->name ?? '-'" />
                    <x-mobile.stat-row label="Diverifikasi oleh" :value="$purchase->verifiedBy?->name ?? '-'" />

                    @if($purchase->notes)
                        <div class="pt-3 border-t border-slate-100">
                            <div class="text-xs text-slate-500 mb-1">Catatan</div>
                            <p class="leading-relaxed text-sm text-slate-900">
                                {!! nl2br(e($purchase->notes)) !!}
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
                    @forelse($purchase->items as $item)
                        <div class="{{ $loop->first ? '' : 'pt-4' }}">
                            {{-- Baris 1: Nama Produk --}}
                            <div class="font-medium text-slate-900 text-sm mb-1">
                                {{ optional($item->product)->name ?? '-' }}
                            </div>

                            <div class="flex justify-between items-start">
                                {{-- Baris 2: SKU --}}
                                <div class="text-xs text-slate-500 space-y-0.5">
                                    <div>SKU: {{ optional($item->product)->sku ?? '-' }}</div>
                                </div>

                                {{-- Baris 3: Qty --}}
                                <div class="text-right">
                                    <div class="text-xs text-slate-500 mt-0.5">
                                        x{{ number_format($item->quantity, 0, ',', '.') }}
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
            'Barang Masuk' => route('transactions.index', ['tab' => 'incoming']),
            '#'.$purchase->transaction_number => route('purchases.show', $purchase),
        ]" />

                <div class="flex flex-wrap items-center gap-2 justify-end">
                    <x-action-button href="{{ route('transactions.index', ['tab' => 'incoming']) }}" variant="secondary"
                        icon="arrow-left">
                        Kembali
                    </x-action-button>

                    @if($purchase->isPending())
                        <x-action-button href="{{ route('purchases.edit', $purchase) }}" variant="primary" icon="edit">
                            Edit Data
                        </x-action-button>
                    @endif
                </div>
            </div>

            {{-- ACTIONS --}}
            @canany(['verify', 'reject', 'complete'], $purchase)
                <x-card class="p-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <p class="text-base font-semibold text-slate-900">Kelola Status</p>

                        <div class="flex flex-wrap items-center gap-2">
                            @can('verify', $purchase)
                                <x-action-button type="button" variant="primary" icon="check" x-data @click="$dispatch('open-confirm-modal', {
                                                                    action: '{{ route('purchases.verify', $purchase) }}',
                                                                    method: 'PATCH',
                                                                    title: 'Verifikasi Transaksi?',
                                                                    message: 'Verifikasi transaksi ini dan perbarui stok?',
                                                                    btnText: 'Ya, Verifikasi',
                                                                    type: 'success'
                                                                })">
                                    Verifikasi
                                </x-action-button>
                            @endcan

                            @can('complete', $purchase)
                                <x-action-button type="button" variant="secondary" icon="check-circle" x-data @click="$dispatch('open-confirm-modal', {
                                                                    action: '{{ route('purchases.complete', $purchase) }}',
                                                                    method: 'PATCH',
                                                                    title: 'Tandai Selesai?',
                                                                    message: 'Apakah Anda yakin ingin menandai transaksi ini sebagai selesai?',
                                                                    btnText: 'Ya, Tandai Selesai',
                                                                    type: 'success'
                                                                })">
                                    Tandai selesai
                                </x-action-button>
                            @endcan

                            @can('reject', $purchase)
                                <x-action-button type="button" variant="outline-danger" icon="x" x-data @click="$dispatch('open-confirm-modal', {
                                                                    action: '{{ route('purchases.reject', $purchase) }}',
                                                                    method: 'PATCH',
                                                                    title: 'Tolak Transaksi?',
                                                                    message: 'Apakah Anda yakin ingin menolak transaksi ini?',
                                                                    btnText: 'Ya, Tolak',
                                                                    type: 'danger',
                                                                    inputName: 'reason',
                                                                    inputLabel: 'Alasan Penolakan',
                                                                    inputPlaceholder: 'Masukkan alasan penolakan (opsional)'
                                                                })">
                                    Tolak
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
                        <p class="text-base font-semibold text-slate-900">Informasi Supplier</p>
                        <div class="space-y-2">
                            <x-description-item label="Nama" :value="optional($purchase->supplier)->name ?? '-'" icon="building-2" />
                            @if(optional($purchase->supplier)->contact_person)
                                <x-description-item label="Kontak" :value="$purchase->supplier->contact_person" icon="user-round" />
                            @endif
                            @if(optional($purchase->supplier)->email)
                                <x-description-item label="Email" :value="$purchase->supplier->email" icon="mail" />
                            @endif
                            @if(optional($purchase->supplier)->phone)
                                <x-description-item label="Telepon" :value="$purchase->supplier->phone" icon="phone" />
                            @endif
                        </div>
                    </div>

                    <div class="space-y-3">
                        <p class="text-base font-semibold text-slate-900">Informasi Transaksi</p>
                        <div class="space-y-2">
                            <x-description-item label="Nomor Transaksi" :value="$purchase->transaction_number" icon="hash" />
                            <x-description-item label="Jenis" value="Barang Masuk" icon="log-in" />
                            <x-description-item label="Tanggal" :value="$purchase->transaction_date->format('d M Y')" icon="calendar" />
                            <x-description-item label="Catatan" :value="$purchase->notes ?? '-'" icon="notebook-pen" />
                        </div>
                    </div>

                    <div class="space-y-3">
                        <p class="text-base font-semibold text-slate-900">Informasi Tambahan</p>
                        <div class="space-y-2">
                            <x-description-item label="Diverifikasi oleh" :value="optional($purchase->verifiedBy)->name ?? '-'" icon="shield-check" />
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
                                    {{ $purchase->total_items ?? $purchase->items->count() }}
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
                                    {{ number_format($purchase->total_quantity, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </x-card>

                    <x-card class="p-4">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-50 text-slate-600">
                                @if($purchase->isPending())
                                    <x-lucide-clock class="h-5 w-5" />
                                @elseif($purchase->isVerified())
                                    <x-lucide-check-circle class="h-5 w-5" />
                                @elseif($purchase->isCompleted())
                                    <x-lucide-check-circle-2 class="h-5 w-5" />
                                @elseif($purchase->isRejected())
                                    <x-lucide-x-circle class="h-5 w-5" />
                                @endif
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-slate-500">Status</p>
                                <p class="text-base font-semibold text-slate-900">
                                    @if($purchase->isPending())
                                        Pending
                                    @elseif($purchase->isVerified())
                                        Verified
                                    @elseif($purchase->isCompleted())
                                        Completed
                                    @elseif($purchase->isRejected())
                                        Rejected
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
                        <x-table.th align="right">Harga Beli</x-table.th>
                        <x-table.th align="right">Subtotal</x-table.th>
                    </x-table.thead>

                    <x-table.tbody>
                        @forelse($purchase->items as $item)
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
                                    Rp {{ number_format((float) $item->unit_cost, 2, ',', '.') }}
                                </x-table.td>
                                <x-table.td align="right" class="font-semibold text-slate-900">
                                    Rp {{ number_format((float) $item->line_total, 2, ',', '.') }}
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
