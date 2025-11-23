@extends('layouts.app')

@section('title', 'Dashboard - Warenesia')

@section('page-header')
    <div class="flex flex-col">
        <h1 class="text-xl md:text-2xl font-semibold text-slate-900">Dashboard</h1>
        <p class="text-xs md:text-sm text-slate-500">
            Ringkasan cepat untuk {{ $user->name ?? 'user' }} dengan modul yang sesuai izin Anda.
        </p>
    </div>
@endsection

@section('content')
    @php
        $canViewProducts = auth()->user()?->can('viewAny', \App\Models\Product::class);
        $canViewSuppliers = auth()->user()?->can('viewAny', \App\Models\Supplier::class);
        $canViewRestocks = auth()->user()?->can('viewAny', \App\Models\RestockOrder::class);
        $canViewSupplierRestocks = auth()->user()?->can('viewSupplierRestocks', \App\Models\RestockOrder::class);
        $canViewSales = auth()->user()?->can('viewAny', \App\Models\OutgoingTransaction::class);
        $canCreatePurchases = auth()->user()?->can('create', \App\Models\IncomingTransaction::class);
        $canCreateSales = auth()->user()?->can('create', \App\Models\OutgoingTransaction::class);
        $canViewReports = auth()->user()?->can('view-transactions-report');
    @endphp

    <div class="space-y-6">
        {{-- Common stats for every role --}}
        @if(
            $canViewProducts
            || $canViewSuppliers
            || $canViewRestocks
            || $canViewSupplierRestocks
            || $canViewSales
        )
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3 text-sm">
                @if($canViewProducts)
                    <div class="rounded-xl border border-slate-200 bg-white px-3 py-3 flex flex-col gap-1">
                        <span class="text-xs text-slate-500">Products</span>
                        <span class="text-2xl font-semibold text-slate-900">
                            {{ number_format($commonStats['products'] ?? 0) }}
                        </span>
                    </div>
                @endif

                @if($canViewSuppliers)
                    <div class="rounded-xl border border-slate-200 bg-white px-3 py-3 flex flex-col gap-1">
                        <span class="text-xs text-slate-500">Suppliers</span>
                        <span class="text-2xl font-semibold text-slate-900">
                            {{ number_format($commonStats['suppliers'] ?? 0) }}
                        </span>
                    </div>
                @endif

                @if($canViewProducts)
                    <div class="rounded-xl border border-slate-200 bg-white px-3 py-3 flex flex-col gap-1">
                        <span class="text-xs text-slate-500">Low stock items</span>
                        <span class="text-2xl font-semibold text-amber-700">
                            {{ number_format($commonStats['lowStock'] ?? 0) }}
                        </span>
                    </div>
                @endif

                @if($canViewRestocks || $canViewSupplierRestocks)
                    <div class="rounded-xl border border-slate-200 bg-white px-3 py-3 flex flex-col gap-1">
                        <span class="text-xs text-slate-500">Open restocks</span>
                        <span class="text-2xl font-semibold text-slate-900">
                            {{ number_format($commonStats['openRestocks'] ?? 0) }}
                        </span>
                    </div>
                @endif

                @if($canViewSales)
                    <div class="rounded-xl border border-slate-200 bg-white px-3 py-3 flex flex-col gap-1">
                        <span class="text-xs text-slate-500">Pending sales</span>
                        <span class="text-2xl font-semibold text-slate-900">
                            {{ number_format($commonStats['pendingSales'] ?? 0) }}
                        </span>
                    </div>
                @endif
            </div>
        @endif

        {{-- Ability-specific sections --}}
        @if($adminData && $canViewReports)
            @include('dashboard._admin', $adminData)
        @endif

        @if($managerData && ($canViewProducts || $canViewRestocks || $canViewSales))
            @include('dashboard._manager', $managerData)
        @endif

        @if($staffData && ($canCreatePurchases || $canCreateSales))
            @include('dashboard._staff', $staffData)
        @endif

        @if($supplierData && $canViewSupplierRestocks)
            @include('dashboard._supplier', $supplierData)
        @endif

        @if(! $adminData && ! $managerData && ! $staffData && ! $supplierData)
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                Tidak ada widget dashboard yang tersedia untuk akun ini. Hubungi administrator untuk mendapatkan akses modul yang diperlukan.
            </div>
        @endif
    </div>
@endsection
