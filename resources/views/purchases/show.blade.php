@extends('layouts.app')

@section('title', 'Detail Barang Masuk')

@section('page-header')
    <x-page-header
        title="Detail Barang Masuk"
        :description="'Transaksi #' . $purchase->transaction_number"
    />
@endsection

@section('content')
    <div class="max-w-6xl mx-auto space-y-6 text-sm text-slate-700">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <x-breadcrumbs :items="[
                'Transaksi' => route('transactions.index', ['tab' => 'incoming']),
                'Detail Barang Masuk' => route('purchases.show', $purchase),
            ]" />

            <div class="flex flex-wrap items-center gap-2 justify-end">
                <x-action-button href="{{ route('transactions.index', ['tab' => 'incoming']) }}" variant="secondary" icon="arrow-left">
                    Kembali
                </x-action-button>

                @if($purchase->isPending())
                    <x-action-button href="{{ route('purchases.edit', $purchase) }}" variant="primary" icon="edit">
                        Edit Data
                    </x-action-button>
                @endif
            </div>
        </div>

        @if(session('success'))
            <x-card class="p-4 bg-emerald-50 border-emerald-200 text-emerald-800">
                {{ session('success') }}
            </x-card>
        @endif

        @canany(['verify', 'reject', 'complete'], $purchase)
            <x-card class="p-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <p class="text-base font-semibold text-slate-900">Kelola Status</p>

                    <div class="flex flex-wrap items-center gap-2">
                        @can('verify', $purchase)
                            <form method="POST" action="{{ route('purchases.verify', $purchase) }}">
                                @csrf
                                @method('PATCH')
                                <x-action-button
                                    type="button"
                                    variant="primary"
                                    icon="check"
                                    onclick="return confirm('Verify this transaction and update stock?')"
                                >
                                    Verifikasi & tambah stok
                                </x-action-button>
                            </form>
                        @endcan

                        @can('complete', $purchase)
                            <form method="POST" action="{{ route('purchases.complete', $purchase) }}">
                                @csrf
                                @method('PATCH')
                                <x-action-button
                                    type="button"
                                    variant="secondary"
                                    icon="check-circle"
                                    onclick="return confirm('Mark this transaction as completed?')"
                                >
                                    Tandai selesai
                                </x-action-button>
                            </form>
                        @endcan

                        @can('reject', $purchase)
                            <form
                                method="POST"
                                action="{{ route('purchases.reject', $purchase) }}"
                                class="flex flex-wrap items-center gap-2"
                            >
                                @csrf
                                @method('PATCH')
                                <input
                                    type="text"
                                    name="reason"
                                    class="w-44 rounded-lg border border-slate-200 px-3 py-2 text-sm"
                                    placeholder="Alasan (opsional)"
                                >
                                <x-action-button
                                    type="button"
                                    variant="outline-danger"
                                    icon="x"
                                    onclick="return confirm('Reject this transaction?')"
                                >
                                    Tolak
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
                    <p class="text-base font-semibold text-slate-900">Informasi Supplier</p>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="text-slate-500">Nama</div>
                        <div class="font-medium text-slate-900">{{ optional($purchase->supplier)->name ?? '-' }}</div>

                        @if(optional($purchase->supplier)->contact_person)
                            <div class="text-slate-500">Kontak</div>
                            <div class="font-medium text-slate-900">{{ $purchase->supplier->contact_person }}</div>
                        @endif

                        @if(optional($purchase->supplier)->email)
                            <div class="text-slate-500">Email</div>
                            <div class="font-medium text-slate-900">{{ $purchase->supplier->email }}</div>
                        @endif

                        @if(optional($purchase->supplier)->phone)
                            <div class="text-slate-500">Telepon</div>
                            <div class="font-medium text-slate-900">{{ $purchase->supplier->phone }}</div>
                        @endif
                    </div>
                </div>

                <div class="space-y-3">
                    <p class="text-base font-semibold text-slate-900">Informasi Transaksi</p>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="text-slate-500">Nomor Transaksi</div>
                        <div class="font-medium text-slate-900">{{ $purchase->transaction_number }}</div>

                        <div class="text-slate-500">Jenis</div>
                        <div class="font-medium text-slate-900">Barang Masuk</div>

                        <div class="text-slate-500">Tanggal</div>
                        <div class="font-medium text-slate-900">{{ $purchase->transaction_date->format('d M Y') }}</div>

                        <div class="text-slate-500">Catatan</div>
                        <div class="text-slate-900">{{ $purchase->notes ?? '-' }}</div>
                    </div>
                </div>

                <div class="space-y-3">
                    <p class="text-base font-semibold text-slate-900">Informasi Tambahan</p>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="text-slate-500">Diverifikasi oleh</div>
                        <div class="font-medium text-slate-900">{{ optional($purchase->verifiedBy)->name ?? '-' }}</div>
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
                            <x-lucide-dollar-sign class="h-5 w-5" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-slate-500">Total Value</p>
                            <p class="text-base font-semibold text-slate-900">
                                Rp {{ number_format($purchase->total_amount, 2, ',', '.') }}
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
                                {{ number_format($item->unit_cost, 2, ',', '.') }}
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

                    @if($purchase->items->count() > 0)
                        <x-table.tr class="bg-slate-50 font-semibold">
                            <x-table.td colspan="4" align="right" class="text-slate-900">Total</x-table.td>
                            <x-table.td align="right" class="text-slate-900">
                                Rp {{ number_format($purchase->total_amount, 2, ',', '.') }}
                            </x-table.td>
                        </x-table.tr>
                    @endif
                </x-table.tbody>
            </x-table>
        </x-card>
    </div>
@endsection
