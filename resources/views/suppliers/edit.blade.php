@extends('layouts.app')

@section('title', 'Edit Supplier')

@section('page-header')
    <div class="hidden md:block">
        <div class="flex flex-col">
            <h1 class="text-base font-semibold text-slate-900">Edit Supplier</h1>
            <p class="text-xs text-slate-500">
                Update supplier information used in products and transactions.
            </p>
        </div>

        <div class="flex items-center gap-2 mt-4 md:mt-0">
            <a href="{{ route('suppliers.index') }}"
                class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50">
                Back to list
            </a>
            <button type="submit" form="supplier-form"
                class="inline-flex items-center rounded-lg bg-teal-500 px-4 py-1.5 text-xs font-semibold text-white hover:bg-teal-600">
                Save changes
            </button>
        </div>
    </div>
    <div class="md:hidden">
        <x-mobile-header title="Edit Supplier" back="{{ route('suppliers.index') }}" />
    </div>
@endsection

@section('content')
    <div class="max-w-4xl mx-auto text-xs">
        {{-- MOBILE VERSION --}}
        <x-mobile.form form-id="supplier-form-mobile" save-label="Save Changes" save-icon="save" :show-delete="true"
            delete-action="{{ route('suppliers.destroy', $supplier) }}" delete-label="Delete Supplier"
            delete-confirm="Are you sure you want to delete this supplier?">
            <x-slot:fields>
                @if($errors->any())
                    <div class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-red-700 mb-4">
                        <div class="font-semibold mb-1">There are some issues with your input:</div>
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach($errors->all() as $errorMessage)
                                <li>{{ $errorMessage }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="rounded-2xl border border-slate-200 bg-white p-4">
                    <form id="supplier-form-mobile" method="POST" action="{{ route('suppliers.update', $supplier) }}">
                        @csrf
                        @method('PUT')
                        @include('suppliers._form', compact('supplier'))
                    </form>
                </div>
            </x-slot:fields>
        </x-mobile.form>

        {{-- DESKTOP VERSION --}}
        <div class="hidden md:block space-y-4">
            @if($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-red-700">
                    <div class="font-semibold mb-1">There are some issues with your input:</div>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $errorMessage)
                            <li>{{ $errorMessage }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <x-card>
                <form id="supplier-form" method="POST" action="{{ route('suppliers.update', $supplier) }}">
                    @csrf
                    @method('PUT')
                    @include('suppliers._form', compact('supplier'))
                </form>
            </x-card>
        </div>
    </div>
@endsection