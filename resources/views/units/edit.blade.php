@extends('layouts.app')

@section('title', 'Edit Satuan')

@section('page-header')
    {{-- PAGE HEADER: Desktop --}}
    <div class="hidden md:block">
        <x-page-header title="Edit Satuan" description="Sesuaikan penamaan atau simbol satuan ukur" />
    </div>

    {{-- PAGE HEADER: Mobile --}}
    <div class="md:hidden">
        <x-mobile-header title="Edit Satuan" back="{{ route('units.index') }}" />
    </div>
@endsection

@section('content')
    <div class="max-w-6xl mx-auto text-sm text-slate-700">

        {{-- MOBILE FORM --}}
        <x-mobile.form form-id="unit-form-mobile" save-label="Simpan Perubahan" save-icon="save" :show-delete="true"
            delete-action="{{ route('units.destroy', $unit) }}" delete-label="Hapus Satuan"
            delete-confirm="Hapus satuan ini?">
            <x-slot:fields>
                @if($errors->any())
                    <x-card class="p-4 border border-rose-200 bg-rose-50 text-rose-800 mb-4">
                        <p class="font-semibold text-slate-900">Periksa kembali isian Anda:</p>
                        <ul class="mt-2 list-disc list-inside space-y-1">
                            @foreach($errors->all() as $message)
                                <li>{{ $message }}</li>
                            @endforeach
                        </ul>
                    </x-card>
                @endif

                <x-card class="p-4">
                    <form id="unit-form-mobile" action="{{ route('units.update', $unit) }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')
                        @include('units.form', ['unit' => $unit])
                    </form>
                </x-card>
            </x-slot:fields>
        </x-mobile.form>

        {{-- PAGE CONTENT --}}
        <div class="hidden md:block space-y-6">
            {{-- TOOLBAR --}}
            <div class="flex flex-wrap items-center justify-between gap-4">
                <x-breadcrumbs :items="[
            'Satuan' => route('units.index'),
            $unit->name => route('units.edit', $unit),
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

            {{-- ERROR --}}
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

            {{-- FORM --}}
            <form id="unit-form" action="{{ route('units.update', $unit) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <x-card class="p-6 space-y-6">
                    @include('units.form', ['unit' => $unit])
                </x-card>
            </form>
        </div>
    </div>
@endsection
