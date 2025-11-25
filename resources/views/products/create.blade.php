@extends('layouts.app')

@section('title', 'Tambah Produk')

@section('page-header')
    <x-page-header
        title="Tambah Produk"
        description="Masukkan detail produk baru untuk menambahkannya ke inventaris gudang."
    />
@endsection

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        
        {{-- Breadcrumb --}}
        <nav class="flex items-center text-sm text-slate-500 gap-1">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center text-slate-600 hover:text-teal-600 transition-colors">
                <x-lucide-home class="w-4 h-4" />
            </a>
            <x-lucide-chevron-right class="w-4 h-4 text-slate-300" />

            <a href="{{ route('products.index') }}" class="hover:text-teal-600 transition-colors">
                Inventaris
            </a>
            <x-lucide-chevron-right class="w-4 h-4 text-slate-300" />

            <a href="{{ route('products.index') }}" class="hover:text-teal-600 transition-colors">
                Produk
            </a>
            <x-lucide-chevron-right class="w-4 h-4 text-slate-300" />

            <span class="font-semibold text-teal-700">
                Buat Baru
            </span>
        </nav>

        {{-- Tombol Action --}}
        <div class="flex items-center gap-2">
            {{-- Menggunakan component action-button agar lebih rapi (opsional) --}}
            <x-action-button href="{{ route('products.index') }}" variant="secondary">
                Batal
            </x-action-button>

            <x-action-button type="button" onclick="document.getElementById('product-form').submit()" variant="primary" icon="save">
                Simpan Produk
            </x-action-button>
        </div>
    </div>

    {{-- Form --}}
    <div class="max-w-7xl mx-auto">
        <form id="product-form" action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @include('products.partials._form', ['product' => null])
        </form>
    </div>
@endsection