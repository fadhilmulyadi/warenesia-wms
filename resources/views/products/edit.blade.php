@extends('layouts.app')

@section('title', 'Edit Produk')

@section('page-header')
    <div class="flex flex-wrap items-center justify-between gap-4">
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
                {{ $product->sku ?? $product->name ?? 'Edit Produk' }}
            </span>
        </nav>

        {{-- Action Buttons --}}
        <div class="flex items-center gap-2">
            <a href="{{ route('products.index') }}"
                class="inline-flex items-center justify-center h-9 px-3 rounded-lg border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                Batal
            </a>

            <button type="submit" form="product-form"
                class="inline-flex items-center justify-center h-9 px-4 rounded-lg bg-teal-600 text-sm font-semibold text-white hover:bg-teal-700 shadow-sm transition-all gap-1.5">
                <x-lucide-save class="w-4 h-4" />
                <span>Perbarui Produk</span>
            </button>
        </div>
    </div>
@endsection

@section('content')
    <div class="max-w-7xl mx-auto mt-2">
        <form id="product-form" action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('products.partials._form', ['product' => $product])
        </form>
    </div>
@endsection