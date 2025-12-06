@extends('layouts.app')

@section('title', 'Pesanan Pembelian')

@section('page-header')
    <x-page-header
        title="Data Restock"
        description="Monitor siklus pengadaan dan status pengiriman supplier"
    />
@endsection

@section('content')
    @php
        $mobileIndexConfig = \App\Support\MobileIndexConfig::restocks($statusOptions);
    @endphp

    {{-- MOBILE VERSION --}}
    <div class="md:hidden">
        <x-mobile.index
            :items="$restockOrders"
            :config="$mobileIndexConfig"
            card-view="mobile.restocks.card"
        />
    </div>

    {{-- DESKTOP VERSION --}}
    <div class="hidden md:block space-y-4 text-xs max-w-6xl mx-auto">
        
        <x-toolbar>
            @php
                $filters = [
                    'status' => 'Status',
                    'date_range' => 'Rentang Tanggal',
                ];
                $resetKeys = ['status', 'date_from', 'date_to', 'date_range'];
            @endphp

            <x-filter-bar
                :action="route('restocks.index', ['per_page' => $perPage])"
                :search="$search"
                :sort="$sort"
                :direction="$direction"
                :filters="$filters"
                :resetKeys="$resetKeys"
                placeholder="Cari Nomor PO atau Supplier..."
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

            <div class="flex flex-wrap flex-none gap-2 justify-end">
                @can('export', \App\Models\RestockOrder::class)
                    <x-action-button 
                        href="{{ route('restocks.export', request()->query()) }}"
                        variant="secondary"
                        icon="download"
                    >
                        Ekspor CSV
                    </x-action-button>
                @endcan
                
                @can('create', \App\Models\RestockOrder::class)
                    <x-action-button 
                        href="{{ route('restocks.create') }}"
                        variant="primary"
                        icon="plus"
                    >
                        Tambah Pesanan
                    </x-action-button>
                @endcan
            </div>
        </x-toolbar>

        <x-table>
            <x-table.thead>
                <x-table.th class="w-24" sortable name="po_number">Nomor PO</x-table.th>
                <x-table.th class="w-32" sortable name="order_date">Tanggal Pemesanan</x-table.th>
                <x-table.th>Supplier</x-table.th>
                <x-table.th class="w-32">Tanggal Kedatangan</x-table.th>
                <x-table.th class="text-center w-28">Status</x-table.th>
                <x-table.th align="right" class="w-24">Kuantitas</x-table.th>
                <x-table.th align="right" class="w-32">Total (Rp)</x-table.th>
                @can('viewAny', \App\Models\RestockOrder::class)
                    <x-table.th align="right" class="w-20">Aksi</x-table.th>
                @endcan
            </x-table.thead>

            <x-table.tbody>
                @forelse($restockOrders as $restockOrder)
                    {{-- Pertahankan navigasi klik baris untuk UX cepat --}}
                    <x-table.tr :href="route('restocks.show', $restockOrder)"> 
                        
                        <x-table.td class="font-mono group-hover:text-slate-900 whitespace-nowrap">
                            {{ $restockOrder->po_number }}
                        </x-table.td>
                        
                        <x-table.td>
                            {{ $restockOrder->order_date?->format('d M Y') ?? '-' }}
                        </x-table.td>
                        
                        <x-table.td>
                            <div class="text-[11px] text-slate-800">
                                {{ $restockOrder->supplier->name ?? '-' }}
                            </div>
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

                        @can('viewAny', \App\Models\RestockOrder::class)
                            <x-table.td align="right">
                                <x-table.actions>
                                    @can('view', $restockOrder)
                                        <x-table.action-item
                                            icon="eye"
                                            href="{{ route('restocks.show', $restockOrder) }}"
                                        >
                                            Lihat Detail
                                        </x-table.action-item>
                                    @endcan
                                </x-table.actions>
                            </x-table.td>
                        @endcan
                    </x-table.tr>
                @empty
                    <x-table.tr>
                        <x-table.td colspan="8" class="py-10">
                            <x-empty-state
                                title="Belum ada pesanan restock"
                                description="Buat pesanan baru untuk mulai melacak pengadaan."
                                icon="repeat"
                            >
                                <x-slot name="actions">
                                    @can('create', \App\Models\RestockOrder::class)
                                        <x-action-button href="{{ route('restocks.create') }}" variant="primary" icon="plus">
                                            Tambah Pesanan
                                        </x-action-button>
                                    @endcan
                                </x-slot>
                            </x-empty-state>
                        </x-table.td>
                    </x-table.tr>
                @endforelse
            </x-table.tbody>
        </x-table>

        @if($restockOrders->hasPages() || $restockOrders->total() > 0)
            <x-advanced-pagination :paginator="$restockOrders" />
        @endif
    </div>
@endsection
