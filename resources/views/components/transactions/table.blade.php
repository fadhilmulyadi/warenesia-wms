@props([
    'transactions',
    'type',
])

@php
    $isIncoming = $type === 'incoming';

    $routePrefix = $isIncoming ? 'purchases' : 'sales';

    $modelClass = $isIncoming
        ? \App\Models\IncomingTransaction::class
        : \App\Models\OutgoingTransaction::class;

    $counterpartyLabel = $isIncoming ? 'Supplier' : 'Customer';
@endphp

<x-table>
    {{-- Header Tabel --}}
    <x-table.thead>
        <x-table.th sortable name="transaction_number">No. Transaksi</x-table.th>
        <x-table.th sortable name="transaction_date">Tanggal</x-table.th>
        <x-table.th>{{ $counterpartyLabel }}</x-table.th>
        <x-table.th align="right">Item</x-table.th>
        <x-table.th align="right">Qty</x-table.th>
        <x-table.th align="right">Total</x-table.th>
        <x-table.th class="text-center">Status</x-table.th>

        @can('viewAny', $modelClass)
            <x-table.th align="right">Aksi</x-table.th>
        @endcan
    </x-table.thead>

    {{-- Body Tabel --}}
    <x-table.tbody>
        @forelse($transactions as $transaction)
            <x-table.tr :href="route($routePrefix . '.show', $transaction)">
                
                {{-- Nomor Transaksi --}}
                <x-table.td class="font-mono whitespace-nowrap group-hover:text-slate-900">
                    {{ $transaction->transaction_number }}
                </x-table.td>

                {{-- Tanggal --}}
                <x-table.td>
                    {{ $transaction->transaction_date->format('d M Y') }}
                </x-table.td>

                {{-- Supplier / Customer --}}
                <x-table.td>
                    <div class="flex flex-col">
                        <span class="text-slate-900">
                            @if($isIncoming)
                                {{ $transaction->supplier->name ?? '-' }}
                            @else
                                {{ $transaction->customer_name }}
                            @endif
                        </span>
                        <span class="text-[10px] text-slate-400">
                            Oleh: {{ $transaction->createdBy->name ?? '-' }}
                        </span>
                    </div>
                </x-table.td>

                {{-- Total Items --}}
                <x-table.td align="right">
                    {{ $transaction->total_items }}
                </x-table.td>

                {{-- Total Qty --}}
                <x-table.td align="right">
                    {{ $transaction->total_quantity }}
                </x-table.td>

                {{-- Total Amount --}}
                <x-table.td align="right">
                    <x-money :value="$transaction->total_amount" />
                </x-table.td>

                {{-- Status --}}
                <x-table.td class="text-center">
                    @include('components.status-badge', [
                        'status' => $transaction->status,
                        'label' => ucfirst(str_replace('_', ' ', $transaction->status)),
                    ])
                </x-table.td>

                {{-- Aksi --}}
                @can('viewAny', $modelClass)
                    <x-table.td align="right">

                        @if($transaction->isPending())
                            
                            <x-table.actions>
                                
                                @can('update', $transaction)
                                    <x-table.action-item
                                        icon="pencil"
                                        :href="route($routePrefix . '.edit', $transaction)"
                                    >
                                        Edit
                                    </x-table.action-item>
                                @endcan

                                @can('delete', $transaction)
                                    <button
                                        type="button"
                                        class="group flex w-full items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors"
                                        x-data
                                        x-on:click="$dispatch('open-delete-modal', {
                                            action: '{{ route($routePrefix . '.destroy', $transaction) }}',
                                            title: '{{ $isIncoming ? 'Hapus Barang Masuk?' : 'Hapus Barang Keluar?' }}',
                                            itemName: '#{{ $transaction->transaction_number }}',
                                            message: 'Apakah Anda yakin ingin menghapus transaksi <b>#{{ $transaction->transaction_number }}</b>?<br>Stok produk akan dikembalikan ke posisi sebelumnya.'
                                        })"
                                    >
                                        <x-lucide-trash-2 class="mr-3 h-4 w-4" />
                                        Hapus
                                    </button>
                                @endcan

                            </x-table.actions>

                        @else
                            <div class="flex items-center justify-end pr-3 text-slate-400 cursor-help" title="Transaksi Terkunci (Final)">
                                <x-lucide-lock class="h-4 w-4" />
                            </div>
                        @endif

                    </x-table.td>
                @endcan

            </x-table.tr>
        @empty
            <x-table.tr>
                <x-table.td colspan="8" class="py-10">
                    <x-empty-state
                        title="Tidak ada data transaksi"
                        :description="'Belum ada data ' . ($isIncoming ? 'barang masuk' : 'barang keluar') . ' yang tercatat.'"
                        icon="{{ $isIncoming ? 'log-in' : 'log-out' }}"
                        containerClass="border-2 border-dashed border-slate-200 bg-slate-50 rounded-xl py-6"
                    >
                        <x-slot name="actions">
                            @can('create', $modelClass)
                                <x-action-button
                                    href="{{ route($routePrefix . '.create') }}"
                                    variant="primary"
                                    icon="plus"
                                >
                                    {{ $isIncoming ? 'Catat Barang Masuk' : 'Catat Barang Keluar' }}
                                </x-action-button>
                            @endcan
                        </x-slot>
                    </x-empty-state>
                </x-table.td>
            </x-table.tr>
        @endforelse
    </x-table.tbody>
</x-table>
