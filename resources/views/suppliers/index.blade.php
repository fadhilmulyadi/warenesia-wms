@extends('layouts.app')

@section('title', 'Supplier')

@section('page-header')
    <x-page-header title="Data Supplier" description="Database pemasok dan riwayat relasi pengadaan" />
@endsection

@section('content')
    @php
        $mobileIndexConfig = \App\Support\MobileIndexConfig::suppliers();
    @endphp

    {{-- MOBILE LIST --}}
    <div class="md:hidden">
        <x-mobile.index :items="$suppliers" :config="$mobileIndexConfig" card-view="mobile.suppliers.card" />
    </div>

    {{-- PAGE CONTENT --}}
    <div class="hidden md:block space-y-4">

        {{-- TOOLBAR --}}
        <x-toolbar>
            <form method="GET" action="{{ route('suppliers.index') }}" class="flex-1 max-w-sm">
                <x-search-bar :value="$search" placeholder="Cari nama supplier..." />
            </form>

            <div class="flex items-center gap-2">
                @can('export', \App\Models\Supplier::class)
                    <x-action-button href="{{ route('suppliers.export', request()->query()) }}" variant="secondary"
                        icon="download">
                        Ekspor CSV
                    </x-action-button>
                @endcan

                @can('create', \App\Models\Supplier::class)
                    <x-action-button href="{{ route('suppliers.create') }}" variant="primary" icon="plus">
                        Tambah Supplier
                    </x-action-button>
                @endcan
            </div>
        </x-toolbar>

        {{-- TABLE --}}
        <x-card class="p-0 overflow-hidden">
            <div class="w-full overflow-x-auto">
                <x-table>
                    <x-table.thead>
                        <x-table.th sortable name="name">Nama</x-table.th>
                        <x-table.th>Kontak</x-table.th>
                        <x-table.th>Email</x-table.th>
                        <x-table.th>No. Handphone</x-table.th>
                        <x-table.th sortable name="average_rating">Rata-rata rating</x-table.th>
                        <x-table.th sortable name="rated_restock_count">Restock dinilai</x-table.th>
                        <x-table.th class="text-center">Status</x-table.th>
                        @can('create', \App\Models\Supplier::class)
                            <x-table.th align="right">Aksi</x-table.th>
                        @endcan
                    </x-table.thead>
                    <x-table.tbody>
                        @forelse($suppliers as $supplier)
                            <x-table.tr>
                                <x-table.td class="font-semibold text-slate-900">
                                    <div class="flex flex-col">
                                        <span>
                                            {{ $supplier->name }}
                                        </span>
                                        @if($supplier->city || $supplier->country)
                                            <span class="text-[10px] text-slate-500 font-normal">
                                                {{ $supplier->city ? $supplier->city . ', ' : '' }}{{ $supplier->country }}
                                            </span>
                                        @endif
                                    </div>
                                </x-table.td>
                                <x-table.td>
                                    <div class="flex flex-col">
                                        <span>{{ $supplier->contact_person ?: '-' }}</span>
                                    </div>
                                </x-table.td>
                                <x-table.td>
                                    {{ $supplier->email ?: '-' }}
                                </x-table.td>
                                <x-table.td>
                                    {{ $supplier->phone ?: '-' }}
                                </x-table.td>
                                <x-table.td>
                                    @if($supplier->average_rating !== null)
                                        <div class="inline-flex items-center gap-1">
                                            <span
                                                class="text-[12px] text-slate-900">{{ number_format((float) $supplier->average_rating, 1) }}</span>
                                            <x-lucide-star class="h-3 w-3 text-yellow-400" />
                                        </div>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </x-table.td>
                                <x-table.td>
                                    {{ $supplier->rated_restock_count ?? 0 }}
                                </x-table.td>
                                <x-table.td class="text-center">
                                    @if($supplier->is_active)
                                        <x-badge variant="success">Active</x-badge>
                                    @else
                                        <x-badge variant="neutral">Inactive</x-badge>
                                    @endif
                                </x-table.td>
                                @can('create', \App\Models\Supplier::class)
                                    <x-table.td align="right">
                                        <x-supplier.actions :supplier="$supplier" />
                                    </x-table.td>
                                @endcan
                            </x-table.tr>
                        @empty
                            <x-table.tr>
                                <x-table.td colspan="8" class="py-10">
                                    <x-empty-state
                                        title="Supplier tidak ditemukan"
                                        description="Coba ubah filter pencarian atau tambahkan supplier baru."
                                        icon="building-2"
                                    >
                                        <x-slot name="actions">
                                            @can('create', \App\Models\Supplier::class)
                                                <x-action-button href="{{ route('suppliers.create') }}" variant="primary" icon="plus">
                                                    Tambah Supplier
                                                </x-action-button>
                                            @endcan
                                        </x-slot>
                                    </x-empty-state>
                                </x-table.td>
                            </x-table.tr>
                        @endforelse
                    </x-table.tbody>
                </x-table>
            </div>

        </x-card>

        {{-- PAGINATION --}}
        @if($suppliers->hasPages() || $suppliers->total() > 0)
            <x-advanced-pagination :paginator="$suppliers" />
        @endif
    </div>


    <x-confirm-delete-modal />
@endsection
