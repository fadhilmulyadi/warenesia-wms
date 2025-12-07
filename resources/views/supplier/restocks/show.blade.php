@extends('layouts.app')

@section('title', 'Restock #' . $restock->po_number)

@section('page-header')
    {{-- PAGE HEADER: Desktop --}}
    <div class="hidden md:block">
        <x-page-header
            :title="'Restock #' . $restock->po_number"
            :description="'Rincian item yang harus dikirim ke gudang'"
        />
    </div>

    {{-- PAGE HEADER: Mobile --}}
    <div class="md:hidden">
        <x-mobile-header
            title="Restock #{{ $restock->po_number }}"
            back="{{ route('supplier.restocks.index') }}"
        />
    </div>
@endsection

@section('content')
    <div class="max-w-6xl mx-auto">
        {{-- MOBILE CONTENT --}}
        <div class="md:hidden space-y-3 pb-24">
            @php
                $statusLabel = $restock->status_label ?? ucfirst($restock->status);
                $statusVariant = match($restock->status) {
                    'pending' => 'warning',
                    'confirmed' => 'info',
                    'in_transit' => 'primary',
                    'received' => 'success',
                    'cancelled' => 'danger',
                    'rejected' => 'danger',
                    default => 'neutral',
                };

                $totalItems = $restock->items->count();
                $totalQty = $restock->items->sum('quantity');
                $totalValue = $restock->items->sum('line_total');
            @endphp

            {{-- SECTION: Summary --}}
            <x-mobile.card>
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <div class="text-xs text-slate-500">PO Number</div>
                        <div class="text-base font-medium text-slate-900">
                            #{{ $restock->po_number }}
                        </div>
                        <div class="mt-1 text-sm text-slate-900">
                            Supplier: <span class="font-medium">{{ $restock->supplier?->name ?? '-' }}</span>
                        </div>
                    </div>
                    <x-badge :variant="$statusVariant" class="text-xs">
                        {{ $statusLabel }}
                    </x-badge>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-y-4 gap-x-2 text-xs">
                    <x-mobile.stat-row 
                        label="Tgl Order"
                        :value="$restock->order_date?->format('d M Y') ?? '-'"
                    />
                    <x-mobile.stat-row 
                        :label="$restock->status === 'received' ? 'Tanggal Diterima' : 'Perkiraan Tiba'"
                        :value="$restock->status === 'received' ? ($restock->incomingTransaction?->transaction_date?->format('d M Y') ?? $restock->updated_at->format('d M Y')) : ($restock->expected_delivery_date?->format('d M Y') ?? '-')"
                    />
                    <x-mobile.stat-row 
                        label="Total Item"
                        :value="number_format($totalItems, 0, ',', '.')"
                    />
                    <x-mobile.stat-row 
                        label="Total Qty"
                        :value="number_format($totalQty, 0, ',', '.')"
                    />
                    <x-mobile.stat-row 
                        label="Total Nilai"
                        prefix="Rp"
                        :value="number_format($totalValue, 0, ',', '.')"
                    />
                </div>
            </x-mobile.card>

            {{-- SECTION: Actions --}}
            @canany(['confirmSupplierRestock', 'rejectSupplierRestock'], $restock)
            <x-mobile.card>
                <div class="space-y-3">
                    @can('confirmSupplierRestock', $restock)
                        <button 
                            type="button"
                            x-data
                            @click="$dispatch('open-confirm-modal', {
                                action: '{{ route('supplier.restocks.confirm', $restock) }}',
                                method: 'PATCH',
                                title: 'Konfirmasi Pesanan?',
                                message: 'Apakah Anda yakin ingin menerima pesanan ini? Status akan berubah menjadi Approved.',
                                btnText: 'Ya, Terima Pesanan',
                                type: 'success'
                            })"
                            class="w-full h-11 rounded-lg bg-emerald-600 text-white font-semibold flex items-center justify-center gap-2 hover:bg-emerald-700 text-sm"
                        >
                            <x-lucide-check-circle class="w-5 h-5" />
                            Terima Pesanan
                        </button>
                    @endcan

                    @can('rejectSupplierRestock', $restock)
                        <button 
                            type="button"
                            x-data
                            @click="$dispatch('open-confirm-modal', {
                                action: '{{ route('supplier.restocks.reject', $restock) }}',
                                method: 'PATCH',
                                title: 'Tolak Pesanan?',
                                message: 'Apakah Anda yakin ingin menolak pesanan ini? Silakan berikan alasan penolakan.',
                                btnText: 'Tolak Pesanan',
                                btnClass: 'bg-red-600 hover:bg-red-500 text-white',
                                type: 'danger',
                                inputName: 'reject_reason',
                                inputLabel: 'Alasan Penolakan (Opsional)',
                                inputPlaceholder: 'Contoh: Stok barang kosong, harga berubah, dll.',
                                inputType: 'textarea'
                            })"
                            class="w-full h-11 rounded-lg bg-rose-100 text-rose-700 font-semibold flex items-center justify-center gap-2 hover:bg-rose-200 text-sm"
                        >
                            <x-lucide-x-circle class="w-5 h-5" />
                            Tolak Pesanan
                        </button>
                    @endcan
                </div>
            </x-mobile.card>
            @endcanany

            {{-- SECTION: Timeline --}}
            <x-mobile.card>
                <h2 class="text-sm font-semibold text-slate-900 mb-3">
                    Status Restock
                </h2>
                @include('components.mobile-restocks-status-timeline', ['status' => $restock->status])
            </x-mobile.card>

            {{-- SECTION: Info Detail --}}
            <x-mobile.card>
                <h2 class="text-sm font-semibold text-slate-900 mb-3">
                    Informasi Pesanan
                </h2>
                <div class="space-y-4 text-xs">
                    <x-mobile.stat-row 
                        label="Supplier"
                        :value="$restock->supplier?->name ?? '-'"
                    />
                    <x-mobile.stat-row 
                        label="Dibuat oleh"
                        :value="$restock->createdBy?->name ?? '-'"
                    />
                    @if($restock->notes)
                        <div class="pt-3 border-t border-slate-100">
                            <div class="text-xs text-slate-500 mb-1">Catatan</div>
                            <p class="leading-relaxed text-sm text-slate-900">
                                {!! nl2br(e($restock->notes)) !!}
                            </p>
                        </div>
                    @endif
                </div>
            </x-mobile.card>

            {{-- MOBILE LIST --}}
            <x-mobile.card>
                <h2 class="text-sm font-semibold text-slate-900 mb-4">
                    Daftar Item
                </h2>
                <div class="space-y-4 divide-y divide-slate-100">
                    @forelse($restock->items as $item)
                        <div class="{{ $loop->first ? '' : 'pt-4' }}">
                            {{-- Row 1: Product Name --}}
                            <div class="font-medium text-slate-900 text-sm mb-1">
                                {{ $item->product->name ?? 'Unknown product' }}
                            </div>
                            
                            <div class="flex justify-between items-start">
                                {{-- Row 2: SKU & Unit Cost --}}
                                <div class="text-xs text-slate-500 space-y-0.5">
                                    <div>SKU: {{ $item->product->sku ?? 'N/A' }}</div>
                                    <div>@ {{ number_format((float) $item->unit_cost, 2, ',', '.') }}</div>
                                </div>

                                {{-- Row 3: Total & Qty --}}
                                <div class="text-right">
                                    <div class="font-bold text-slate-900 text-sm">
                                        Rp {{ number_format((float) $item->line_total, 2, ',', '.') }}
                                    </div>
                                    <div class="text-xs text-slate-500 mt-0.5">
                                        x{{ number_format((int) $item->quantity, 0, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-slate-500 py-4 text-sm">
                            Tidak ada item pada restock ini.
                        </div>
                    @endforelse
                </div>
            </x-mobile.card>

            @if($restock->hasRating())
            <x-mobile.card>
                <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
                    <p class="text-sm font-semibold text-slate-900">Rating Supplier</p>
                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700">
                        Rated
                    </span>
                </div>

                <div class="space-y-3">
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="flex items-center gap-2">
                            <span class="text-lg font-semibold text-slate-900">
                                {{ $restock->rating }}/{{ \App\Models\RestockOrder::MAX_RATING }}
                            </span>
                            <div class="flex items-center gap-1">
                                @for($i = \App\Models\RestockOrder::MIN_RATING; $i <= \App\Models\RestockOrder::MAX_RATING; $i++)
                                    <x-lucide-star class="h-4 w-4 {{ $i <= (int) $restock->rating ? 'text-yellow-400' : 'text-slate-300' }}" />
                                @endfor
                            </div>
                        </div>
                        <div class="text-xs text-slate-500">
                            Dinilai oleh {{ $restock->ratingGivenBy->name ?? 'Unknown user' }}
                            @if($restock->rating_given_at)
                                pada {{ $restock->rating_given_at->format('d M Y H:i') }}
                            @endif
                        </div>
                    </div>

                    @if($restock->rating_notes)
                        <div class="rounded-lg bg-slate-50 px-3 py-2 text-xs text-slate-800 whitespace-pre-line">
                            {{ $restock->rating_notes }}
                        </div>
                    @endif
                </div>
            </x-mobile.card>
            @endif
        </div>

        {{-- DESKTOP CONTENT --}}
        <div class="hidden md:block space-y-6 text-sm text-slate-700">
            {{-- TOOLBAR --}}
            <div class="flex flex-wrap items-center justify-between gap-4">
                <x-breadcrumbs :items="[
                    'Pesanan Masuk' => route('supplier.restocks.index'),
                    '#'.$restock->po_number => route('supplier.restocks.show', $restock),
                ]" />

                <div class="flex flex-wrap items-center gap-2 justify-end">
                    <x-action-button href="{{ route('supplier.restocks.index') }}" variant="secondary" icon="arrow-left">
                        Kembali
                    </x-action-button>

                    @can('rejectSupplierRestock', $restock)
                        <x-action-button 
                            type="button" 
                            variant="outline-danger" 
                            icon="x-circle"
                            x-data
                            @click="$dispatch('open-confirm-modal', {
                                action: '{{ route('supplier.restocks.reject', $restock) }}',
                                method: 'PATCH',
                                title: 'Tolak Pesanan?',
                                message: 'Apakah Anda yakin ingin menolak pesanan ini? Silakan berikan alasan penolakan.',
                                btnText: 'Tolak Pesanan',
                                btnClass: 'bg-red-600 hover:bg-red-500 text-white',
                                type: 'danger',
                                inputName: 'reject_reason',
                                inputLabel: 'Alasan Penolakan (Opsional)',
                                inputPlaceholder: 'Contoh: Stok barang kosong, harga berubah, dll.',
                                inputType: 'textarea'
                            })"
                        >
                            Tolak Pesanan
                        </x-action-button>
                    @endcan

                    @can('confirmSupplierRestock', $restock)
                        <x-action-button 
                            type="button" 
                            variant="primary" 
                            icon="check-circle"
                            x-data
                            @click="$dispatch('open-confirm-modal', {
                                action: '{{ route('supplier.restocks.confirm', $restock) }}',
                                method: 'PATCH',
                                title: 'Konfirmasi Pesanan?',
                                message: 'Apakah Anda yakin ingin menerima pesanan ini? Status akan berubah menjadi Approved.',
                                btnText: 'Ya, Terima Pesanan',
                                type: 'success'
                            })"
                        >
                            Terima Pesanan
                        </x-action-button>
                    @endcan
                </div>
            </div>

            @if($errors->any())
                <x-card class="p-4 border border-rose-200 bg-rose-50 text-rose-800">
                    <p class="font-semibold text-slate-900">Terjadi kesalahan:</p>
                    <ul class="mt-2 list-disc list-inside space-y-1">
                        @foreach($errors->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </x-card>
            @endif

            {{-- SECTION: Timeline --}}
            <x-card class="p-6 space-y-4">
                <p class="text-base font-semibold text-slate-900">Status Restock</p>
                @include('components.restocks-status-timeline', ['status' => $restock->status])
            </x-card>

            @php
                $totalItems = (int) ($restock->total_items ?? $restock->items->count());
                $totalQty = (int) ($restock->total_quantity ?? $restock->items->sum('quantity'));
                $totalValue = (float) ($restock->total_amount ?? $restock->items->sum('line_total'));
            @endphp

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                {{-- Informasi utama --}}
                <x-card class="p-6 space-y-6 lg:col-span-2">
                    {{-- Informasi Supplier --}}
                    <div class="space-y-3">
                        <p class="text-base font-semibold text-slate-900">Informasi Supplier</p>
                        <div class="space-y-2">
                            <x-description-item label="Nama" :value="$restock->supplier->name ?? 'Unknown supplier'" icon="building-2" />
                            @if(optional($restock->supplier)->contact_person)
                                <x-description-item label="Kontak" :value="$restock->supplier->contact_person" icon="user-round" />
                            @endif
                            @if(optional($restock->supplier)->email)
                                <x-description-item label="Email" :value="$restock->supplier->email" icon="mail" />
                            @endif
                            @if(optional($restock->supplier)->phone)
                                <x-description-item label="Telepon" :value="$restock->supplier->phone" icon="phone" />
                            @endif
                        </div>
                    </div>

                    {{-- Informasi Pesanan --}}
                    <div class="space-y-3">
                        <p class="text-base font-semibold text-slate-900">Informasi Pesanan</p>
                        <div class="space-y-2">
                            <x-description-item label="Nomor PO" :value="$restock->po_number" icon="hash" />
                            <x-description-item label="Tanggal Order" :value="optional($restock->order_date)->format('d M Y') ?? '-'" icon="calendar" />
                            <x-description-item
                                :label="$restock->status === 'received' ? 'Tanggal Diterima' : 'Perkiraan Tiba'"
                                :value="$restock->status === 'received' 
                                    ? ($restock->incomingTransaction?->transaction_date?->format('d M Y') ?? $restock->updated_at->format('d M Y'))
                                    : ($restock->expected_delivery_date?->format('d M Y') ?? '-')"
                                icon="clock"
                            />
                            <x-description-item label="Catatan" :value="$restock->notes ?? '-'" icon="notebook-pen" />
                        </div>
                    </div>

                    {{-- Informasi Tambahan --}}
                    <div class="space-y-3">
                        <p class="text-base font-semibold text-slate-900">Informasi Tambahan</p>
                        <div class="space-y-2">
                            <x-description-item label="Dibuat oleh" :value="optional($restock->createdBy)->name ?? '-'" icon="user" />
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
                                    {{ number_format($totalItems, 0, ',', '.') }}
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
                                    {{ number_format($totalQty, 0, ',', '.') }}
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
                                    Rp {{ number_format($totalValue, 2, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </x-card>
                </div>
            </div>

            {{-- TABLE --}}
            <x-card class="p-6 space-y-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <p class="text-base font-semibold text-slate-900">Daftar Item</p>
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
                        @forelse($restock->items as $item)
                            <x-table.tr>
                                <x-table.td>
                                    <p class="font-medium text-slate-900">
                                        {{ $item->product->name ?? 'Unknown product' }}
                                    </p>
                                </x-table.td>
                                <x-table.td class="text-slate-500">
                                    {{ $item->product->sku ?? 'N/A' }}
                                </x-table.td>
                                <x-table.td align="right" class="font-semibold text-slate-900">
                                    {{ number_format((int) $item->quantity, 0, ',', '.') }}
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
                                    Tidak ada item pada restock ini.
                                </x-table.td>
                            </x-table.tr>
                        @endforelse
                    </x-table.tbody>
                </x-table>
            </x-card>

            @if($restock->hasRating())
            <x-card class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-slate-900">Rating Supplier</h3>
                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                        Rated
                    </span>
                </div>

                <div class="flex items-start gap-4 bg-slate-50 p-4 rounded-xl">
                    <div class="flex-shrink-0 text-center px-4 border-r border-slate-200">
                        <span class="block text-3xl font-bold text-slate-900">{{ $restock->rating }}</span>
                        <div class="flex justify-center gap-0.5 text-yellow-400 mt-1">
                            @for($i=1; $i<=5; $i++)
                                <x-lucide-star class="w-4 h-4 {{ $i <= $restock->rating ? 'fill-current' : 'text-slate-300' }}" />
                            @endfor
                        </div>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">
                            Ulasan dari {{ $restock->ratingGivenBy->name ?? 'User' }}
                        </p>
                        <p class="text-slate-800 text-sm italic">
                            "{{ $restock->rating_notes ?? 'Tidak ada catatan ulasan.' }}"
                        </p>
                        <p class="text-xs text-slate-400 mt-2">
                            {{ $restock->rating_given_at?->format('d M Y, H:i') }}
                        </p>
                    </div>
                </div>
            </x-card>
            @endif
        </div>
    </div>

    {{-- MODAL --}}
    <x-confirm-modal />

    {{-- MODAL --}}

@endsection
