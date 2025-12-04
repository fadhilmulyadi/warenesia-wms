@extends('layouts.app')

@section('title', 'Add Supplier')

@section('page-header')
    {{-- DESKTOP HEADER --}}
    <div class="hidden md:block">
        <x-page-header title="Add Supplier" description="Register a new supplier for Warenesia warehouse operations." />
    </div>

    {{-- MOBILE HEADER --}}
    <div class="md:hidden">
        <x-mobile-header title="Add Supplier" back="{{ route('suppliers.index') }}" />
    </div>
@endsection

@section('content')
    <div class="max-w-6xl mx-auto">

        {{-- MOBILE FORM (x-mobile.form) --}}
        <div class="md:hidden">
            <x-mobile.form form-id="supplier-form" action="{{ route('suppliers.store') }}" method="POST"
                submit-label="Simpan Supplier">
                <x-slot:fields>
                    @if($errors->any())
                        <div class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-red-700 mb-4 text-xs">
                            <div class="font-semibold mb-1">There are some issues with your input:</div>
                            <ul class="list-disc list-inside space-y-0.5">
                                @foreach($errors->all() as $errorMessage)
                                    <li>{{ $errorMessage }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="supplier-form" method="POST" action="{{ route('suppliers.store') }}">
                        @csrf
                        @include('suppliers._form', ['supplier' => null])
                    </form>
                </x-slot:fields>
            </x-mobile.form>
        </div>

        {{-- DESKTOP FORM --}}
        <div class="hidden md:block space-y-4">
            <div class="flex items-center justify-between flex-wrap gap-3">
                <x-breadcrumbs :items="[
            'Suppliers' => route('suppliers.index'),
            'Add Supplier' => '#',
        ]" />

                <div class="flex flex-wrap gap-2">
                    <x-action-button href="{{ route('suppliers.index') }}" variant="secondary" icon="arrow-left">
                        Kembali
                    </x-action-button>

                    <x-action-button type="submit" form="supplier-form-desktop" variant="primary" icon="save">
                        Simpan Supplier
                    </x-action-button>
                </div>
            </div>

            @if($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-red-700 text-sm">
                    <div class="font-semibold mb-1">There are some issues with your input:</div>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $errorMessage)
                            <li>{{ $errorMessage }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="supplier-form-desktop" method="POST" action="{{ route('suppliers.store') }}">
                @csrf
                @include('suppliers._form', ['supplier' => null])
            </form>
        </div>
    </div>
@endsection