@extends('layouts.app')

@section('title', 'Edit Satuan')

@section('page-header')
    <x-page-header
        title="Edit Satuan"
        description="Perbarui informasi satuan yang digunakan produk."
    />
@endsection

@section('content')
    <div class="max-w-6xl mx-auto space-y-6 text-sm text-slate-700">
        {{-- Header: breadcrumbs + tombol aksi --}}
        <div class="flex flex-wrap items-center justify-between gap-4">
            <x-breadcrumbs :items="[
                'Satuan' => route('units.index'),
                'Edit' => '#',
            ]" />

            <div class="flex flex-wrap items-center gap-2 justify-end">
                <x-action-button href="{{ route('units.index') }}" variant="secondary" icon="arrow-left">
                    Kembali
                </x-action-button>

                <x-action-button type="submit" form="unit-form" variant="primary" icon="save">
                    Simpan Perubahan
                </x-action-button>
            </div>
        </div>

        {{-- Error alert (gaya restock) --}}
        @if($errors->any())
            <x-card class="p-4 border border-rose-200 bg-rose-50 text-rose-800">
                <p class="font-semibold text-slate-900">Periksa kembali isian Anda:</p>
                <ul class="mt-2 list-disc list-inside space-y-1">
                    @foreach($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </x-card>
        @endif

        {{-- Form utama --}}
        <form
            id="unit-form"
            action="{{ route('units.update', $unit) }}"
            method="POST"
            class="space-y-6"
        >
            @csrf
            @method('PUT')

            <x-card class="p-6 space-y-6">
                @include('units.form.general', ['unit' => $unit])
            </x-card>
        </form>
    </div>
@endsection