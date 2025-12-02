@extends('layouts.app')

@section('title', 'Tambah Produk')

@section('page-header')
    <div class="hidden md:block">
        <x-page-header
            title="Tambah Produk"
            description="Tambahkan produk baru ke inventaris gudang."
        />
    </div>
    <div class="md:hidden">
        <x-mobile-header
            title="Tambah Produk"
            back="{{ route('products.index') }}"
        />
    </div>
@endsection

@section('content')
    <x-mobile.form form-id="product-form-mobile" save-label="Simpan Produk" save-icon="save">
        <x-slot:fields>
            <form
                id="product-form-mobile"
                method="POST"
                action="{{ route('products.store') }}"
                enctype="multipart/form-data"
            >
                @csrf
                @include('products.form.form', [
                    'product' => null,
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
            <x-breadcrumbs :items="['Produk' => route('products.index'), 'Tambah' => '#']" />
            <div class="flex flex-wrap gap-2 justify-end">
                <x-action-button href="{{ route('products.index') }}" variant="secondary" icon="arrow-left">
                    Batal
                </x-action-button>
                <x-action-button type="submit" form="product-form" variant="primary" icon="save">
                    Simpan Produk
                </x-action-button>
            </div>
        </div>
            @include('products.form.form', [
                'product' => null,
                'categories' => $categories,
                'suppliers' => $suppliers,
                'units' => $units,
            ])
        </form>
    </div>
@endsection
