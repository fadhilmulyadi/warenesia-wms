@extends('layouts.app')

@section('title', 'Dashboard Supplier')

@section('page-header')
    <x-page-header
        title="Dashboard Supplier"
        description="Konfirmasi restock dan status pengiriman"
    />
@endsection

@section('content')
    <div class="space-y-6">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <x-dashboard.card title="Pending Confirmations" subtitle="Restock orders awaiting your response">
                <x-dashboard.list :items="$pendingRestockOrders" />
            </x-dashboard.card>

            <x-dashboard.card title="Delivery History" subtitle="Recent shipments and progress">
                <x-dashboard.list :items="$deliveryHistory" />
            </x-dashboard.card>
        </div>
    </div>
@endsection