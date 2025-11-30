@extends('layouts.app')

@section('title', 'Dashboard Staff Gudang')

@section('page-header')
    <x-page-header
        title="Dashboard Staff Gudang"
        description="Input cepat transaksi gudang dan pantau aktivitas hari ini."
    />
@endsection

@php
    $supplierOptions = $suppliers?->pluck('name', 'id')->toArray() ?? [];
    $productOptions = $products?->mapWithKeys(
        fn ($p) => [$p->id => "{$p->name} ({$p->sku}) - Stok: {$p->current_stock}"]
    )->toArray() ?? [];
    $productStocks = $products?->mapWithKeys(
        fn ($p) => [$p->id => (int) $p->current_stock]
    )->toArray() ?? [];
    $todayList = $todayTransactions ?? [];
@endphp

@section('content')
<div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-6">
    
    {{-- LEFT SIDE --}}
    <div class="space-y-6 lg:col-span-3">
        <x-dashboard.quick-entry
            :supplierOptions="$supplierOptions"
            :productOptions="$productOptions"
            :productStocks="$productStocks"
            :prefilledType="$prefilledType"
            :prefilledSupplierId="$prefilledSupplierId"
            :prefilledProductId="$prefilledProductId"
            :prefilledCustomerName="$prefilledCustomerName"
            :prefilledQuantity="$prefilledQuantity"
        />
    </div>

    {{-- RIGHT SIDE --}}
    <div class="space-y-6 lg:col-span-3">

        <x-dashboard.card 
            title="Transaksi Hari Ini"
            subtitle="5-10 transaksi yang kamu buat hari ini."
            padding="p-4"
        >
            @if(count($todayList) === 0)
                <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-xs text-slate-500 text-center">
                    Belum ada transaksi hari ini.
                </div>
            @else
                <x-dashboard.list :items="$todayList" />
            @endif
        </x-dashboard.card>

    </div>

</div>
@endsection
