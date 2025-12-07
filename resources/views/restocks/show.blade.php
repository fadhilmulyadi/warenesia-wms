@extends('layouts.app')

@section('title', 'Restock #' . $restock->po_number)

@section('page-header')
    <div class="hidden md:block">
        <x-page-header :title="'Restock #' . $restock->po_number" :description="'Detail Purchase Order untuk ' . ($restock->supplier->name ?? 'Supplier tidak dikenal')" />
    </div>

    <div class="md:hidden">
        <x-mobile-header title="Restock #{{ $restock->po_number }}" back="{{ route('restocks.index') }}" />
    </div>
@endsection

@section('content')
    <div class="max-w-6xl mx-auto">
        {{-- MOBILE --}}
        <div class="md:hidden space-y-3 pb-24">
            @php
                $statusLabel = $restock->status_label ?? ucfirst($restock->status);
                $statusVariant = match ($restock->status) {
                    'pending' => 'warning',
                    'approved' => 'info',
                    'in_transit' => 'primary',
                    'received' => 'success',
                    'cancelled' => 'danger',
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
                    <x-mobile.stat-row label="Tgl Order" :value="$restock->order_date?->format('d M Y') ?? '-'" />
                    <x-mobile.stat-row 
                        :label="$restock->status === 'received' ? 'Tanggal Diterima' : 'Perkiraan Tiba'"
                        :value="$restock->status === 'received' ? ($restock->incomingTransaction?->transaction_date?->format('d M Y') ?? $restock->updated_at->format('d M Y')) : ($restock->expected_delivery_date?->format('d M Y') ?? '-')"
                    /><x-mobile.stat-row label="Total Item" :value="number_format($totalItems, 0, ',', '.')" />
                    <x-mobile.stat-row label="Total Qty" :value="number_format($totalQty, 0, ',', '.')" />
                    <x-mobile.stat-row label="Total Nilai" prefix="Rp" :value="number_format($totalValue, 0, ',', '.')" />
                </div>
            </x-mobile.card>

            {{-- ACTION CARD --}}
            @canany(['cancel', 'markInTransit', 'markReceived'], $restock)
                <x-mobile.card>
                    <div class="space-y-3">
                        @can('cancel', $restock)
                            <button type="button" x-data @click="$dispatch('open-confirm-modal', {
                                            action: '{{ route('restocks.cancel', $restock) }}',
                                            method: 'PATCH',
                                            title: 'Batalkan Restock?',
                                            message: 'Apakah Anda yakin ingin membatalkan restock ini? Stok yang sudah diterima (jika ada) akan dikembalikan.',
                                            btnText: 'Ya, Batalkan',
                                            type: 'danger'
                                        })"
                                class="w-full h-11 rounded-lg bg-rose-100 text-rose-700 font-semibold flex items-center justify-center gap-2 hover:bg-rose-200 text-sm">
                                <x-lucide-x-circle class="w-5 h-5" />
                                Batalkan Restock
                            </button>
                        @endcan

                        @can('markInTransit', $restock)
                            <button type="button" x-data @click="$dispatch('open-confirm-modal', {
                                            action: '{{ route('restocks.mark-in-transit', $restock) }}',
                                            method: 'PATCH',
                                            title: 'Tandai Dikirim?',
                                            message: 'Apakah Anda yakin ingin menandai restock ini sebagai dikirim (In Transit)?',
                                            btnText: 'Ya, Tandai Dikirim',
                                            type: 'info'
                                        })"
                                class="w-full h-11 rounded-lg bg-slate-900 text-white font-semibold flex items-center justify-center gap-2 hover:bg-black text-sm">
                                <x-lucide-truck class="w-5 h-5" />
                                Tandai Dikirim
                            </button>
                        @endcan

                        @can('markReceived', $restock)
                            <button type="button" x-data @click="$dispatch('open-confirm-modal', {
                                            action: '{{ route('restocks.mark-received', $restock) }}',
                                            method: 'PATCH',
                                            title: 'Tandai Diterima?',
                                            message: 'Pastikan Anda telah memeriksa fisik barang. Stok produk akan bertambah sesuai jumlah yang diterima.',
                                            btnText: 'Ya, Terima Barang',
                                            type: 'success'
                                        })"
                                class="w-full h-11 rounded-lg bg-teal-600 text-white font-semibold flex items-center justify-center gap-2 hover:bg-teal-700 text-sm">
                                <x-lucide-check-circle class="w-5 h-5" />
                                Tandai Diterima
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
                @include('components.mobile-restocks-status-timeline', ['status' => $restock->status])
            </x-mobile.card>

            {{-- INFO PESANAN --}}
            <x-mobile.card>
                <h2 class="text-sm font-semibold text-slate-900 mb-3">
                    Informasi Pesanan
                </h2>
                <div class="space-y-4 text-xs">
                    <x-mobile.stat-row label="Supplier" :value="$restock->supplier?->name ?? '-'" />
                    <x-mobile.stat-row label="Dibuat oleh" :value="$restock->createdBy?->name ?? '-'"/>
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

            @if($restock->hasRating() || ($restock->isReceived() && auth()->user()->can('rate', $restock)))
                <x-mobile.card>
                    <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
                        <p class="text-sm font-semibold text-slate-900">Rating Supplier</p>
                    </div>

                    @if(!$restock->hasRating())
                        @can('rate', $restock)
                            <form method="POST" action="{{ route('restocks.rate', $restock) }}" class="space-y-4">
                                @csrf
                                @method('PATCH')

                                <div class="space-y-2">
                                    <x-input-label for="rating_mobile"
                                        value="Rating ({{ \App\Models\RestockOrder::MIN_RATING }}-{{ \App\Models\RestockOrder::MAX_RATING }})" />
                                    <select id="rating_mobile" name="rating" class="w-full rounded-lg border-slate-200 text-sm">
                                        @for($i = \App\Models\RestockOrder::MIN_RATING; $i <= \App\Models\RestockOrder::MAX_RATING; $i++)
                                            <option value="{{ $i }}" @selected((int) old('rating', $restock->rating) === $i)>
                                                {{ $i }}
                                            </option>
                                        @endfor
                                    </select>
                                    <x-input-error class="mt-1" :messages="$errors->get('rating')" />
                                </div>

                                <div class="space-y-2">
                                    <x-input-label for="rating_notes_mobile" value="Feedback (opsional)" />
                                    <textarea id="rating_notes_mobile" name="rating_notes" rows="3"
                                        class="w-full rounded-lg border-slate-200 text-sm"
                                        placeholder="Bagikan catatan tentang kecepatan, akurasi, atau kualitas pengiriman.">{{ old('rating_notes', $restock->rating_notes) }}</textarea>
                                    <x-input-error class="mt-1" :messages="$errors->get('rating_notes')" />
                                </div>

                                <div class="flex flex-wrap items-center gap-2">
                                    <x-action-button type="submit" variant="primary" icon="star">
                                        Simpan Rating
                                    </x-action-button>
                                </div>
                            </form>
                        @endcan
                    @else
                        <div class="space-y-3">
                            <div class="flex flex-wrap items-center gap-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-lg font-semibold text-slate-900">
                                        {{ $restock->rating }}/{{ \App\Models\RestockOrder::MAX_RATING }}
                                    </span>
                                    <div class="flex items-center gap-1">
                                        @for($i = \App\Models\RestockOrder::MIN_RATING; $i <= \App\Models\RestockOrder::MAX_RATING; $i++)
                                            <x-lucide-star
                                                class="h-4 w-4 {{ $i <= (int) $restock->rating ? 'text-yellow-400' : 'text-slate-300' }}" />
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
                    @endif
                </x-mobile.card>
            @endif
        </div>

        {{-- DESKTOP --}}
        <div class="hidden md:block space-y-6 text-sm text-slate-700">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <x-breadcrumbs :items="[
            'Restock' => route('restocks.index', ['tab' => 'restocks']),
            '#'.$restock->po_number => route('restocks.show', $restock),
        ]" />

                <div class="flex flex-wrap items-center gap-2 justify-end">
                    <x-action-button href="{{ route('restocks.index') }}" variant="secondary" icon="arrow-left">
                        Kembali
                    </x-action-button>

                    @can('cancel', $restock)
                        <x-action-button type="button" variant="outline-danger" icon="x" x-data @click="$dispatch('open-confirm-modal', {
                                        action: '{{ route('restocks.cancel', $restock) }}',
                                        method: 'PATCH',
                                        title: 'Batalkan Restock?',
                                        message: 'Apakah Anda yakin ingin membatalkan restock ini? Stok yang sudah diterima (jika ada) akan dikembalikan.',
                                        btnText: 'Ya, Batalkan',
                                        type: 'danger'
                                    })">
                            Batalkan
                        </x-action-button>
                    @endcan

                    @can('markInTransit', $restock)
                        <x-action-button type="button" variant="primary" icon="truck" x-data @click="$dispatch('open-confirm-modal', {
                                        action: '{{ route('restocks.mark-in-transit', $restock) }}',
                                        method: 'PATCH',
                                        title: 'Tandai Dikirim?',
                                        message: 'Apakah Anda yakin ingin menandai restock ini sebagai dikirim (In Transit)?',
                                        btnText: 'Ya, Tandai Dikirim',
                                        type: 'info'
                                    })">
                            Tandai dikirim
                        </x-action-button>
                    @endcan

                    @can('markReceived', $restock)
                        <x-action-button type="button" variant="primary" icon="check-circle" x-data @click="$dispatch('open-confirm-modal', {
                                        action: '{{ route('restocks.mark-received', $restock) }}',
                                        method: 'PATCH',
                                        title: 'Tandai Diterima?',
                                        message: 'Pastikan Anda telah memeriksa fisik barang. Stok produk akan bertambah sesuai jumlah yang diterima.',
                                        btnText: 'Ya, Terima Barang',
                                        type: 'success'
                                    })">
                            Tandai diterima
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

            <x-card class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-slate-900">Rating Supplier</h3>
                </div>

                @if(!$restock->isReceived())
                    <div
                        class="rounded-lg bg-slate-50 p-4 text-sm text-slate-500 text-center border border-dashed border-slate-200">
                        Rating dapat diberikan setelah pesanan diterima.
                    </div>
                @elseif(!$restock->hasRating())
                    @can('rate', $restock)
                        {{-- FORM RATING --}}
                        <form method="POST" action="{{ route('restocks.rate', $restock) }}">
                            @csrf
                            @method('PATCH')

                            {{-- Layout Grid: Kiri Bintang, Kanan Textarea --}}
                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

                                {{-- KOLOM KIRI: BINTANG (Span 4 kolom) --}}
                                <div class="lg:col-span-4 space-y-3">
                                    <label class="block text-sm font-medium text-slate-700">Berikan Penilaian</label>

                                    {{-- Interactive Star Component using Alpine.js --}}
                                    <div x-data="{ rating: 0, hoverRating: 0 }" class="flex items-center gap-1">
                                        {{-- Hidden Input untuk dikirim ke server --}}
                                        <input type="hidden" name="rating" :value="rating" required>

                                        <template x-for="i in 5">
                                            <button type="button" @click="rating = i" @mouseenter="hoverRating = i"
                                                @mouseleave="hoverRating = 0"
                                                class="focus:outline-none transition-transform active:scale-90">
                                                <svg class="w-8 h-8 transition-colors duration-200"
                                                    :class="(hoverRating >= i || rating >= i) ? 'text-yellow-400 fill-yellow-400' : 'text-slate-300 fill-transparent'"
                                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke="currentColor"
                                                    stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                                </svg>
                                            </button>
                                        </template>
                                    </div>
                                    <p class="text-xs text-slate-500">Klik bintang untuk menilai.</p>
                                    <x-input-error :messages="$errors->get('rating')" />
                                </div>

                                {{-- KOLOM KANAN: TEXTAREA (Span 8 kolom) --}}
                                <div class="lg:col-span-8 space-y-3">
                                    <x-input-label for="rating_notes" value="Ulasan (Opsional)" />
                                    <textarea id="rating_notes" name="rating_notes" rows="3"
                                        class="w-full rounded-lg border-slate-200 text-sm focus:border-teal-500 focus:ring-teal-500"
                                        placeholder="Bagaimana kualitas barang dan kecepatan pengiriman?">{{ old('rating_notes') }}</textarea>

                                    <div class="flex justify-end pt-2">
                                        <x-action-button type="submit" variant="primary" icon="send">
                                            Kirim Ulasan
                                        </x-action-button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    @endcan
                @else
                    {{-- VIEW MODE --}}
                    <div class="flex items-start gap-4 bg-slate-50 p-4 rounded-xl">
                        <div class="flex-shrink-0 text-center px-4 border-r border-slate-200">
                            <span class="block text-3xl font-bold text-slate-900">{{ $restock->rating }}</span>
                            <div class="flex justify-center gap-0.5 text-yellow-400 mt-1">
                                @for($i = 1; $i <= 5; $i++)
                                    <x-lucide-star
                                        class="w-4 h-4 {{ $i <= $restock->rating ? 'fill-current' : 'text-slate-300' }}" />
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
                @endif
            </x-card>
        </div>
    </div>
    <x-confirm-modal />
@endsection
