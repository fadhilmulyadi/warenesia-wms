@extends('layouts.app')

@section('title', 'Detail Produk')

@section('page-header')
    <div class="flex flex-col">
        <h1 class="text-base font-semibold text-slate-900">Detail Produk</h1>
        <p class="text-xs text-slate-500">
            Informasi lengkap produk gudang Warenesia.
        </p>
    </div>

    <div class="flex items-center gap-2">
        @can('update', $product)
            <a href="{{ route('products.edit', $product) }}"
               class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50">
                Edit
            </a>
        @endcan
        <a href="{{ route('products.index') }}"
           class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50">
            Back to list
        </a>
    </div>
@endsection

@section('content')
    <div class="max-w-6xl mx-auto space-y-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-4">
            @include('products._tabs', [
                'mode'       => 'show',
                'product'    => $product,
                'categories' => $categories,
                'suppliers'  => $suppliers,
            ])
        </div>
    </div>
@endsection
