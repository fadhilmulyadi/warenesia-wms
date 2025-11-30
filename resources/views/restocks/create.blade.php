@extends('layouts.app')

@section('title', 'Buat Restock')

@section('page-header')
    <x-page-header
        title="Buat Restock Baru"
        description="Input pembelian barang dari supplier."
    />
@endsection

@section('content')
    @php
        $initialItems = old('items', [[
            'product_id' => null,
            'quantity' => 1,
            'unit_cost' => 0,
        ]]);

        $selectedSupplier = old('supplier_id');
        $orderDate = old('order_date', $today);
        $expectedDelivery = old('expected_delivery_date');
        $notes = old('notes');
    @endphp

    <div class="max-w-6xl mx-auto space-y-6 text-sm text-slate-700">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <x-breadcrumbs :items="[
                'Transaksi' => route('transactions.index', ['tab' => 'restocks']),
                'Buat Restock' => '#',
            ]" />

            <div class="flex flex-wrap items-center gap-2 justify-end">
                <x-action-button href="{{ route('restocks.index') }}" variant="secondary" icon="arrow-left">
                    Kembali
                </x-action-button>

                <x-action-button type="submit" form="restock-form" variant="primary" icon="save">
                    Simpan Data
                </x-action-button>
            </div>
        </div>

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

        <form id="restock-form" action="{{ route('restocks.store') }}" method="POST" class="space-y-6">
            @csrf

            <x-card class="p-6 space-y-6">
                <p class="text-base font-semibold text-slate-900">Informasi Pesanan</p>

                <div class="space-y-2">
                    <x-input-label value="Supplier" />
                    <x-custom-select
                        name="supplier_id"
                        :options="$suppliers->pluck('name', 'id')->toArray()"
                        :value="$selectedSupplier"
                        placeholder="Pilih Supplier..."
                    />
                <x-input-error class="mt-1" :messages="$errors->get('supplier_id')" />
            </div>

            <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-3">
                <div class="space-y-2">
                    <x-input-label value="Tanggal Order" />
                    <input
                        type="date"
                        name="order_date"
                            value="{{ $orderDate }}"
                            class="w-full rounded-lg border-slate-200 text-sm"
                            required
                        >
                        <x-input-error class="mt-1" :messages="$errors->get('order_date')" />
                    </div>

                    <div class="space-y-2">
                        <x-input-label value="Perkiraan Tiba" />
                        <input
                            type="date"
                            name="expected_delivery_date"
                            value="{{ $expectedDelivery }}"
                            class="w-full rounded-lg border-slate-200 text-sm"
                        >
                        <x-input-error class="mt-1" :messages="$errors->get('expected_delivery_date')" />
                    </div>
                </div>

                <div class="space-y-2">
                    <x-input-label value="Catatan" />
                    <textarea
                        name="notes"
                        rows="3"
                        class="w-full rounded-lg border-slate-200 text-sm"
                        placeholder="Catatan tambahan (opsional)"
                    >{{ $notes }}</textarea>
                    <x-input-error class="mt-1" :messages="$errors->get('notes')" />
                </div>
            </x-card>

            <x-card class="p-6 space-y-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <p class="text-base font-semibold text-slate-900">Daftar Item</p>
                    <p class="text-xs font-medium text-slate-500">Isi produk dan jumlah yang akan dipesan.</p>
                </div>

                <x-transactions.items-table
                    :products="$products"
                    price-label="Harga Beli (HPP)"
                    price-field="unit_cost"
                    :initial-items="$initialItems"
                />
            </x-card>
        </form>
    </div>
@endsection
