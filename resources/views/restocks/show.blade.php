@extends('layouts.app')

@section('title', 'Restock #' . $restock->po_number)

@section('page-header')
    <x-page-header
        :title="'Restock #' . $restock->po_number"
        :description="'Supplier: ' . ($restock->supplier->name ?? 'Unknown')"
    />
@endsection

@section('content')
    <div class="max-w-6xl mx-auto space-y-6 text-sm text-slate-700">
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
                        <x-action-button type="button" variant="outline-danger" icon="x">
                            Batalkan
                        </x-action-button>
                    </form>
                @endcan

                @can('markInTransit', $restock)
                    <form method="POST" action="{{ route('restocks.mark-in-transit', $restock) }}">
                        @csrf
                        @method('PATCH')
                        <x-action-button type="button" variant="primary" icon="truck">
                            Tandai dikirim
                        </x-action-button>
                    </form>
                @endcan

                @can('markReceived', $restock)
                    <form method="POST" action="{{ route('restocks.mark-received', $restock) }}">
                        @csrf
                        @method('PATCH')
                        <x-action-button type="button" variant="primary" icon="check-circle">
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
            <div class="flex flex-wrap items-start justify-between gap-3">
                <p class="text-base font-semibold text-slate-900">Status Restock</p>
                @include('components.status-badge', [
                    'status' => $restock->status,
                    'label' => $restock->status_label,
                ])
            </div>

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
@endsection
