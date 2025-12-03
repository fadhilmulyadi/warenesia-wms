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
            back="{{ route('restocks.index') }}"
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
                        <div class="text-xs text-slate-400">PO Number</div>
                        <div class="text-sm font-semibold text-slate-900">
                            #{{ $restock->po_number }}
                        </div>
                        <div class="mt-1 text-[11px] text-slate-500">
                            Supplier: {{ $restock->supplier?->name ?? '-' }}
                        </div>
                    </div>
                    <x-badge :variant="$statusVariant">
                        {{ $statusLabel }}
                    </x-badge>
                </div>

                <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
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

            {{-- ACTION CARD (CANCEL / MARK IN TRANSIT / MARK RECEIVED) --}}
            @canany(['cancel', 'markInTransit', 'markReceived'], $restock)
            <x-mobile.card>
                <div class="space-y-2 text-xs">
                    @can('cancel', $restock)
                        <form method="POST" action="{{ route('restocks.cancel', $restock) }}">
                            @csrf
                            @method('PATCH')
                            <button 
                                type="submit"
                                class="w-full h-9 rounded-lg bg-rose-100 text-rose-700 font-semibold flex items-center justify-center gap-2 hover:bg-rose-200"
                            >
                                <x-lucide-x-circle class="w-4 h-4" />
                                Batalkan Restock
                            </button>
                        </form>
                    @endcan

                    @can('markInTransit', $restock)
                        <form method="POST" action="{{ route('restocks.mark-in-transit', $restock) }}">
                            @csrf
                            @method('PATCH')
                            <button 
                                type="submit"
                                class="w-full h-9 rounded-lg bg-slate-900 text-white font-semibold flex items-center justify-center gap-2 hover:bg-black"
                            >
                                <x-lucide-truck class="w-4 h-4" />
                                Tandai Dikirim
                            </button>
                        </form>
                    @endcan

                    @can('markReceived', $restock)
                        <form method="POST" action="{{ route('restocks.mark-received', $restock) }}">
                            @csrf
                            @method('PATCH')
                            <button 
                                type="submit"
                                class="w-full h-9 rounded-lg bg-teal-600 text-white font-semibold flex items-center justify-center gap-2 hover:bg-teal-700"
                            >
                                <x-lucide-check-circle class="w-4 h-4" />
                                Tandai Diterima
                            </button>
                        </form>
                    @endcan
                </div>
            </x-mobile.card>
            @endcanany

            {{-- TIMELINE STATUS --}}
            <x-mobile.card>
                <h2 class="text-sm font-semibold text-slate-900 mb-2">
                    STATUS RESTOK
                </h2>
                @include('components.restocks-status-timeline', ['status' => $restock->status])
            </x-mobile.card>

            {{-- INFO PESANAN (SUPPLIER, DIBUAT OLEH, CATATAN) --}}
            <x-mobile.card>
                <h2 class="text-sm font-semibold text-slate-900 mb-2">
                    Informasi Pesanan
                </h2>
                <div class="space-y-2 text-xs">
                    <x-mobile.stat-row 
                        label="Supplier"
                        :value="$restock->supplier?->name ?? '-'"
                    />
                    <x-mobile.stat-row 
                        label="Dibuat oleh"
                        :value="$restock->createdBy?->name ?? '-'"
                    />
                    @if($restock->notes)
                        <div class="pt-2 border-t border-slate-100 text-[11px] text-slate-500">
                            <div class="font-semibold text-slate-700 mb-1">Catatan</div>
                            <p class="leading-relaxed">
                                {!! nl2br(e($restock->notes)) !!}
                            </p>
                        </div>
                    @endif
                </div>
            </x-mobile.card>

            {{-- TABEL ITEM --}}
            <x-mobile.card>
                <h2 class="text-sm font-semibold text-slate-900 mb-2">
                    Daftar Item
                </h2>
                <div class="overflow-x-auto -mx-4 px-4">
                    <x-table>
                        <x-table.thead>
                            <x-table.th>Product</x-table.th>
                            <x-table.th>SKU</x-table.th>
                            <x-table.th align="right">Quantity</x-table.th>
                            <x-table.th align="right">Unit Cost</x-table.th>
                            <x-table.th align="right">Line Total</x-table.th>
                        </x-table.thead>
        
                        <x-table.tbody>
                            @forelse($restock->items as $item)
                                <x-table.tr>
                                    <x-table.td>
                                        <div class="flex flex-col">
                                            <span class="font-medium">
                                                {{ $item->product->name ?? 'Unknown product' }}
                                            </span>
                                        </div>
                                    </x-table.td>
                                    <x-table.td>
                                        {{ $item->product->sku ?? 'N/A' }}
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
                </div>
            </x-mobile.card>

            @if($restock->hasRating() || ($restock->isReceived() && auth()->user()->can('rate', $restock)))
            <x-mobile.card>
                <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
                    <p class="text-sm font-semibold text-slate-900">Rating Supplier</p>
                    @if($restock->hasRating())
                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">
                            Rated
                        </span>
                    @endif
                </div>

                @if(! $restock->hasRating())
                    @can('rate', $restock)
                        <form method="POST" action="{{ route('restocks.rate', $restock) }}" class="space-y-4">
                            @csrf
                            @method('PATCH')

                            <div class="space-y-2">
                                <x-input-label for="rating_mobile" value="Rating ({{ \App\Models\RestockOrder::MIN_RATING }}-{{ \App\Models\RestockOrder::MAX_RATING }})" />
                                <select
                                    id="rating_mobile"
                                    name="rating"
                                    class="w-full rounded-lg border-slate-200 text-sm"
                                >
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
                                <textarea
                                    id="rating_notes_mobile"
                                    name="rating_notes"
                                    rows="3"
                                    class="w-full rounded-lg border-slate-200 text-sm"
                                    placeholder="Bagikan catatan tentang kecepatan, akurasi, atau kualitas pengiriman."
                                >{{ old('rating_notes', $restock->rating_notes) }}</textarea>
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
                                        <x-lucide-star class="h-4 w-4 {{ $i <= (int) $restock->rating ? 'text-yellow-400' : 'text-slate-300' }}" />
                                    @endfor
                                </div>
                            </div>
                            <div class="text-[10px] text-slate-500">
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
                    'Transaksi' => route('transactions.index', ['tab' => 'restocks']),
                    'Detail Restock' => route('restocks.show', $restock),
                ]" />

                <div class="flex flex-wrap items-center gap-2 justify-end">
                    <x-action-button href="{{ route('restocks.index') }}" variant="secondary" icon="arrow-left">
                        Kembali
                    </x-action-button>

                    @can('cancel', $restock)
                        <form method="POST" action="{{ route('restocks.cancel', $restock) }}">
                            @csrf
                            @method('PATCH')
                            <x-action-button type="submit" variant="outline-danger" icon="x">
                                Batalkan
                            </x-action-button>
                        </form>
                    @endcan

                    @can('markInTransit', $restock)
                        <form method="POST" action="{{ route('restocks.mark-in-transit', $restock) }}">
                            @csrf
                            @method('PATCH')
                            <x-action-button type="submit" variant="primary" icon="truck">
                                Tandai dikirim
                            </x-action-button>
                        </form>
                    @endcan

                    @can('markReceived', $restock)
                        <form method="POST" action="{{ route('restocks.mark-received', $restock) }}">
                            @csrf
                            @method('PATCH')
                            <x-action-button type="submit" variant="primary" icon="check-circle">
                                Tandai diterima
                            </x-action-button>
                        </form>
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

            @if(session('success'))
                <x-card class="p-4 border border-emerald-200 bg-emerald-50 text-emerald-800">
                    {{ session('success') }}
                </x-card>
            @endif

            <x-card class="p-6 space-y-4">
                <p class="text-base font-semibold text-slate-900">STATUS RESTOK</p>
                <div class="border-t border-slate-200"></div>

                @include('components.restocks-status-timeline', ['status' => $restock->status])
            </x-card>

            <x-card class="p-6 space-y-4">
                <p class="text-base font-semibold text-slate-900">INFORMASI PESANAN</p>
                
                <div class="border-t border-slate-200"></div>

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
                    <p class="text-base font-semibold text-slate-900">DAFTAR ITEM</p>
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

            <x-card class="p-6 space-y-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <p class="text-base font-semibold text-slate-900">Rating Supplier</p>
                    @if($restock->hasRating())
                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700">
                            Rated
                        </span>
                    @endif
                </div>

                @if(! $restock->isReceived())
                    <p class="text-sm text-slate-500">
                        Rating dapat diberikan setelah restock ini berstatus received.
                    </p>
                @elseif(! $restock->hasRating())
                    @can('rate', $restock)
                        <form method="POST" action="{{ route('restocks.rate', $restock) }}" class="space-y-4">
                            @csrf
                            @method('PATCH')

                            <div class="space-y-2">
                                <x-input-label for="rating" value="Rating ({{ \App\Models\RestockOrder::MIN_RATING }}-{{ \App\Models\RestockOrder::MAX_RATING }})" />
                                <select
                                    id="rating"
                                    name="rating"
                                    class="w-full rounded-lg border-slate-200 text-sm"
                                >
                                    @for($i = \App\Models\RestockOrder::MIN_RATING; $i <= \App\Models\RestockOrder::MAX_RATING; $i++)
                                        <option value="{{ $i }}" @selected((int) old('rating', $restock->rating) === $i)>
                                            {{ $i }}
                                        </option>
                                    @endfor
                                </select>
                                <x-input-error class="mt-1" :messages="$errors->get('rating')" />
                            </div>

                            <div class="space-y-2">
                                <x-input-label for="rating_notes" value="Feedback (opsional)" />
                                <textarea
                                    id="rating_notes"
                                    name="rating_notes"
                                    rows="3"
                                    class="w-full rounded-lg border-slate-200 text-sm"
                                    placeholder="Bagikan catatan tentang kecepatan, akurasi, atau kualitas pengiriman."
                                >{{ old('rating_notes', $restock->rating_notes) }}</textarea>
                                <x-input-error class="mt-1" :messages="$errors->get('rating_notes')" />
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                <x-action-button type="submit" variant="primary" icon="star">
                                    Simpan Rating
                                </x-action-button>
                                <x-action-button href="{{ route('restocks.show', $restock) }}" variant="secondary">
                                    Batal
                                </x-action-button>
                            </div>
                        </form>
                    @else
                        <p class="text-sm text-slate-500">
                            Anda tidak memiliki izin untuk memberikan rating.
                        </p>
                    @endcan
                @else
                    <div class="space-y-3">
                        <div class="flex flex-wrap items-center gap-3">
                            <div class="flex items-center gap-2">
                                <span class="text-2xl font-semibold text-slate-900">
                                    {{ $restock->rating }}/{{ \App\Models\RestockOrder::MAX_RATING }}
                                </span>
                                <div class="flex items-center gap-1">
                                    @for($i = \App\Models\RestockOrder::MIN_RATING; $i <= \App\Models\RestockOrder::MAX_RATING; $i++)
                                        <x-lucide-star class="h-5 w-5 {{ $i <= (int) $restock->rating ? 'text-yellow-400' : 'text-slate-300' }}" />
                                    @endfor
                                </div>
                            </div>
                            <div class="text-sm text-slate-500">
                                Dinilai oleh {{ $restock->ratingGivenBy->name ?? 'Unknown user' }}
                                @if($restock->rating_given_at)
                                    pada {{ $restock->rating_given_at->format('d M Y H:i') }}
                                @endif
                            </div>
                        </div>

                        @if($restock->rating_notes)
                            <div class="rounded-lg bg-slate-50 px-4 py-3 text-sm text-slate-800 whitespace-pre-line">
                                {{ $restock->rating_notes }}
                            </div>
                        @endif
                    </div>
                @endif
            </x-card>
        </div>
    </div>
@endsection
