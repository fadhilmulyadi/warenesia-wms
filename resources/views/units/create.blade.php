@extends('layouts.app')

@section('title', 'Tambah Satuan')

@section('page-header')
    {{-- PAGE HEADER: Desktop --}}
    <div class="hidden md:block">
        <x-page-header title="Tambah Satuan" description="Definisikan satuan ukur baru dalam sistem" />
    </div>

    {{-- PAGE HEADER: Mobile --}}
    <div class="md:hidden">
        <x-mobile-header title="Tambah Satuan" back="{{ route('units.index') }}" />
    </div>
@endsection

@section('content')
    <div class="max-w-6xl mx-auto text-sm text-slate-700">

        {{-- MOBILE FORM --}}
        <x-mobile.form form-id="unit-form-mobile" save-label="Simpan Satuan" save-icon="save">
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
                    <form id="unit-form-mobile" action="{{ route('units.store') }}" method="POST" class="space-y-6">
                        @csrf
                        @include('units.form', ['unit' => new \App\Models\Unit()])
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
            'Tambah' => '#',
        ]" />

                <div class="flex flex-wrap items-center gap-2 justify-end">
                    <x-action-button href="{{ route('units.index') }}" variant="secondary" icon="arrow-left">
                        Kembali
                    </x-action-button>

                    <x-action-button type="submit" form="unit-form" variant="primary" icon="save">
                        Simpan
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
            <form id="unit-form" action="{{ route('units.store') }}" method="POST" class="space-y-6">
                @csrf

                <x-card class="p-6 space-y-6">
                    @include('units.form', ['unit' => new \App\Models\Unit()])
                </x-card>
            </form>
        </div>
    </div>
@endsection
