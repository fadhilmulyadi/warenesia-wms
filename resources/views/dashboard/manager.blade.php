@extends('layouts.app')

@section('title', 'Dashboard Manajer')

@section('page-header')
    <x-page-header title="Dashboard Manajer" description="Monitor persetujuan, siklus restock, dan kondisi stok" />
@endsection

@section('content')
    <div class="space-y-6">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($overview as $stat)
                <x-dashboard.card>
                    <x-dashboard.stat :title="$stat['title']" :value="$stat['value']" :subtitle="$stat['subtitle']"
                        :icon="$stat['icon']" />
                </x-dashboard.card>
            @endforeach
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <x-dashboard.card title="Persetujuan Menunggu" subtitle="Transaksi masuk dan keluar yang perlu ditinjau">
                <x-dashboard.list
                    :items="$pendingApprovals"
                    emptyTitle="Tidak ada transaksi yang menunggu"
                    emptyDescription="Semua transaksi telah ditinjau."
                    emptyIcon="check-circle"
                />
            </x-dashboard.card>

            <x-dashboard.card title="Pesanan Restok Aktif" subtitle="Pesanan dalam proses konfirmasi">
                <div class="space-y-4">
                    @forelse($activeRestocks as $order)
                        <div class="space-y-2">
                            <div class="flex items-start justify-between gap-3">
                                <div class="space-y-1">
                                    <p class="text-sm font-semibold text-slate-900">{{ $order['title'] }}</p>
                                    <p class="text-xs text-slate-500">{{ $order['description'] }}</p>
                                </div>
                                <x-status-badge :status="$order['status']" />
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="flex-1 h-3 rounded-full bg-slate-200 overflow-hidden">
                                    <div class="h-full rounded-full bg-sky-500 transition-all duration-300" @style(['width: ' . $order['progress'] . '%'])></div>
                                </div>
                                <span
                                    class="text-xs font-medium text-slate-600 whitespace-nowrap">{{ $order['progress'] }}%</span>
                                @if(!empty($order['eta']))
                                    <span class="text-xs text-slate-500 whitespace-nowrap">ETA {{ $order['eta'] }}</span>
                                @endif
                            </div>
                        </div>
                        @unless($loop->last)
                            <div class="border-t border-slate-100"></div>
                        @endunless
                        @empty
                        <x-empty-state
                            title="Belum ada pesanan restock aktif"
                            description="Pesanan yang masih berjalan akan tampil di sini."
                            icon="repeat"
                        />
                    @endforelse
                </div>
            </x-dashboard.card>
        </div>
    </div>
@endsection
