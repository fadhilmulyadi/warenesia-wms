@extends('layouts.app')

@section('title', 'Edit Transaksi Keluar')

@section('page-header')
    <x-page-header 
        title="Edit Transaksi Keluar"
        :description="'Edit detail transaksi: ' . $sale->transaction_number"
    />
@endsection

@section('content')
    @php
        $currentItems = $sale->items->map(function($item) {
            return [
                'product_id' => $item->product_id,
                'quantity' => (int) $item->quantity,
                'unit_price' => (float) $item->unit_price
            ];
        })->values()->toArray();
        
        $initialItems = old('items', $currentItems);
    @endphp

    <div class="max-w-5xl mx-auto">
        <div class="flex items-center justify-end gap-2 mb-4">
            <x-action-button href="{{ route('transactions.index') }}" variant="secondary">
                Batal
            </x-action-button>
            <x-action-button type="button" onclick="submitFormWithValidation('edit-form')" variant="primary" icon="save">
                Simpan Perubahan
            </x-action-button>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-4 space-y-4 shadow-sm">
            <form id="edit-form" method="POST" action="{{ route('sales.update', $sale) }}">
                @csrf
                @method('PUT')

                <x-transactions.form-header :value="['date' => $sale->transaction_date->format('Y-m-d'), 'notes' => $sale->notes]">
                    <x-input-label for="customer_name" value="Nama Pelanggan" class="mb-1" />
                    <x-text-input 
                        id="customer_name" 
                        name="customer_name" 
                        type="text" 
                        class="w-full" 
                        :value="old('customer_name', $sale->customer_name)" 
                        required 
                        autofocus 
                        autocomplete="customer_name"
                    />
                </x-transactions.form-header>

                <!-- <div class="space-y-4">
                    <x-input-error class="mt-2" :messages="$errors->get('customer_name')" />
                </div> -->

                <x-transactions.items-table 
                    :products="$products" 
                    price-label="Harga Jual" 
                    price-field="unit_price"
                    :initial-items="$initialItems"
                />
            </form>
        </div>
    </div>
@endsection
