@extends('layouts.app')

@section('title', 'Satuan Produk')

@section('page-header')
    <x-page-header title="Satuan Produk" description="Kelola daftar satuan yang digunakan pada master produk." />
@endsection

@section('content')
    @php
        $mobileIndexConfig = \App\Support\MobileIndexConfig::units();
    @endphp

    {{-- MOBILE LIST --}}
    <div class="md:hidden">
        <x-mobile.index :items="$units" :config="$mobileIndexConfig" card-view="mobile.units.card" />
    </div>

    {{-- PAGE CONTENT --}}
    <div class="hidden md:block space-y-4 text-xs max-w-6xl mx-auto">

        {{-- TOOLBAR --}}
        <x-toolbar>
            <div class="flex flex-wrap items-center justify-between gap-3 w-full">
                <form method="GET" action="{{ route('units.index') }}" class="w-full sm:w-auto">
                    <x-search-bar name="q" :value="$search" placeholder="Cari satuan..." class="w-full sm:w-72" />
                </form>

                <div class="flex flex-wrap gap-2 justify-end">
                    <x-action-button href="{{ route('units.create') }}" variant="primary" icon="plus">
                        Tambah Satuan
                    </x-action-button>
                </div>
            </div>
        </x-toolbar>

        {{-- TABLE --}}
        <x-table>
            <x-table.thead>
                <x-table.th>Nama</x-table.th>
                <x-table.th>Deskripsi</x-table.th>
                <x-table.th align="right">Dipakai Produk</x-table.th>
                <x-table.th align="right">Aksi</x-table.th>
            </x-table.thead>

            <x-table.tbody>
                @forelse($units as $unit)
                    <x-table.tr>
                        <x-table.td class="font-semibold text-slate-900">
                            {{ $unit->name }}
                        </x-table.td>

                        <x-table.td class="text-slate-600">
                            {{ $unit->description ?: '-' }}
                        </x-table.td>

                        <x-table.td align="right" class="tabular-nums text-slate-900">
                            {{ $unit->products_count }}
                        </x-table.td>

                        <x-table.td align="right">
                            <x-table.actions>
                                <x-table.action-item icon="pencil" href="{{ route('units.edit', $unit) }}">
                                    Edit
                                </x-table.action-item>

                                @if(!$unit->products_count)
                                    <x-table.action-item icon="trash-2" danger="true" x-on:click="$dispatch('open-delete-modal', { 
                                                                                                        action: '{{ route('units.destroy', $unit) }}',
                                                                                                        title: 'Hapus Satuan',
                                                                                                        message: 'Yakin ingin menghapus satuan ini?',
                                                                                                        itemName: '{{ $unit->name }}'
                                                                                                    })">
                                        Hapus
                                    </x-table.action-item>
                                @else
                                    <span class="text-[11px] text-slate-400">Digunakan</span>
                                @endif
                            </x-table.actions>
                        </x-table.td>
                    </x-table.tr>
                @empty
                    <x-table.tr>
                        <x-table.td colspan="4" class="py-8 text-center text-slate-500">
                            Belum ada data satuan. Tambahkan satuan pertama untuk mulai digunakan di produk.
                        </x-table.td>
                    </x-table.tr>
                @endforelse
            </x-table.tbody>
        </x-table>

        @if($units->hasPages() || $units->total() > 0)
            <x-advanced-pagination :paginator="$units" />
        @endif

    </div>

    <x-confirm-delete-modal />
@endsection