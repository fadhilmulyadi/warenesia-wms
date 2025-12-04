@extends('layouts.app')

@section('title', 'Edit Supplier')

@section('page-header')
    {{-- DESKTOP HEADER --}}
    <div class="hidden md:block">
        <x-page-header title="Edit Supplier" description="Perbarui informasi dan status supplier." />
    </div>

    {{-- MOBILE HEADER --}}
    <div class="md:hidden">
        <x-mobile-header title="Edit Supplier" back="{{ route('suppliers.index') }}" />
    </div>
@endsection

@section('content')
    <div class="max-w-6xl mx-auto">

        {{-- MOBILE FORM (x-mobile.form) --}}
        <div class="md:hidden">
            <x-mobile.form form-id="supplier-form" action="{{ route('suppliers.update', $supplier) }}" method="PUT"
                submit-label="Simpan Perubahan" :show-delete="true"
                delete-action="{{ route('suppliers.destroy', $supplier) }}" delete-label="Hapus Supplier"
                delete-confirm="Are you sure you want to delete this supplier?">
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

                    <form id="supplier-form" method="POST" action="{{ route('suppliers.update', $supplier) }}">
                        @csrf
                        @method('PUT')
                        @include('suppliers._form', compact('supplier'))
                    </form>
                </x-slot:fields>
            </x-mobile.form>
        </div>

        {{-- DESKTOP FORM --}}
        <div class="hidden md:block space-y-4">
            <div class="flex items-center justify-between flex-wrap gap-3">
                <x-breadcrumbs :items="[
            'Suppliers' => route('suppliers.index'),
            $supplier->name => '#',
        ]" />

                <div class="flex flex-wrap gap-2">
                    <x-action-button href="{{ route('suppliers.index') }}" variant="secondary" icon="arrow-left">
                        Kembali
                    </x-action-button>

                    <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}"
                        onsubmit="return confirm('Are you sure you want to delete this supplier?');">
                        @csrf
                        @method('DELETE')
                        <x-action-button type="submit" variant="outline-danger" icon="trash-2">
                            Hapus
                        </x-action-button>
                    </form>

                    <x-action-button type="submit" form="supplier-form-desktop" variant="primary" icon="save">
                        Simpan Perubahan
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

            <form id="supplier-form-desktop" method="POST" action="{{ route('suppliers.update', $supplier) }}">
                @csrf
                @method('PUT')
                @include('suppliers._form', compact('supplier'))
            </form>
        </div>
    </div>
@endsection