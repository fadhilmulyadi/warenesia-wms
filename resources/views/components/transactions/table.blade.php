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

    $counterpartyLabel = $isIncoming ? 'Pemasok' : 'Customer';
@endphp

<x-table>
    {{-- Header Tabel --}}
    <x-table.thead>
        <x-table.th>No. Transaksi</x-table.th>
        <x-table.th>Tanggal</x-table.th>
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
                        <x-table.actions>

                            @can('view', $transaction)
                                <x-table.action-item
                                    icon="eye"
                                    :href="route($routePrefix . '.show', $transaction)"
                                >
                                    Lihat Detail
                                </x-table.action-item>
                            @endcan

                            @if($transaction->isPending())

                                @can('update', $transaction)
                                    <x-table.action-item
                                        icon="pencil"
                                        :href="route($routePrefix . '.edit', $transaction)"
                                    >
                                        Edit
                                    </x-table.action-item>
                                @endcan

                                @can('delete', $transaction)
                                    <x-table.action-item
                                        icon="trash-2"
                                        danger="true"
                                        on-click="if(confirm('Yakin ingin menghapus transaksi ini?')) { document.getElementById('delete-{{ $transaction->id }}').submit(); }"
                                    >
                                        Hapus
                                    </x-table.action-item>

                                    <form id="delete-{{ $transaction->id }}" action="{{ route($routePrefix . '.destroy', $transaction) }}" method="POST" class="hidden">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                @endcan

                            @endif

                        </x-table.actions>
                    </x-table.td>
                @endcan

            </x-table.tr>
        @empty
            <x-table.tr>
                <x-table.td colspan="8" class="text-center py-8">
                    <div class="flex flex-col items-center justify-center">
                        <div class="h-12 w-12 rounded-full bg-slate-100 flex items-center justify-center mb-3">
                           <x-lucide-clipboard-list class="h-6 w-6 text-slate-400" />
                        </div>
                        <p class="text-sm font-medium text-slate-900">
                            Tidak ada data transaksi
                        </p>
                        <p class="text-xs text-slate-500 mt-1">
                            Belum ada data {{ $isIncoming ? 'barang masuk' : 'barang keluar' }} yang tercatat.
                        </p>
                    </div>
                </x-table.td>
            </x-table.tr>
        @endforelse
    </x-table.tbody>
</x-table>