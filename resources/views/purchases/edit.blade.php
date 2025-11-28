@extends('layouts.app')

@section('title', 'Edit Transaki Masuk')

@section('page-header')
    <x-page-header
        title="Edit Transaksi Masuk"
        :description="'Edit detail transaksi: ' . $purchase->transaction_number"
    />
@endsection

@section('content')
    @php
        $currentItems = $purchase->items->map(function($item) {
            return [
                'product_id' => $item->product_id,
                'quantity' => (int) $item->quantity,
                'unit_cost' => (float) $item->unit_cost,
            ];
        })->values()->toArray();

        $initialItems = old('items', $currentItems);
      
    @endphp
    
    <div class="max-w-5xl mx-auto">
        <div class="flex items-center justify-end gap-2 mb-4">
            <x-action-button href="{{ route('transactions.index') }}" variant="secondary">
                Batal
            </x-action-button>
            <x-action-button type="button" onclick="document.getElementById('edit-form').submit()" variant="primary" icon="save">
                Simpan Perubahan
            </x-action-button>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-4 space-y-4 shadow-sm">
            <form id="edit-form" method="POST" action="{{ route('purchases.update', $purchase) }}">
                @csrf
                @method('PUT')

                <x-transactions.form-header :value="['date' => $purchase->transaction_date->format('Y-m-d'), 'notes' => $purchase->notes]">
                    <x-input-label value="Pilih Supplier" class="mb-1" />
                    <x-custom-select 
                        name="supplier_id" 
                        :options="$suppliers->pluck('name', 'id')->toArray()" 
                        placeholder="Cari Supplier..." 
                        :value="old('supplier_id', $purchase->supplier_id)"
                    />
                </x-transactions.form-header>

                <x-transactions.items-table 
                    :products="$products" 
                    price-label="Harga Beli (HPP)" 
                    price-field="unit_cost" 
                    :initial-items="$initialItems"
                />
            </form>
        </div>
    </div>
@endsection