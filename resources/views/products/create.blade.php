@extends('layouts.app')

@section('title', 'Tambah Produk')

@section('page-header')
    <div class="flex flex-col">
        <h1 class="text-base font-semibold text-slate-900">Tambah Produk</h1>
        <p class="text-xs text-slate-500">
            Daftarkan produk baru ke gudang Warenesia.
        </p>
    </div>

    <div class="flex items-center gap-2">
        <a href="{{ route('products.index') }}"
           class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50">
            Back to list
        </a>

        <button
            type="submit"
            form="product-form"
            class="inline-flex items-center rounded-lg bg-teal-500 px-4 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-teal-600">
            Save
        </button>
    </div>
@endsection

@section('content')
    <div class="max-w-6xl mx-auto space-y-4">
        @if($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700">
                <div class="font-semibold mb-1">Terjadi kesalahan:</div>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $errorMessage)
                        <li>{{ $errorMessage }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            id="product-form"
            method="POST"
            action="{{ route('products.store') }}"
        >
            @csrf

            @include('products._tabs', [
                'mode'       => 'create',
                'product'    => null,
                'categories' => $categories,
                'suppliers'  => $suppliers,
            ])
        </form>
    </div>
@endsection
