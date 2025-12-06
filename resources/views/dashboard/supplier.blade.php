@extends('layouts.app')

@section('title', 'Dashboard Supplier')

@section('page-header')
    <x-page-header
        title="Dashboard Supplier"
        description="Kelola pesanan masuk dan konfirmasi pengiriman"
    />
@endsection

@section('content')
    <div class="space-y-6">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <x-dashboard.card title="Konfirmasi Pending" subtitle="Pesanan restock yang menunggu respons Anda">
                <x-dashboard.list
                    :items="$pendingRestockOrders"
                    emptyTitle="Belum ada permintaan konfirmasi"
                    emptyDescription="Pesanan restock yang perlu dikonfirmasi akan tampil di sini."
                    emptyIcon="inbox"
                />
            </x-dashboard.card>

            <x-dashboard.card title="Riwayat Pengiriman" subtitle="Pengiriman terbaru dan progresnya">
                <x-dashboard.list
                    :items="$deliveryHistory"
                    emptyTitle="Belum ada riwayat pengiriman"
                    emptyDescription="Pengiriman yang telah dikirim akan muncul di sini."
                    emptyIcon="truck"
                />
            </x-dashboard.card>
        </div>
    </div>
@endsection
