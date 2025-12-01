@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('page-header')
    <x-page-header title="Dashboard Admin" description="Ringkasan stok, transaksi, dan notifikasi" />
@endsection

@section('content')
    <div class="space-y-6">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($kpis as $stat)
                <x-dashboard.card>
                    <x-dashboard.stat :title="$stat['title']" :value="$stat['value']" :subtitle="$stat['subtitle']"
                        :icon="$stat['icon']" />
                </x-dashboard.card>
            @endforeach
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <x-dashboard.card title="Peringatan Stok Rendah" subtitle="Produk di bawah batas minimum">
                <x-dashboard.list :items="$lowStockAlerts" />
            </x-dashboard.card>

            <x-dashboard.card title="Akses Cepat" subtitle="Pintasan ke tugas yang sering dilakukan">
                <x-dashboard.quick-access :items="$quickLinks" />
            </x-dashboard.card>
        </div>
    </div>
@endsection