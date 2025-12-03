@extends('layouts.app')

@section('title', 'Catat Barang Masuk')

@section('page-header')
    <x-page-header
        title="Barang Masuk Baru"
        description="Buat transaksi pembelian dari supplier."
    />
@endsection

@section('content')
    {{-- MOBILE VERSION --}}
    <x-mobile.form form-id="transaction-form-mobile" save-label="Simpan Pembelian" save-icon="save">
        <x-slot:fields>
            @if($errors->any())
                <div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700 mb-4">
                    Terdapat kesalahan input. Periksa kembali formulir di bawah.
                </div>
            @endif

            <form
                id="transaction-form-mobile"
                method="POST"
                action="{{ route('purchases.store') }}"
                class="space-y-6"
            >
                @csrf
                
                {{-- Informasi Transaksi --}}
                <x-card class="p-4 space-y-4">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-2">
                        Informasi Transaksi
                    </h3>

                    <div class="space-y-4">
                        {{-- Tanggal --}}
                        <div>
                            <x-input-label for="transaction_date_mobile" value="Tanggal Transaksi" />
                            <input
                                type="date"
                                id="transaction_date_mobile"
                                name="transaction_date"
                                value="{{ date('Y-m-d') }}"
                                class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm"
                                required
                            >
                            <x-input-error :messages="$errors->get('transaction_date')" class="mt-2" />
                        </div>

                        {{-- Supplier --}}
                        <div>
                            <x-input-label for="supplier_id_mobile" value="Supplier" />
                            <x-custom-select
                                id="supplier_id_mobile"
                                name="supplier_id"
                                :options="$suppliers->pluck('name', 'id')->toArray()"
                                :value="$prefilledSupplierId"
                                placeholder="Pilih Supplier"
                                class="mt-1 block w-full"
                                required
                            />
                            <x-input-error :messages="$errors->get('supplier_id')" class="mt-2" />
                        </div>

                        {{-- Catatan --}}
                        <div>
                            <x-input-label for="notes_mobile" value="Catatan / Referensi" />
                            <textarea
                                id="notes_mobile"
                                name="notes"
                                rows="2"
                                class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm"
                                placeholder="Contoh: No. PO External..."
                            ></textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>
                    </div>
                </x-card>

                {{-- Daftar Item --}}
                <x-card class="p-4 space-y-4">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-2">
                        Daftar Item
                    </h3>
                    
                    <div class="overflow-x-auto -mx-4 px-4">
                        <x-transactions.items-table :products="$products" :initial-items="$initialItems ?? []" />
                    </div>
                </x-card>
            </form>
        </x-slot:fields>
    </x-mobile.form>

    {{-- DESKTOP VERSION --}}
    <div class="hidden md:block space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <x-breadcrumbs :items="['Pembelian' => route('purchases.index'), 'Buat Baru' => '#']" />
            <div class="flex flex-wrap gap-2 justify-end">
                <x-action-button href="{{ route('purchases.index') }}" variant="secondary" icon="arrow-left">
                    Kembali
                </x-action-button>
                <x-action-button type="submit" form="transaction-form" variant="primary" icon="save">
                    Simpan Data
                </x-action-button>
            </div>
        </div>

        <form
            id="transaction-form"
            method="POST"
            action="{{ route('purchases.store') }}"
            class="space-y-6"
        >
            @csrf
            
            <x-card class="p-6">
                <x-transactions.form-header>
                    <x-input-label value="Supplier" class="mb-1" />
                    <x-custom-select
                        name="supplier_id"
                        :options="$suppliers->pluck('name', 'id')->toArray()"
                        :value="$prefilledSupplierId"
                        placeholder="Pilih Supplier"
                        required
                    />
                </x-transactions.form-header>

                <div class="mt-8">
                    <h3 class="text-base font-semibold text-slate-900 mb-4">Daftar Item</h3>
                    <x-transactions.items-table :products="$products" :initial-items="$initialItems ?? []" />
                </div>
            </x-card>
        </form>
    </div>
@endsection
