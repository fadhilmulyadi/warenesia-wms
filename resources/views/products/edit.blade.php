@extends('layouts.app')

@section('title', 'Edit Produk')

@section('page-header')
    <div class="hidden md:block">
        <x-page-header
            title="Edit Produk"
            :description="'Perbarui informasi untuk produk dengan SKU: ' . $product->sku"
        />
    </div>
    <div class="md:hidden">
        <x-mobile-header
            title="Edit Produk"
            back="{{ route('products.index') }}"
        />
    </div>
@endsection

@section('content')
    {{-- MOBILE VERSION --}}
    <x-mobile.form
        form-id="product-form-mobile"
        save-label="Simpan Perubahan"
        save-icon="save"
        :show-delete="true"
        delete-action="{{ route('products.destroy', $product) }}"
        delete-label="Hapus Produk"
        delete-confirm="Hapus produk ini?"
    >
        <x-slot:fields>
            <form
                id="product-form-mobile"
                method="POST"
                action="{{ route('products.update', $product) }}"
                enctype="multipart/form-data"
            >
                @csrf
                @method('PUT')
                @include('products.form.form', [
                    'product' => $product,
                    'categories' => $categories,
                    'suppliers' => $suppliers,
                    'units' => $units,
                    'readonly' => false
                ])
            </form>
        </x-slot:fields>
    </x-mobile.form>

    {{-- DESKTOP VERSION --}}
    <div class="hidden md:block space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <x-breadcrumbs :items="[
                'Inventaris' => route('products.index'),
                'Produk'     => route('products.index'),
                $product->sku ?? $product->name ?? 'Edit Produk' => '#',
            ]" />
            <div class="flex flex-wrap gap-2 justify-end">
                <x-action-button href="{{ route('products.index') }}" variant="secondary" icon="arrow-left">
                    Batal
                </x-action-button>
                <x-action-button type="submit" form="product-form" variant="primary" icon="save">
                    Perbarui Produk
                </x-action-button>
            </div>
        </div>

        <form
            id="product-form"
            method="POST"
            action="{{ route('products.update', $product) }}"
            enctype="multipart/form-data"
        >
            @csrf
            @method('PUT')
            @include('products.form.form', [
                'product' => $product,
                'categories' => $categories,
                'suppliers' => $suppliers,
                'units' => $units,
                'readonly' => false
            ])
        </form>
    </div>
@endsection
