@extends('layouts.app')

@section('title', 'Catat Barang Masuk')

@section('page-header')
    <x-page-header
        title="Barang Masuk Baru"
        description="Buat transaksi pembelian dari supplier."
    />
@endsection

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        
        {{-- Breadcrumb --}}
        <x-breadcrumbs :items="[
            'Transaksi' => route('transactions.index', ['tab' => 'incoming']),
            'Buat Baru' => '#',
        ]" />

        {{-- Action Buttons --}}
        <div class="flex items-center gap-2">
            <x-action-button href="{{ route('transactions.index') }}" variant="secondary" icon="arrow-left">
                Kembali
            </x-action-button>

            <x-action-button type="button" onclick="document.getElementById('transaction-form').submit()" variant="primary" icon="save">
                Simpan Data
            </x-action-button>
        </div>
    </div>

    {{-- MAIN FORM WRAPPER --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 max-w-5xl mx-auto"
         x-data="{
             items: [{ product_id: '', quantity: 1, unit_cost: 0 }],
             addItem() { this.items.push({ product_id: '', quantity: 1, unit_cost: 0 }) },
             removeItem(index) { if(this.items.length > 1) this.items.splice(index, 1) }
         }">

        <form id="transaction-form" action="{{ route('purchases.store') }}" method="POST">
            @csrf

            <x-transactions.form-header>
                <x-input-label value="Pilih Supplier" class="mb-1" />
                <x-custom-select name="supplier_id" :options="$suppliers->pluck('name', 'id')->toArray()" placeholder="Cari Supplier..." />
            </x-transactions.form-header>

            <x-transactions.items-table 
                :products="$products" 
                price-label="Harga Beli (HPP)" 
                price-field="unit_cost" 
            />
            
        </form>
    </div>
@endsection