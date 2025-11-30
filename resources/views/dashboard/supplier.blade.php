@extends('layouts.app')

@section('title', 'Supplier Dashboard')

@section('page-header')
    <div class="space-y-1">
        <h1 class="text-xl font-semibold text-slate-900">Supplier Dashboard</h1>
        <p class="text-sm text-slate-500">Restock confirmations and delivery status</p>
    </div>
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
