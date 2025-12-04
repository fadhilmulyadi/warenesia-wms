@extends('layouts.app')

@section('title', 'Restock #' . $restock->po_number)

@section('page-header')
    <div class="hidden md:block">
        <x-page-header
            :title="'Restock #' . $restock->po_number"
            :description="'Supplier: ' . ($restock->supplier->name ?? 'Unknown')"
        />
    </div>

    <div class="md:hidden">
        <x-mobile-header
            title="Restock #{{ $restock->po_number }}"
            back="{{ route('supplier.restocks.index') }}"
        />
    </div>
@endsection

@section('content')
    <div class="max-w-6xl mx-auto">
        {{-- MOBILE --}}
        <div class="md:hidden space-y-3 pb-24">
            @php
                $statusLabel = $restock->status_label ?? ucfirst($restock->status);
                $statusVariant = match($restock->status) {
                    'pending' => 'warning',
                    'approved' => 'info',
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

            {{-- SUMMARY --}}
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
                        label="Perkiraan Tiba"
                        :value="$restock->expected_delivery_date?->format('d M Y') ?? '-'"
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

            {{-- ACTION CARD --}}
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
                            @click="$dispatch('open-reject-modal')"
                            class="w-full h-11 rounded-lg bg-rose-100 text-rose-700 font-semibold flex items-center justify-center gap-2 hover:bg-rose-200 text-sm"
                        >
                            <x-lucide-x-circle class="w-5 h-5" />
                            Tolak Pesanan
                        </button>
                    @endcan
                </div>
            </x-mobile.card>
            @endcanany

            {{-- TIMELINE STATUS --}}
            <x-mobile.card>
                <h2 class="text-sm font-semibold text-slate-900 mb-3">
                    Status Restock
                </h2>
                @include('components.restocks-status-timeline', ['status' => $restock->status])
            </x-mobile.card>

            {{-- INFO PESANAN --}}
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

            {{-- DAFTAR ITEM (STACKED LIST) --}}
            <x-mobile.card>
                <h2 class="text-sm font-semibold text-slate-900 mb-4">
                    Daftar Item
                </h2>
                <div class="space-y-4 divide-y divide-slate-100">
                    @forelse($restock->items as $item)
                        <div class="{{ $loop->first ? '' : 'pt-4' }}">
                            {{-- Baris 1: Nama Produk --}}
                            <div class="font-medium text-slate-900 text-sm mb-1">
                                {{ $item->product->name ?? 'Unknown product' }}
                            </div>
                            
                            <div class="flex justify-between items-start">
                                {{-- Baris 2: SKU & Harga Satuan --}}
                                <div class="text-xs text-slate-500 space-y-0.5">
                                    <div>SKU: {{ $item->product->sku ?? 'N/A' }}</div>
                                    <div>@ {{ number_format((float) $item->unit_cost, 2, ',', '.') }}</div>
                                </div>

                                {{-- Baris 3: Total Harga & Qty --}}
                                <div class="text-right">
                                    <div class="font-bold text-slate-900 text-sm">
                                        Rp {{ number_format((float) $item->line_total, 2, ',', '.') }}
                                    </div>
                                    <div class="text-xs text-slate-500 mt-0.5">
                                        x{{ number_format((int) $item->quantity) }}
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

        {{-- DESKTOP --}}
        <div class="hidden md:block space-y-6 text-sm text-slate-700">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <x-breadcrumbs :items="[
                    'Restocks' => route('supplier.restocks.index'),
                    'Detail Restock' => route('supplier.restocks.show', $restock),
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
                            @click="$dispatch('open-reject-modal')"
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

            <x-card class="p-6 space-y-4">
                <p class="text-base font-semibold text-slate-900">Status Restock</p>
                @include('components.restocks-status-timeline', ['status' => $restock->status])
            </x-card>

            <x-card class="p-6 space-y-4">
                <p class="text-base font-semibold text-slate-900">Informasi Pesanan</p>
                
                <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="space-y-1">
                        <p class="text-sm text-slate-500">Nomor PO</p>
                        <p class="text-sm font-semibold text-slate-900">{{ $restock->po_number }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-sm text-slate-500">Tanggal Order</p>
                        <p class="text-sm text-slate-900">
                            {{ optional($restock->order_date)->format('d M Y') ?? '-' }}
                        </p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-sm text-slate-500">Perkiraan Tiba</p>
                        <p class="text-sm text-slate-900">
                            {{ optional($restock->expected_delivery_date)->format('d M Y') ?? '-' }}
                        </p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-sm text-slate-500">Supplier</p>
                        <p class="text-sm text-slate-900">
                            {{ $restock->supplier->name ?? 'Unknown supplier' }}
                        </p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-sm text-slate-500">Total Item</p>
                        <p class="text-sm font-semibold text-slate-900">
                            {{ number_format((int) $restock->total_items) }}
                        </p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-sm text-slate-500">Total Kuantitas</p>
                        <p class="text-sm font-semibold text-slate-900">
                            {{ number_format((int) $restock->total_quantity) }}
                        </p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-sm text-slate-500">Total Nilai</p>
                        <p class="text-sm font-semibold text-slate-900">
                            Rp {{ number_format((float) $restock->total_amount, 2, ',', '.') }}
                        </p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-sm text-slate-500">Dibuat oleh</p>
                        <p class="text-sm text-slate-900">
                            {{ optional($restock->createdBy)->name ?? '-' }}
                        </p>
                    </div>
                </div>

                @if($restock->notes)
                    <div class="pt-4 border-t border-slate-100 space-y-1">
                        <p class="text-sm text-slate-500">Catatan</p>
                        <p class="text-sm text-slate-900 whitespace-pre-line">
                            {{ $restock->notes }}
                        </p>
                    </div>
                @endif
            </x-card>

            <x-card class="p-6 space-y-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <p class="text-base font-semibold text-slate-900">Daftar Item</p>
                </div>

                <x-table>
                    <x-table.thead>
                        <x-table.th>Product</x-table.th>
                        <x-table.th align="right">Quantity</x-table.th>
                        <x-table.th align="right">Unit Cost</x-table.th>
                        <x-table.th align="right">Line Total</x-table.th>
                    </x-table.thead>

                    <x-table.tbody>
                        @forelse($restock->items as $item)
                            <x-table.tr>
                                <x-table.td>
                                    <div class="flex flex-col">
                                        <span class="font-medium text-slate-900">
                                            {{ $item->product->name ?? 'Unknown product' }}
                                        </span>
                                        <span class="text-xs text-slate-500">
                                            SKU: {{ $item->product->sku ?? 'N/A' }}
                                        </span>
                                    </div>
                                </x-table.td>
                                <x-table.td align="right" class="font-semibold text-slate-900">
                                    {{ number_format((int) $item->quantity) }}
                                </x-table.td>
                                <x-table.td align="right">
                                    {{ number_format((float) $item->unit_cost, 2, ',', '.') }}
                                </x-table.td>
                                <x-table.td align="right" class="font-semibold text-slate-900">
                                    {{ number_format((float) $item->line_total, 2, ',', '.') }}
                                </x-table.td>
                            </x-table.tr>
                        @empty
                            <x-table.tr>
                                <x-table.td colspan="4" class="text-center text-slate-500">
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

    {{-- CONFIRM MODAL --}}
    <x-confirm-modal />

    {{-- REJECT MODAL --}}
    <div x-data="{
        show: false,
        open() {
            this.show = true;
            document.body.classList.add('overflow-y-hidden');
        },
        close() {
            this.show = false;
            document.body.classList.remove('overflow-y-hidden');
        }
    }" 
    x-on:open-reject-modal.window="open()" 
    x-on:keydown.escape.window="close()"
    x-show="show" 
    class="relative z-50" 
    style="display: none;">
        
        {{-- Backdrop --}}
        <div x-show="show" x-transition.opacity class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm" @click="close()"></div>

        {{-- Modal Center Wrapper --}}
        <div class="fixed inset-0 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center">
                
                {{-- Modal Panel --}}
                <div x-show="show" x-transition
                    class="relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all">
                    
                    <div class="px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-rose-100 sm:mx-0 sm:h-10 sm:w-10">
                                <x-lucide-alert-triangle class="h-6 w-6 text-rose-600" />
                            </div>
                            <div class="mt-3 sm:ml-4 sm:mt-0 w-full">
                                <h3 class="text-base font-semibold leading-6 text-slate-900">Tolak Pesanan?</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-slate-600">
                                        Apakah Anda yakin ingin menolak pesanan ini? Silakan berikan alasan penolakan.
                                    </p>
                                    
                                    <form id="reject-form" method="POST" action="{{ route('supplier.restocks.reject', $restock) }}" class="mt-4">
                                        @csrf
                                        @method('PATCH')
                                        
                                        <div class="space-y-2">
                                            <label for="reject_reason" class="block text-sm font-medium text-slate-700">Alasan Penolakan (Opsional)</label>
                                            <textarea 
                                                id="reject_reason" 
                                                name="reject_reason" 
                                                rows="3" 
                                                class="w-full rounded-lg border-slate-300 shadow-sm focus:border-rose-500 focus:ring-rose-500 sm:text-sm"
                                                placeholder="Contoh: Stok barang kosong, harga berubah, dll."
                                            ></textarea>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="submit" form="reject-form"
                            class="inline-flex w-full justify-center rounded-lg bg-rose-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-rose-500 sm:ml-3 sm:w-auto">
                            Tolak Pesanan
                        </button>
                        <button type="button"
                            class="mt-3 inline-flex w-full justify-center rounded-lg bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto"
                            @click="close()">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
