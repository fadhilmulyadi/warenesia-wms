@extends('layouts.app')

@section('title', 'Pesanan Pembelian')

@section('page-header')
    <x-page-header
        title="Data Restock"
        description="Kelola siklus pengadaan barang ke pemasok dan monitor status pengiriman pesanan."
    />
@endsection

@section('content')
    <div class="space-y-4 text-xs max-w-6xl mx-auto">
        
        <x-toolbar>
            <form method="GET" action="{{ route('restocks.index') }}" class="flex-1 max-w-sm">
                <x-search-bar :value="$search" placeholder="Cari Nomor PO..." />
            </form>

            <div class="flex items-center gap-2">
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
                <x-table.th class="w-24">Nomor PO</x-table.th>
                <x-table.th class="w-32">Tanggal Pemesanan</x-table.th>
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
                            {{ $restockOrder->expected_delivery_date?->format('d M Y') ?? '-' }}
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
                                {{-- Menggunakan komponen aksi tabel --}}
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
                        <x-table.td colspan="8" align="center">
                            <span class="text-[11px] text-slate-500 py-6 block">
                                No restock orders yet.
                            </span>
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