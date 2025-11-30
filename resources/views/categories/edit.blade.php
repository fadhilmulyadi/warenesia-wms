@extends('layouts.app')

@section('title', 'Edit Kategori')

@section('page-header')
    <x-page-header
        title="Edit Kategori"
        description="Perbarui nama, prefix SKU, dan metadata kategori."
    />
@endsection

@section('content')
    <x-card class="p-6 space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <x-breadcrumbs :items="['Kategori' => route('categories.index'), 'Edit' => '#']" />
            <div class="flex gap-2">
                <x-action-button href="{{ route('categories.index') }}" variant="secondary" icon="arrow-left">
                    Kembali
                </x-action-button>
                <x-action-button type="submit" form="category-form" variant="primary" icon="save">
                    Simpan Perubahan
                </x-action-button>
            </div>
        </div>

        @if($errors->any())
            <x-form-error :errors="$errors" />
        @endif

        <form
            id="category-form"
            method="POST"
            action="{{ route('categories.update', $category) }}"
            enctype="multipart/form-data"
            class="space-y-6"
        >
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 p-6 space-y-6">
                    @include('categories.form.general', ['category' => $category])
                </div>
                <div class="bg-white rounded-xl border border-slate-200 p-6 space-y-6">
                    @include('categories.form.sidebar', ['category' => $category])
                </div>
            </div>
        </form>
    </x-card>
@endsection
