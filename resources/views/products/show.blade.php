@extends('layouts.app')

@section('title', 'Detail Produk: ' . $product->name)

@section('page-header')
    <x-page-header
        title="Detail Produk"
        :description="'Informasi detail untuk produk: ' . $product->sku"
    />
@endsection

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        
        <x-breadcrumbs :items="[
            'Inventaris' => route('products.index'),
            'Produk'     => route('products.index'),
            $product->name => '#' 
        ]" />

        <div class="flex items-center gap-2">
            <x-action-button href="{{ route('products.index') }}" variant="secondary" icon="arrow-left">
                Kembali
            </x-action-button>

            @can('update', $product)
                <x-action-button href="{{ route('products.edit', $product->id) }}" variant="primary" icon="pencil">
                    Edit Produk
                </x-action-button>
            @endcan
        </div>
    </div>

    <div class="max-w-7xl mx-auto">
        @include('products.partials._form', [
            'product' => $product,
            'readonly' => true 
        ])
    </div>
@endsection