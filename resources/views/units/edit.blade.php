@extends('layouts.app')

@section('title', 'Edit Satuan')

@section('page-header')
    <x-page-header
        title="Edit Satuan"
        description="Perbarui informasi satuan yang digunakan produk."
    />
@endsection

@section('content')
    <x-card class="p-6 space-y-6">
        <div class="flex items-center justify-between">
            <x-breadcrumbs :items="['Satuan' => route('units.index'), 'Edit' => '#']" />
            <div class="flex gap-2">
                <x-action-button href="{{ route('units.index') }}" variant="secondary" icon="arrow-left">
                    Kembali
                </x-action-button>
                <x-action-button type="submit" form="unit-form" variant="primary" icon="save">
                    Simpan Perubahan
                </x-action-button>
            </div>
        </div>

        @if($errors->any())
            <x-form-error :errors="$errors" />
        @endif

        <form id="unit-form" action="{{ route('units.update', $unit) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            <div class="bg-white rounded-xl border border-slate-200 p-6">
                @include('units.form.general', ['unit' => $unit])
            </div>
        </form>
    </x-card>
@endsection
