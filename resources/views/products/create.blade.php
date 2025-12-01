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
        
            <x-breadcrumbs :items="[
                'Produk' => route('products.index'),
                'Buat Baru'  => '#'
            ]" />

            <div class="flex flex-wrap items-center gap-2 justify-end">
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
        @php
            $categoryOptions = $categories->mapWithKeys(function ($cat) {
                return [$cat->id => [
                    'label' => $cat->name, 
                    'prefix' => $cat->sku_prefix,
                ]];
            })->toArray();
            $supplierOptions = $suppliers->mapWithKeys(function ($item) {
                return [$item->id => $item->name . ($item->contact_person ? ' (' . $item->contact_person . ')' : '')];
            })->toArray();
            $unitOptions = $units->mapWithKeys(fn ($unit) => [$unit->id => ['label' => $unit->name]])->toArray();
        @endphp
        <form id="product-form" action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @include('products.form.form', [
                'product' => null,
                'categories' => $categories,
                'suppliers' => $suppliers,
                'units' => $units,
                'categoryOptions' => $categoryOptions,
                'supplierOptions' => $supplierOptions,
                'unitOptions' => $unitOptions,
            ])
        </form>
    </div>
@endsection
