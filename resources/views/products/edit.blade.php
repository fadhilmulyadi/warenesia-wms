@extends('layouts.app')

@section('title', 'Edit Produk')

@section('page-header')
    <x-page-header
        title="Edit Produk"
        :description="'Perbarui informasi untuk produk dengan SKU: ' . $product->sku"
    />
@endsection

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-4">
        
        {{-- Breadcrumb --}}
        <x-breadcrumbs :items="[
            'Inventaris' => route('products.index'),
            'Produk'     => route('products.index'),
            $product->sku ?? $product->name ?? 'Edit Produk' => '#',
        ]" />

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

    <div class="max-w-7xl mx-auto mt-2">
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
            $unitOptions = $units->mapWithKeys(fn ($unit) => [$unit->name => ['label' => $unit->name]])->toArray();
        @endphp
        <form id="product-form" action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('products.form.form', [
                'product' => $product,
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
