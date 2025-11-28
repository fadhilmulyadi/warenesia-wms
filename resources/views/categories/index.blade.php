@extends('layouts.app')

@section('title', 'Kategori Produk')

@section('page-header')
    <x-page-header
        title="Data Kategori"
        description="Kelola klasifikasi produk untuk mengorganisir inventaris dengan lebih terstruktur."
    />
@endsection

@section('content')
    <div class="space-y-4">

        {{-- Toolbar --}}
        <x-toolbar>
            <form method="GET" action="{{ route('categories.index') }}" class="flex-1 max-w-sm">
                <x-search-bar :value="$search" placeholder="Cari kategori..." />
            </form>

            <div class="flex items-center gap-2">
                @can('export', \App\Models\Category::class)
                    <x-action-button 
                        href="{{ route('categories.export', request()->query()) }}"
                        variant="secondary"
                        icon="download"
                    >
                        Ekspor CSV
                    </x-action-button>
                @endcan
                
                @can('create', \App\Models\Category::class)
                    <x-action-button 
                        href="{{ route('categories.create') }}"
                        variant="primary"
                        icon="plus"
                    >
                        Tambah Kategori
                    </x-action-button>
                @endcan
            </div>
        </x-toolbar>

        {{-- Tabel utama --}}
        <x-table>
            <x-table.thead>
                <x-table.th sortable name="name">Nama</x-table.th>
                <x-table.th>Deskripsi</x-table.th>
                <x-table.th align="right" sortable name="products_count">Produk</x-table.th>
                <x-table.th align="right">Aksi</x-table.th>
            </x-table.thead>

            <x-table.tbody>
                @forelse($categories as $category)
                    <x-table.tr>
                        {{-- Nama --}}
                        <x-table.td class="align-top">
                            <span class="font-medium text-slate-900">
                                {{ $category->name }}
                            </span>
                        </x-table.td>

                        {{-- Deskripsi --}}
                        <x-table.td class="align-top text-slate-600">
                            {{ $category->description ?: '-' }}
                        </x-table.td>

                        {{-- Jumlah produk --}}
                        <x-table.td align="right" class="align-top tabular-nums">
                            {{ $category->products_count }}
                        </x-table.td>

                        {{-- Aksi --}}
                        <x-table.td align="right" class="align-top">
                            @canany(['update', 'delete'], $category)
                                <x-table.actions>

                                    {{-- Edit --}}
                                    @can('update', $category)
                                        <x-table.action-item
                                            icon="pencil"
                                            href="{{ route('categories.edit', $category) }}"
                                        >
                                            Edit Kategori
                                        </x-table.action-item>
                                    @endcan

                                    {{-- Hapus --}}
                                    @can('delete', $category)
                                        @if($category->products_count == 0)
                                            <x-table.action-item
                                                icon="trash-2"
                                                danger="true"
                                                x-on:click="$dispatch('open-delete-modal', { 
                                                    action: '{{ route('categories.destroy', $category) }}',
                                                    title: 'Hapus Kategori',
                                                    message: 'Yakin ingin menghapus kategori ini?',
                                                    itemName: '{{ $category->name }}'
                                                })"
                                            >
                                                Hapus
                                            </x-table.action-item>
                                        @else
                                            <x-table.action-item
                                                icon="trash-2"
                                                danger="true"
                                                disabled
                                                class="opacity-40 cursor-not-allowed"
                                            >
                                                Tidak bisa dihapus
                                            </x-table.action-item>

                                            <div class="px-4 py-1 text-[10px] text-amber-600">
                                                Masih dipakai {{ $category->products_count }} produk.
                                            </div>
                                        @endif
                                    @endcan

                                </x-table.actions>
                            @else
                                <span class="text-slate-400 text-sm">-</span>
                            @endcanany
                        </x-table.td>

                    </x-table.tr>
                @empty
                    <x-table.tr>
                        <x-table.td colspan="4" class="py-8 text-center text-slate-500">
                            Belum ada kategori. Tambahkan kategori pertama dengan tombol diatas.
                            <span class="font-semibold">"Tambah Kategori"</span>
                        </x-table.td>
                    </x-table.tr>
                @endforelse
            </x-table.tbody>
        </x-table>

        {{-- Pagination --}}
        @if($categories->hasPages() || $categories->total() > 0)
            <x-advanced-pagination :paginator="$categories" />
        @endif

        <x-confirm-delete-modal />
    </div>
@endsection