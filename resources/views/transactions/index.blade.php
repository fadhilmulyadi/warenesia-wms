@extends('layouts.app')

@section('title', 'Transactions')

@section('page-header')
    <x-page-header
        title="Data Transaksi"
        description="Lihat, kelola, dan evaluasi seluruh transaksi operasional."
    />
@endsection

@section('content')

    @php
        $tabs = [
            'incoming' => 'Barang Masuk',
            'outgoing' => 'Barang Keluar',
        ];
        
        $currentPaginator = $activeTab === 'incoming' ? $incomingTransactions : $outgoingTransactions;
    @endphp

    <div class="space-y-6">

        {{-- Navigation & Create Button --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="w-full md:w-auto min-w-[350px]">
                <x-tab-navigation 
                    :tabs="$tabs" 
                    :active="$activeTab" 
                    base-url="transactions.index" 
                />
            </div>

            <div class="flex shrink-0">
                @if($activeTab === 'incoming')
                    @can('create', \App\Models\IncomingTransaction::class)
                        <x-action-button href="{{ route('purchases.create') }}" variant="primary" icon="plus">
                            Catat Barang Masuk
                        </x-action-button>
                    @endcan
                @else
                    @can('create', \App\Models\OutgoingTransaction::class)
                        <x-action-button href="{{ route('sales.create') }}" variant="primary" icon="plus">
                            Catat Barang Keluar
                        </x-action-button>
                    @endcan
                @endif
            </div>
        </div>

        {{-- UNIFIED COMPONENT --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            
            <x-transactions.table 
                :transactions="$currentPaginator" 
                :type="$activeTab" 
            />

        </div>

        @if($currentPaginator && $currentPaginator->hasPages())
            <div class="mt-2">
                <x-advanced-pagination :paginator="$currentPaginator" />
            </div>
        @endif

    </div>
@endsection