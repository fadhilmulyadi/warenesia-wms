@extends('layouts.app')

@section('title', 'Edit Kategori')

@section('page-header')
    <div class="hidden md:block">
        <x-page-header title="Edit Kategori" description="Perbarui metadata dan aturan prefix kategori" />
    </div>
    <div class="md:hidden">
        <x-mobile-header title="Edit Kategori" back="{{ route('categories.index') }}" />
    </div>
@endsection

@section('content')
    {{-- MOBILE VERSION --}}
    <x-mobile.form form-id="category-form-mobile" save-label="Simpan Perubahan" save-icon="save" :show-delete="true"
        delete-action="{{ route('categories.destroy', $category) }}" delete-label="Hapus Kategori"
        delete-confirm="Hapus kategori ini? Data akan disoft delete.">
        <x-slot:fields>
            @if($errors->any())
                <x-form-error :errors="$errors" class="mb-4" />
            @endif

            <form id="category-form-mobile" method="POST" action="{{ route('categories.update', $category) }}"
                enctype="multipart/form-data" class="space-y-6 px-4">
                @csrf
                @method('PUT')
                <div class="space-y-6">
                    <div class="bg-white rounded-xl border border-slate-200 p-4 space-y-6">
                        @include('categories.form.general', ['category' => $category])
                    </div>
                    <div class="bg-white rounded-xl border border-slate-200 p-4 space-y-6">
                        @include('categories.form.sidebar', ['category' => $category])
                    </div>
                </div>
            </form>
        </x-slot:fields>
    </x-mobile.form>

    {{-- DESKTOP VERSION --}}
    <div class="hidden md:block">
        <x-card class="p-6 space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <x-breadcrumbs :items="['Kategori' => route('categories.index'), $category->name => route('categories.edit', $category), 'Edit' => '#']" />
                <div class="flex flex-wrap gap-2 justify-end">
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

            <form id="category-form" method="POST" action="{{ route('categories.update', $category) }}"
                enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 p-6 space-y-6">
                        @include('categories.form.general', ['category' => $category])
                    </div>
                    <div class="bg-white rounded-xl border border-slate-200 p-6 space-y-6">
                        @include('categories.form.sidebar', ['category' => $category])
                    </div>
                </div>
            </form>
        </x-card>
    </div>
@endsection
