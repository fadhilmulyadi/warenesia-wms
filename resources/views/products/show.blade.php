@extends('layouts.app')

@section('title', 'Detail Produk: ' . $product->name)

@section('page-header')
    <x-page-header
        title="Detail Produk"
        :description="'SKU: ' . $product->sku"
    />
@endsection

@section('content')
    @php
        $totalTransactions = $total_transactions ?? $totalTransactions ?? 0;
        $totalIncoming = $total_incoming ?? $totalIncoming ?? 0;
        $totalOutgoing = $total_outgoing ?? $totalOutgoing ?? 0;
        $recentTransactions = collect($recent_transactions ?? $recentTransactions ?? []);
        $imageUrl = $product->image_path
            ? \Illuminate\Support\Facades\Storage::url($product->image_path)
            : null;
    @endphp

    <div class="max-w-6xl mx-auto space-y-6 text-sm text-slate-700">

        <div class="flex flex-wrap items-center justify-between gap-4">
            <x-breadcrumbs :items="[
                'Produk' => route('products.index'),
                'Detail Produk' => route('products.show', $product),
            ]" />

            <div class="flex flex-wrap items-center gap-2 justify-end">
                <x-action-button href="{{ route('products.index') }}" variant="secondary" icon="arrow-left">
                    Kembali
                </x-action-button>

                <x-action-button href="{{ route('products.edit', $product) }}" variant="primary" icon="edit">
                    Edit Produk
                </x-action-button>

                <x-action-button href="{{ route('restocks.create', ['product' => $product]) }}" variant="success" icon="plus">
                    Restock
                </x-action-button>
            </div>
        </div>

        <div class="grid gap-6 md:grid-cols-2">

            <x-card class="p-6 space-y-6 h-full">

                <p class="text-base font-semibold text-slate-900 mb-2">
                    IDENTITAS PRODUK
                </p>

                <div class="space-y-6">

                    <div class="flex flex-col gap-4 md:flex-row md:items-start">
                        <div class="aspect-square rounded-xl border border-slate-200 bg-slate-50 overflow-hidden flex items-center justify-center w-full">
                            @if($imageUrl)
                                <img src="{{ $imageUrl }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                            @else
                                <x-lucide-image class="h-10 w-10 text-slate-300" />
                            @endif
                        </div>
    
                        <div class="grid gap-4 sm:grid-cols-2 text-sm">
                            <div class="text-slate-500">Nama Produk</div>
                            <div class="font-medium text-slate-900">{{ $product->name }}</div>
    
                            <div class="text-slate-500">SKU</div>
                            <div class="font-medium text-slate-900">{{ $product->sku }}</div>
    
                            <div class="text-slate-500">Kategori</div>
                            <div class="font-medium text-slate-900">{{ optional($product->category)->name ?? '-' }}</div>
    
                            <div class="text-slate-500">Supplier</div>
                            <div class="font-medium text-slate-900">{{ optional($product->supplier)->name ?? '-' }}</div>

                            <div class="text-slate-500">Unit</div>
                            <div class="font-medium text-slate-900 uppercase">{{ $product->unit->name ?? '-' }}</div>
    
                            <div class="text-slate-500">Lokasi Rak</div>
                            <div class="font-medium text-slate-900">{{ $product->rack_location ?: '-' }}</div>
                        </div>
                    </div>

                </div>
                    {{-- DIVIDER --}}
                    <div class="border-t border-slate-200"></div>

                    {{-- QR CODE SECTION --}}
                    <div class="space-y-3">
                        <div class="flex items-center gap-2 text-slate-600">
                            <x-lucide-qr-code class="h-4 w-4" />
                            <p class="text-sm font-semibold text-slate-900">QR Code</p>
                        </div>

                        <div class="flex items-start gap-4">
                            {{-- QR SVG --}}
                            <div class="rounded-lg border border-slate-200 bg-white p-3 flex items-center justify-center w-28 h-28">
                                {!! QrCode::format('svg')->size(120)->margin(1)->generate($product->getBarcodePayload()) !!}
                            </div>

                            {{-- Buttons --}}
                            <div class="flex flex-col gap-2">
                                <x-action-button href="{{ route('products.barcode', $product) }}" variant="secondary" icon="download">
                                    Download QR
                                </x-action-button>

                                <x-action-button href="{{ route('products.barcode.label', $product) }}" variant="primary" icon="printer">
                                    Print Label
                                </x-action-button>
                            </div>
                        </div>
                    </div>
            </x-card>

            <x-card class="p-6 space-y-6 h-full">

                <p class="text-base font-semibold text-slate-900 mb-1">
                    DATA OPERASIONAL PRODUK
                </p>

                {{-- Harga & Stok --}}
                <div class="space-y-4">

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="text-slate-500">Harga Beli</div>
                        <div class="font-semibold text-slate-900">
                            Rp {{ number_format($product->purchase_price, 2, ',', '.') }}
                        </div>

                        <div class="text-slate-500">Harga Jual</div>
                        <div class="font-semibold text-slate-900">
                            Rp {{ number_format($product->sale_price, 2, ',', '.') }}
                        </div>

                        <div class="text-slate-500">Stok Saat Ini</div>
                        <div class="flex items-center gap-2 font-semibold text-slate-900">
                            <x-lucide-package class="h-4 w-4 text-slate-400" />
                            <span>{{ number_format($product->current_stock, 0, ',', '.') }} {{ $product->unit->name ?? '-' }}</span>
                        </div>

                        <div class="text-slate-500">Minimum Stok</div>
                        <div class="font-medium text-slate-900">
                            {{ number_format($product->min_stock, 0, ',', '.') }} {{ $product->unit->name ?? '-' }}
                        </div>
                    </div>

                    {{-- DIVIDER --}}
                    <div class="border-t border-slate-200"></div>

                    {{-- Deskripsi --}}
                    <div class="space-y-2">
                        <p class="text-sm font-semibold text-slate-900">Deskripsi Produk</p>
                        <p class="text-slate-700 leading-relaxed">
                            {{ $product->description ?: 'Tidak ada deskripsi.' }}
                        </p>
                    </div>
                </div>

            </x-card>
        </div>

        <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            <x-card class="p-4 flex items-center gap-3">
                {{-- Icon --}}
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-50 text-slate-600">
                    <x-lucide-bar-chart-3 class="h-5 w-5" />
                </span>

                {{-- Text --}}
                <div>
                    <p class="text-sm text-slate-500">Total Transaksi</p>
                    <p class="text-2xl font-bold text-slate-900">
                        {{ number_format($totalTransactions, 0, ',', '.') }}
                    </p>
                </div>
            </x-card>

            <x-card class="p-4 flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                    <x-lucide-log-in class="h-5 w-5" />
                </span>

                <div>
                    <p class="text-sm text-slate-500">Total Transaksi Masuk</p>
                    <p class="text-2xl font-bold text-emerald-600">
                        {{ number_format($totalIncoming, 0, ',', '.') }}
                    </p>
                </div>
            </x-card>

            <x-card class="p-4 flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-50 text-red-600">
                    <x-lucide-log-out class="h-5 w-5" />
                </span>

                <div>
                    <p class="text-sm text-slate-500">Total Transaksi Keluar</p>
                    <p class="text-2xl font-bold text-red-600">
                        {{ number_format($totalOutgoing, 0, ',', '.') }}
                    </p>
                </div>
            </x-card>
        </div>

        <x-card class="p-6 space-y-4">
            <div class="flex items-center justify-between">
                <p class="text-base font-semibold text-slate-900">RIWAYAT TRANSAKSI TERBARU</p>
            </div>

            <x-table>
                <x-table.thead>
                    <x-table.th>Tipe</x-table.th>
                    <x-table.th>Tanggal</x-table.th>
                    <x-table.th align="right">Qty (+/-)</x-table.th>
                    <x-table.th>Reference Number</x-table.th>
                    <x-table.th>Dibuat oleh</x-table.th>
                </x-table.thead>

                <x-table.tbody>
                    @forelse($recentTransactions->take(5) as $transaction)
                        @php
                            $type = strtoupper((string) data_get($transaction, 'type', data_get($transaction, 'direction', '')));
                            $isIncoming = $type === 'IN';
                            $dateValue = data_get($transaction, 'date') ?? data_get($transaction, 'transaction_date');
                            $formattedDate = ($dateValue instanceof \Carbon\Carbon)
                                ? $dateValue->format('d M Y')
                                : (is_string($dateValue) ? $dateValue : '-');
                            $quantity = data_get($transaction, 'quantity', data_get($transaction, 'qty', data_get($transaction, 'total_quantity', 0)));
                            $reference = data_get($transaction, 'reference_number') ?? data_get($transaction, 'transaction_number') ?? '-';
                            $creator = data_get($transaction, 'createdBy.name') ?? data_get($transaction, 'created_by.name') ?? data_get($transaction, 'created_by') ?? '-';
                        @endphp

                        <x-table.tr>
                            <x-table.td>
                                <span class="font-semibold {{ $isIncoming ? 'text-emerald-600' : 'text-red-600' }}">
                                    {{ $isIncoming ? 'IN' : 'OUT' }}
                                </span>
                            </x-table.td>
                            <x-table.td class="text-slate-900">{{ $formattedDate }}</x-table.td>
                            <x-table.td align="right" class="font-semibold {{ $isIncoming ? 'text-emerald-600' : 'text-red-600' }}">
                                {{ ($isIncoming ? '+' : '-') . number_format(abs((float) $quantity), 0, ',', '.') }}
                            </x-table.td>
                            <x-table.td class="text-slate-900">{{ $reference }}</x-table.td>
                            <x-table.td class="text-slate-900">{{ $creator }}</x-table.td>
                        </x-table.tr>
                    @empty
                        <x-table.tr>
                            <x-table.td colspan="5" class="text-center text-slate-500">
                                Belum ada transaksi terkait produk ini.
                            </x-table.td>
                        </x-table.tr>
                    @endforelse
                </x-table.tbody>
            </x-table>
        </x-card>

    </div>
@endsection
