@extends('layouts.app')

@section('title', 'Catat Barang Keluar')

@section('page-header')
    <x-page-header
        title="Barang Keluar Baru"
        description="Buat transaksi penjualan ke customer."
    />
@endsection

@section('content')
    @php
        $initialItems = old('items', [
            [
                'product_id' => $prefilledProductId ?? null,
                'quantity' => $prefilledQuantity ?? 1,
                'unit_price' => $prefilledUnitPrice ?? null,
            ],
        ]);

        $transactionDate = old('transaction_date', $today);
        $notes = old('notes');
        $customerName = old('customer_name', $prefilledCustomerName);
    @endphp

    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">

        {{-- Breadcrumb --}}
        <x-breadcrumbs :items="[
            'Transaksi' => route('transactions.index', ['tab' => 'outgoing']),
            'Buat Baru' => '#',
        ]" />

        <div class="flex flex-wrap items-center gap-2 justify-end">
            <x-action-button href="{{ route('transactions.index') }}" variant="secondary" icon="arrow-left">
                Kembali
            </x-action-button>

            <x-action-button type="button" onclick="submitFormWithValidation('transaction-form')" variant="primary" icon="save">
                Simpan Data
            </x-action-button>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 max-w-5xl mx-auto">

        <form id="transaction-form" action="{{ route('sales.store') }}" method="POST" >
            @csrf

            <x-transactions.form-header :value="['date' => $transactionDate, 'notes' => $notes]">
                <x-input-label value="Customer" class="mb-1" />
                <x-text-input
                    name="customer_name"
                    type="text"
                    class="w-full"
                    :value="$customerName"
                    placeholder="Nama Customer"
                />
            </x-transactions.form-header>

            <x-transactions.items-table 
                :products="$products" 
                price-label="Harga Jual" 
                price-field="unit_price" 
                :initial-items="$initialItems"
            />
            
        </form>
    </div>
@endsection
