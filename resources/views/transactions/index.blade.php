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
    $currentPaginator = $transactions;

    $searchPlaceholder = $activeTab === 'incoming'
        ? 'Cari nomor transaksi atau pemasok...'
        : 'Cari nomor transaksi atau customer...';

    $filters = [
        'status' => 'Status',
    ];

    if ($activeTab === 'incoming') {
        $filters['supplier_id'] = 'Supplier';
        $resetKeys = ['status', 'supplier_id', 'date_from', 'date_to', 'date_range'];
    } else {
        $filters['customer_id'] = 'Customer';
        $resetKeys = ['status', 'customer_id', 'date_from', 'date_to', 'date_range'];
    }

    $filters['date_range'] = 'Rentang Tanggal';
@endphp

<div class="space-y-6">

    <div class="max-w-md">
        <x-tab-navigation 
            :tabs="$tabs" 
            :active="$activeTab" 
            base-url="transactions.index" 
        />
    </div>

    <x-toolbar>
        
        {{-- FILTER BAR (LEFT) --}}
        <x-filter-bar
            :action="route('transactions.index', ['tab' => $activeTab, 'type' => $typeParam, 'per_page' => $perPage])"
            :search="$search"
            :sort="$sort"
            :direction="$direction"
            :filters="$filters"
            :resetKeys="$resetKeys"
            :placeholder="$searchPlaceholder"
        >
            {{-- STATUS --}}
            <x-slot:filter_status>
                <x-filter.checkbox-list
                    name="status"
                    :options="$statusOptions"
                    :selected="request()->query('status', [])"
                />
            </x-slot:filter_status>

            {{-- DATE RANGE --}}
            <x-slot:filter_date_range>
                <div
                    x-data="{
                        updateMeta() {
                            const from = this.$refs.from?.value || '';
                            const to = this.$refs.to?.value || '';
                            const hasRange = !!(from || to);

                            this.$refs.flag.value = hasRange ? '1' : '';

                            this.$refs.option.textContent = hasRange
                                ? [from || 'Dari', to || 'Sampai'].join(' - ')
                                : '';

                            this.$refs.display.value = hasRange ? 'applied' : '';
                            this.$refs.display.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    }"
                    x-init="updateMeta()"
                    class="space-y-2"
                >
                    <input type="hidden" name="date_range" x-ref="flag">
                    <select class="hidden" x-ref="display">
                        <option value=""></option>
                        <option value="applied" x-ref="option"></option>
                    </select>

                    <div class="flex flex-col gap-2 w-full">
                        <x-form.date
                            name="date_from"
                            x-ref="from"
                            :value="request('date_from')"
                            placeholder="Dari tanggal"
                            x-on:change="updateMeta()"
                        />
                        <x-form.date
                            name="date_to"
                            x-ref="to"
                            :value="request('date_to')"
                            placeholder="Sampai tanggal"
                            x-on:change="updateMeta()"
                        />
                    </div>
                </div>
            </x-slot:filter_date_range>

            @if($activeTab === 'incoming')
                <x-slot:filter_supplier_id>
                    <x-filter.checkbox-list
                        name="supplier_id"
                        :options="$suppliers->map(fn ($s) => ['value' => $s->id, 'label' => $s->name])"
                        :selected="request()->query('supplier_id', [])"
                    />
                </x-slot:filter_supplier_id>
            @else
                <x-slot:filter_customer_id>
                    <x-filter.checkbox-list
                        name="customer_id"
                        :options="$customers->map(fn ($c) => ['value' => $c->id, 'label' => $c->name])"
                        :selected="request()->query('customer_id', [])"
                    />
                </x-slot:filter_customer_id>
            @endif

        </x-filter-bar>

        <div class="flex flex-wrap gap-2 justify-end w-full md:w-auto">
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

    </x-toolbar>

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
