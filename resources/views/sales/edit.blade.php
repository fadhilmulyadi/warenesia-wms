@extends('layouts.app')

@section('title', 'Edit Penjualan')

@section('page-header')
    {{-- PAGE HEADER: Desktop --}}
    <div class="hidden md:block">
        <x-page-header
            title="Edit Penjualan"
            :description="'Koreksi data pengeluaran untuk Ref #' . $sale->transaction_number"
        />
    </div>
    {{-- PAGE HEADER: Mobile --}}
    <div class="md:hidden">
        <x-mobile-header
            title="Edit Penjualan"
            back="{{ route('sales.index') }}"
        />
    </div>
@endsection

@section('content')
    {{-- MOBILE FORM --}}
    <x-mobile.form
        form-id="edit-form-mobile"
        save-label="Simpan Perubahan"
        save-icon="save"
        :show-delete="true"
        delete-action="{{ route('sales.destroy', $sale) }}"
        delete-label="Hapus Penjualan"
        delete-confirm="Hapus penjualan ini?"
        :use-delete-modal="true"
        delete-title="Hapus Penjualan"
        item-name="#{{ $sale->transaction_number }}"
    >
        <x-slot:fields>
            @if($errors->any())
                <div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700 mb-4">
                    Terdapat kesalahan input. Periksa kembali formulir di bawah.
                </div>
            @endif

            <form
                id="edit-form-mobile"
                method="POST"
                action="{{ route('sales.update', $sale) }}"
                class="space-y-6"
            >
                @csrf
                @method('PUT')
                
                {{-- SECTION: Transaction Info --}}
                <x-card class="p-4 space-y-4">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-2">
                        Informasi Transaksi
                    </h3>

                    <div class="space-y-4">
                        {{-- Date --}}
                        <div>
                            <x-input-label for="transaction_date_mobile" value="Tanggal Transaksi" />
                            <input
                                type="date"
                                id="transaction_date_mobile"
                                name="transaction_date"
                                value="{{ old('transaction_date', $sale->transaction_date->format('Y-m-d')) }}"
                                class="mt-1 block w-full h-[42px] rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-teal-500 focus:ring-teal-500 sm:text-sm"
                                required
                            >
                            <x-input-error :messages="$errors->get('transaction_date')" class="mt-2" />
                        </div>

                        {{-- Customer --}}
                        <div>
                            <x-input-label for="customer_name_mobile" value="Nama Customer" />
                            <input
                                type="text"
                                id="customer_name_mobile"
                                name="customer_name"
                                value="{{ old('customer_name', $sale->customer_name) }}"
                                class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm"
                                placeholder="Nama Customer"
                                required
                            >
                            <x-input-error :messages="$errors->get('customer_name')" class="mt-2" />
                        </div>

                        {{-- Notes --}}
                        <div>
                            <x-input-label for="notes_mobile" value="Catatan / Referensi" />
                            <textarea
                                id="notes_mobile"
                                name="notes"
                                rows="2"
                                class="mt-1 block w-full rounded-xl border-slate-300 bg-white text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-teal-500 focus:ring-teal-500 sm:text-sm"
                            >{{ old('notes', $sale->notes) }}</textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>
                    </div>
                </x-card>

                {{-- SECTION: Items --}}
                <x-card class="p-4 space-y-4">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-2">
                        Daftar Item
                    </h3>
                    
                    <div class="overflow-x-auto -mx-4 px-4">
                        <x-transactions.items-table
                            :products="$products"
                            :initial-items="$sale->items->map(fn($item) => [
                                'product_id' => $item->product_id,
                                'quantity' => $item->quantity,
                                'unit_price' => $item->unit_price,
                            ])->toArray()"
                        />
                    </div>
                </x-card>
            </form>
        </x-slot:fields>
    </x-mobile.form>

    {{-- PAGE CONTENT --}}
    <div class="hidden md:block space-y-6">
        {{-- TOOLBAR --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <x-breadcrumbs :items="[
                'Transaksi' => route('transactions.index'),
                'Barang Keluar' => route('transactions.index', ['tab' => 'outgoing']),
                '#'.$sale->transaction_number => route('sales.show', $sale),
                'Edit' => '#',
            ]" />
            <div class="flex flex-wrap gap-2 justify-end">
                <x-action-button href="{{ route('sales.index') }}" variant="secondary" icon="arrow-left">
                    Batal
                </x-action-button>
                <x-action-button type="submit" form="edit-form" variant="primary" icon="save">
                    Simpan Perubahan
                </x-action-button>
            </div>
        </div>

        {{-- FORM --}}
        <form
            id="edit-form"
            method="POST"
            action="{{ route('sales.update', $sale) }}"
            class="space-y-6"
        >
            @csrf
            @method('PUT')
            
            <x-card class="p-6">
                <x-transactions.form-header
                    :value="['date' => $sale->transaction_date->format('Y-m-d'), 'notes' => $sale->notes]"
                >
                    <x-input-label value="Nama Customer" class="mb-1" />
                    <x-text-input
                        name="customer_name"
                        :value="old('customer_name', $sale->customer_name)"
                        placeholder="Nama Customer"
                        class="w-full"
                        required
                    />
                </x-transactions.form-header>

                <div class="mt-8">
                    <h3 class="text-base font-semibold text-slate-900 mb-4">Daftar Item</h3>
                    <x-transactions.items-table
                        :products="$products"
                        :initial-items="$sale->items->map(fn($item) => [
                            'product_id' => $item->product_id,
                            'quantity' => $item->quantity,
                            'unit_price' => $item->unit_price,
                        ])->toArray()"
                    />
                </div>
            </x-card>
        </form>
    </div>
    <x-confirm-delete-modal />
@endsection
