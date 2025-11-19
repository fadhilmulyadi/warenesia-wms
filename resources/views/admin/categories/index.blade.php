@extends('layouts.app')

@section('title', 'Kategori Produk')

@section('page-header')
    <div class="flex flex-col">
        <h1 class="text-base font-semibold text-slate-900">Categories</h1>
        <p class="text-xs text-slate-500">
            Kelola kategori produk untuk gudang Warenesia.
        </p>
    </div>

    <div class="flex items-center gap-2">
        <a href="{{ route('admin.categories.create') }}"
           class="inline-flex items-center rounded-lg bg-teal-500 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-teal-600">
            + Add category
        </a>
    </div>
@endsection

@section('content')
    <div class="space-y-4">
        @if(session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <div class="rounded-2xl border border-slate-200 bg-white p-4 text-sm">
            {{-- Search --}}
            <form method="GET" action="{{ route('admin.categories.index') }}" class="mb-3">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                    <div class="text-xs text-slate-500">
                        Total: <span class="font-semibold text-slate-700">{{ $categories->total() }}</span> categories
                    </div>

                    <div class="flex items-center gap-2">
                        <input
                            type="text"
                            name="q"
                            value="{{ $search ?? '' }}"
                            placeholder="Search category..."
                            class="w-full md:w-64 rounded-lg border border-slate-200 px-3 py-1.5 text-xs"
                        >
                        <button
                            type="submit"
                            class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50"
                        >
                            Search
                        </button>
                    </div>
                </div>
            </form>

            {{-- Table --}}
            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="min-w-full text-xs">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="px-3 py-2 text-left font-medium">Name</th>
                            <th class="px-3 py-2 text-left font-medium">Description</th>
                            <th class="px-3 py-2 text-right font-medium">Products</th>
                            <th class="px-3 py-2 text-right font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($categories as $category)
                            <tr>
                                <td class="px-3 py-2 align-top">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-slate-900">{{ $category->name }}</span>
                                    </div>
                                </td>
                                <td class="px-3 py-2 align-top text-slate-600">
                                    {{ $category->description ?: '—' }}
                                </td>
                                <td class="px-3 py-2 align-top text-right">
                                    <span class="inline-flex items-center justify-end">
                                        {{ $category->products_count }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 align-top">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('admin.categories.edit', $category) }}"
                                           class="inline-flex items-center rounded-lg border border-slate-200 px-2 py-1 text-[11px] text-slate-700 hover:bg-slate-50">
                                            Edit
                                        </a>

                                        <form method="POST"
                                              action="{{ route('admin.categories.destroy', $category) }}"
                                              onsubmit="return confirm('Yakin ingin menghapus kategori ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="inline-flex items-center rounded-lg border border-red-200 px-2 py-1 text-[11px] text-red-600 hover:bg-red-50"
                                                @if($category->products_count > 0) disabled @endif
                                            >
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                    @if($category->products_count > 0)
                                        <div class="mt-1 text-[10px] text-right text-amber-600">
                                            Tidak bisa dihapus, masih dipakai {{ $category->products_count }} produk.
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-3 py-8 text-center text-slate-500">
                                    Belum ada kategori. Tambahkan kategori pertama dengan tombol
                                    <span class="font-semibold">"Add category"</span> di atas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $categories->links() }}
            </div>
        </div>
    </div>
@endsection
