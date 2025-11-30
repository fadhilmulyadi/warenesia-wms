@extends('layouts.app')

@section('title', 'Tambah Satuan')

@section('page-header')
    <x-page-header
        title="Tambah Satuan"
        description="Daftarkan satuan baru untuk digunakan di produk."
    />
@endsection

@section('content')
    <x-card class="p-6 space-y-6">
        <div class="flex items-center justify-between">
            <x-breadcrumbs :items="['Satuan' => route('units.index'), 'Tambah' => '#']" />
            <div class="flex gap-2">
                <x-action-button href="{{ route('units.index') }}" variant="secondary" icon="arrow-left">
                    Kembali
                </x-action-button>
                <x-action-button type="submit" form="unit-form" variant="primary" icon="save">
                    Simpan
                </x-action-button>
            </div>
        </div>

        @if($errors->any())
            <x-form-error :errors="$errors" />
        @endif

        <form id="unit-form" action="{{ route('units.store') }}" method="POST" class="space-y-6">
            @csrf
            <div class="bg-white rounded-xl border border-slate-200 p-6">
                @include('units.form.general', ['unit' => new \App\Models\Unit()])
            </div>
        </form>
    </x-card>
@endsection
