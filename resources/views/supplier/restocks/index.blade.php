@extends('layouts.app')

@section('title', 'Pesanan Saya')

@section('page-header')
    {{-- PAGE HEADER: Desktop --}}
    <div class="hidden md:block">
        <x-page-header
            title="Pesanan Masuk"
            description="Daftar permintaan stok yang perlu diproses"
        />
    </div>

    {{-- PAGE HEADER: Mobile --}}
    <div class="md:hidden">
        <x-mobile-header
            title="Pesanan Masuk"
        />
    </div>
@endsection

@section('content')
    @php
        $mobileIndexConfig = \App\Support\MobileIndexConfig::supplierRestocks($statusOptions);
    @endphp

    {{-- MOBILE LIST --}}
    <div class="md:hidden">
        <x-mobile.index
            :items="$restockOrders"
            :config="$mobileIndexConfig"
            card-view="mobile.supplier.restocks.card"
        />
    </div>

    {{-- PAGE CONTENT --}}
    <div class="hidden md:block space-y-4 text-xs max-w-6xl mx-auto">

        {{-- TOOLBAR --}}
        <x-toolbar>
            @php
                $filters = [
                    'status' => 'Status',
                    'date_range' => 'Rentang Tanggal',
                ];
                $resetKeys = ['status', 'date_from', 'date_to', 'date_range'];
            @endphp

            <x-filter-bar
                :action="route('supplier.restocks.index')"
                :search="$search"
                :sort="$sort"
                :direction="$direction"
                :filters="$filters"
                :resetKeys="$resetKeys"
                placeholder="Cari Nomor PO..."
            >
                <x-slot:filter_status>
                    <x-filter.checkbox-list
                        name="status"
                        :options="$statusOptions"
                        :selected="request()->query('status', [])"
                    />
                </x-slot:filter_status>

                <x-slot:filter_date_range>
                    <x-filter.date-range
                        from-name="date_from"
                        to-name="date_to"
                        :from-value="request('date_from')"
                        :to-value="request('date_to')"
                    />
                </x-slot:filter_date_range>
            </x-filter-bar>
        </x-toolbar>


        {{-- TABLE --}}
        <x-table>
            <x-table.thead>
                <x-table.th class="w-24" sortable name="po_number">Nomor PO</x-table.th>
                <x-table.th class="w-32" sortable name="order_date">Tanggal Pemesanan</x-table.th>
                <x-table.th class="w-32">Tanggal Kedatangan</x-table.th>
                <x-table.th class="text-center w-28">Status</x-table.th>
                <x-table.th align="right" class="w-24">Kuantitas</x-table.th>
                <x-table.th align="right" class="w-32">Total (Rp)</x-table.th>
            </x-table.thead>

            <x-table.tbody>
                @forelse($restockOrders as $restockOrder)
                    <x-table.tr :href="route('supplier.restocks.show', $restockOrder)">

                        <x-table.td class="font-mono whitespace-nowrap">
                            {{ $restockOrder->po_number }}
                        </x-table.td>

                        <x-table.td>
                            {{ $restockOrder->order_date?->format('d M Y') ?? '-' }}
                        </x-table.td>

                        <x-table.td>
                            @if($restockOrder->status === 'received')
                                <div class="flex flex-col">
                                    <span class="font-medium text-emerald-700">
                                        {{ $restockOrder->incomingTransaction?->transaction_date?->format('d M Y') ?? $restockOrder->updated_at->format('d M Y') }}
                                    </span>
                                    <span class="text-[10px] text-emerald-600/75">Received</span>
                                </div>
                            @else
                                {{ $restockOrder->expected_delivery_date?->format('d M Y') ?? '-' }}
                            @endif
                        </x-table.td>

                        <x-table.td align="center">
                            @include('components.status-badge', [
                                'status' => $restockOrder->status,
                                'label' => $restockOrder->status_label,
                            ])
                        </x-table.td>

                        <x-table.td align="right" class="tabular-nums">
                            {{ number_format($restockOrder->total_quantity, 0, ',', '.') }}
                        </x-table.td>

                        <x-table.td align="right">
                            <x-money :value="$restockOrder->total_amount" />
                        </x-table.td>

                    </x-table.tr>
                @empty
                    <x-table.tr>
                        <x-table.td colspan="6" align="center">
                            <span class="text-[11px] text-slate-500 py-6 block">
                                Tidak ada pesanan restok yang ditugaskan kepada Anda.
                            </span>
                        </x-table.td>
                    </x-table.tr>
                @endforelse
            </x-table.tbody>
        </x-table>

        {{-- PAGINATION --}}
        @if($restockOrders->hasPages() || $restockOrders->total() > 0)
            <x-advanced-pagination :paginator="$restockOrders" />
        @endif
    </div>
@endsection
