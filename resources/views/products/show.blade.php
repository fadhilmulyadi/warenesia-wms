@extends('layouts.app')

@section('title', 'Detail Produk: ' . $product->name)

@section('page-header')
    {{-- DESKTOP HEADER --}}
    <div class="hidden md:block">
        <x-page-header title="Detail Produk" :description="'SKU: ' . $product->sku" />
    </div>

    {{-- MOBILE HEADER --}}
    <div class="md:hidden">
        <x-mobile-header title="Detail Produk" back="{{ route('products.index') }}" />
    </div>
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

    <div class="max-w-6xl mx-auto">

        {{-- MOBILE LAYOUT --}}
        <div class="md:hidden space-y-3 pb-24">
            @php
                $categoryName = $product->category?->name ?? 'Tidak ada kategori';
                $supplierName = $product->supplier?->name ?? 'Tidak ada supplier';
                $unitName = $product->unit?->name ?? '-';

                $stock = $product->current_stock ?? 0;
                $minStock = $product->min_stock ?? 0;

                $stockLabel = 'Stok Aman';
                $stockVariant = 'success';

                if ($stock == 0) {
                    $stockLabel = 'Stok Habis';
                    $stockVariant = 'danger';
                } elseif ($stock <= $minStock) {
                    $stockLabel = 'Stok Rendah';
                    $stockVariant = 'warning';
                }
            @endphp

            {{-- SUMMARY CARD --}}
            <x-mobile.card>
                <div class="flex items-start justify-between gap-2">
                    <div class="space-y-1">
                        <div class="text-xs font-semibold uppercase text-slate-400">
                            SKU: {{ $product->sku }}
                        </div>
                        <h1 class="text-sm font-semibold text-slate-900">
                            {{ $product->name }}
                        </h1>
                        <div class="flex flex-wrap gap-1 pt-1">
                            <x-badge variant="gray">
                                {{ $categoryName }}
                            </x-badge>
                            <x-badge :variant="$stockVariant">
                                {{ $stockLabel }}
                            </x-badge>
                        </div>
                    </div>

                    @if($imageUrl)
                        <img src="{{ $imageUrl }}" alt="{{ $product->name }}"
                            class="w-16 h-16 rounded-lg object-cover border border-slate-200">
                    @endif
                </div>

                <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                    <div class="space-y-0.5">
                        <div class="text-xs text-slate-400">Stok Saat Ini</div>
                        <div class="font-semibold text-slate-900 flex items-baseline gap-1">
                            <span>{{ number_format($stock, 0, ',', '.') }}</span>
                            <span class="text-[11px] text-slate-500">{{ $unitName }}</span>
                        </div>
                    </div>
                    <div class="space-y-0.5">
                        <div class="text-xs text-slate-400">Stok Minimum</div>
                        <div class="font-semibold text-slate-900">
                            {{ number_format($minStock, 0, ',', '.') }}
                        </div>
                    </div>
                </div>

                <div class="mt-3 flex gap-2">
                    <a href="{{ route('products.edit', $product) }}"
                        class="flex-1 h-9 rounded-lg bg-slate-100 text-slate-800 text-xs font-semibold flex items-center justify-center gap-2 hover:bg-slate-200 transition">
                        <x-lucide-pencil class="w-4 h-4" />
                        Edit Produk
                    </a>

                    <a href="{{ route('restocks.create', ['product' => $product]) }}"
                        class="flex-1 h-9 rounded-lg bg-teal-600 text-white text-xs font-semibold flex items-center justify-center gap-2 hover:bg-teal-700 transition">
                        <x-lucide-package-plus class="w-4 h-4" />
                        Restock
                    </a>
                </div>
            </x-mobile.card>

            {{-- HARGA & INFO LAIN --}}
            <x-mobile.card>
                <h2 class="text-sm font-semibold text-slate-900 mb-2">
                    Harga & Informasi
                </h2>
                <div class="grid grid-cols-2 gap-3 text-xs">
                    <x-mobile.stat-row label="Harga Beli (HPP)" :value="number_format($product->purchase_price, 0, ',', '.')" prefix="Rp" />
                    <x-mobile.stat-row label="Harga Jual" :value="number_format($product->sale_price, 0, ',', '.')"
                        prefix="Rp" />
                    <x-mobile.stat-row label="Supplier" :value="$supplierName" />
                    <x-mobile.stat-row label="Lokasi Rak" :value="$product->rack_location ?? '-'" />
                </div>
            </x-mobile.card>

            {{-- QR CODE & LABEL --}}
            <x-mobile.card>
                <h2 class="text-sm font-semibold text-slate-900 mb-2">
                    QR & Label
                </h2>
                <div class="flex flex-col items-center gap-2">
                    <div class="bg-white p-3 rounded-xl border border-slate-200">
                        {!! QrCode::format('svg')->size(120)->margin(1)->generate($product->getBarcodePayload()) !!}
                    </div>
                    <div class="flex gap-2 w-full">
                        <a href="{{ route('products.barcode', $product) }}"
                            class="flex-1 h-9 rounded-lg border border-slate-200 text-xs font-semibold text-slate-700 flex items-center justify-center gap-2 hover:bg-slate-50">
                            <x-lucide-download class="w-4 h-4" />
                            Download QR
                        </a>
                        <a href="{{ route('products.barcode.label', $product) }}"
                            class="flex-1 h-9 rounded-lg bg-slate-900 text-xs font-semibold text-white flex items-center justify-center gap-2 hover:bg-black">
                            <x-lucide-printer class="w-4 h-4" />
                            Print Label
                        </a>
                    </div>
                </div>
            </x-mobile.card>

            {{-- DESKRIPSI --}}
            @if($product->description)
                <x-mobile.card>
                    <h2 class="text-sm font-semibold text-slate-900 mb-2">
                        Deskripsi
                    </h2>
                    <p class="text-xs leading-relaxed text-slate-600">
                        {!! nl2br(e($product->description)) !!}
                    </p>
                </x-mobile.card>
            @endif

            {{-- STATISTIK & RIWAYAT --}}
            <x-mobile.card>
                <h2 class="text-sm font-semibold text-slate-900 mb-2">
                    Statistik Transaksi
                </h2>
                <div class="grid grid-cols-2 gap-2">
                    <x-mobile.stat-row label="Total Transaksi" :value="number_format($totalTransactions, 0, ',', '.')" />
                    <x-mobile.stat-row label="Masuk" :value="number_format($totalIncoming, 0, ',', '.')" />
                    <x-mobile.stat-row label="Keluar" :value="number_format($totalOutgoing, 0, ',', '.')" />
                </div>
            </x-mobile.card>

            @if($recentTransactions->isNotEmpty())
                <x-mobile.card>
                    <h2 class="text-sm font-semibold text-slate-900 mb-2">
                        Riwayat Transaksi Terbaru
                    </h2>
                    <div class="space-y-2">
                        @foreach($recentTransactions->take(5) as $transaction)
                            @php
                                $type = strtoupper((string) data_get($transaction, 'type', data_get($transaction, 'direction', '')));
                                $isIncoming = $type === 'IN';
                                $dateValue = data_get($transaction, 'date') ?? data_get($transaction, 'transaction_date');
                                $formattedDate = ($dateValue instanceof \Carbon\Carbon)
                                    ? $dateValue->format('d M Y')
                                    : (is_string($dateValue) ? $dateValue : '-');
                                $quantity = data_get($transaction, 'quantity', data_get($transaction, 'qty', data_get($transaction, 'total_quantity', 0)));
                                $reference = data_get($transaction, 'reference_number') ?? data_get($transaction, 'transaction_number') ?? '-';
                            @endphp
                            <div class="flex items-center justify-between text-xs py-1.5 border-b border-slate-50 last:border-0">
                                <div>
                                    <div class="font-semibold text-slate-900">
                                        {{ $isIncoming ? 'IN' : 'OUT' }}
                                    </div>
                                    <div class="text-[11px] text-slate-500">
                                        {{ $formattedDate }} · {{ $reference }}
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-semibold {{ $isIncoming ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $isIncoming ? '+' : '-' }}{{ number_format(abs((float) $quantity), 0, ',', '.') }}
                                    </div>
                                    <div class="text-[11px] text-slate-400">
                                        {{ $unitName }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-mobile.card>
            @endif
        </div>

        {{-- DESKTOP LAYOUT --}}
        <div class="hidden md:block space-y-6 text-sm text-slate-700">

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

                    <x-action-button href="{{ route('restocks.create', ['product' => $product]) }}" variant="success"
                        icon="plus">
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
                            <div
                                class="aspect-square rounded-xl border border-slate-200 bg-slate-50 overflow-hidden flex items-center justify-center w-full">
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
                                <div class="font-medium text-slate-900">{{ optional($product->category)->name ?? '-' }}
                                </div>

                                <div class="text-slate-500">Supplier</div>
                                <div class="font-medium text-slate-900">{{ optional($product->supplier)->name ?? '-' }}
                                </div>

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
                            <div
                                class="rounded-lg border border-slate-200 bg-white p-3 flex items-center justify-center w-28 h-28">
                                {!! QrCode::format('svg')->size(120)->margin(1)->generate($product->getBarcodePayload()) !!}
                            </div>

                            {{-- Buttons --}}
                            <div class="flex flex-col gap-2">
                                <x-action-button href="{{ route('products.barcode', $product) }}" variant="secondary"
                                    icon="download">
                                    Download QR
                                </x-action-button>

                                <x-action-button href="{{ route('products.barcode.label', $product) }}" variant="primary"
                                    icon="printer">
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
                                <span>{{ number_format($product->current_stock, 0, ',', '.') }}
                                    {{ $product->unit->name ?? '-' }}</span>
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
                                <x-table.td align="right"
                                    class="font-semibold {{ $isIncoming ? 'text-emerald-600' : 'text-red-600' }}">
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
    </div>
@endsection