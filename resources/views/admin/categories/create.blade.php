@extends('layouts.app')

@section('title', 'Tambah Kategori')

@section('page-header')
    <div class="flex flex-col">
        <h1 class="text-base font-semibold text-slate-900">Tambah Kategori</h1>
        <p class="text-xs text-slate-500">
            Buat kategori baru untuk mengelompokkan produk.
        </p>
    </div>

    <div class="flex items-center gap-2">
        <a href="{{ route('admin.categories.index') }}"
           class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50">
            Back to list
        </a>
        <button
            type="submit"
            form="category-form"
            class="inline-flex items-center rounded-lg bg-teal-500 px-4 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-teal-600">
            Save
        </button>
    </div>
@endsection

@section('content')
    <div class="max-w-3xl mx-auto space-y-4">
        @if($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700">
                <div class="font-semibold mb-1">Terjadi kesalahan:</div>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-2xl border border-slate-200 bg-white p-4">
            <form
                id="category-form"
                method="POST"
                action="{{ route('admin.categories.store') }}"
                class="space-y-4"
            >
                @csrf
                @include('admin.categories._form', ['category' => new \App\Models\Category()])
            </form>
        </div>
    </div>
@endsection
