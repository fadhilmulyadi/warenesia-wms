@extends('layouts.app')

@section('title', 'Tambah Kategori')

@section('page-header')
    {{-- PAGE HEADER: Desktop --}}
    <div class="hidden md:block">
        <x-page-header title="Kategori Produk" description="Kelola awalan SKU dan metadata kategori untuk WMS." />
    </div>
    {{-- PAGE HEADER: Mobile --}}
    <div class="md:hidden">
        <x-mobile-header title="Tambah Kategori" back="{{ route('categories.index') }}" />
    </div>
@endsection

@section('content')
    {{-- MOBILE FORM --}}
    <x-mobile.form form-id="category-form-mobile" save-label="Simpan Kategori" save-icon="save">
        <x-slot:fields>
            @if($errors->any())
                <x-form-error :errors="$errors" class="mb-4" />
            @endif

            <form id="category-form-mobile" method="POST" action="{{ route('categories.store') }}"
                enctype="multipart/form-data" class="space-y-6 px-4">
                @csrf
                <div class="space-y-6">
                    {{-- SECTION: General --}}
                    <div class="bg-white rounded-xl border border-slate-200 p-4 space-y-6">
                        @include('categories.form.general', ['category' => new \App\Models\Category()])
                    </div>
                    {{-- SECTION: Sidebar --}}
                    <div class="bg-white rounded-xl border border-slate-200 p-4 space-y-6">
                        @include('categories.form.sidebar', ['category' => new \App\Models\Category()])
                    </div>
                </div>
            </form>
        </x-slot:fields>
    </x-mobile.form>

    {{-- PAGE CONTENT --}}
    <div class="hidden md:block">
        <x-card class="p-6 space-y-6">
            {{-- TOOLBAR --}}
            <div class="flex flex-wrap items-center justify-between gap-3">
                <x-breadcrumbs :items="['Kategori' => route('categories.index'), 'Tambah' => '#']" />
                <div class="flex flex-wrap gap-2 justify-end">
                    <x-action-button href="{{ route('categories.index') }}" variant="secondary" icon="arrow-left">
                        Kembali
                    </x-action-button>
                    <x-action-button type="submit" form="category-form" variant="primary" icon="save">
                        Simpan Kategori
                    </x-action-button>
                </div>
            </div>

            @if($errors->any())
                <x-form-error :errors="$errors" />
            @endif

            {{-- FORM --}}
            <form id="category-form" method="POST" action="{{ route('categories.store') }}" enctype="multipart/form-data"
                class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 p-6 space-y-6">
                        @include('categories.form.general', ['category' => new \App\Models\Category()])
                    </div>
                    <div class="bg-white rounded-xl border border-slate-200 p-6 space-y-6">
                        @include('categories.form.sidebar', ['category' => new \App\Models\Category()])
                    </div>
                </div>
            </form>
        </x-card>
    </div>
@endsection