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
        <x-breadcrumbs :items="[
            'Produk' => route('products.index'),
            'Buat Baru'  => '#'
        ]" />

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