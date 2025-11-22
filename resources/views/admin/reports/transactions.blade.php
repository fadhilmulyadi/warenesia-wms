@php
    use App\Models\IncomingTransaction;
    use App\Models\OutgoingTransaction;
    use App\Models\RestockOrder;

    $transactionType = $filters['transaction_type'] ?? 'all';
    $showPurchases = $transactionType === 'all' || $transactionType === 'purchases';
    $showSales = $transactionType === 'all' || $transactionType === 'sales';
    $showRestocks = $transactionType === 'all' || $transactionType === 'restocks';

    $netFlowClass = $netFlow >= 0 ? 'text-emerald-600' : 'text-rose-600';

    $purchaseStatusColors = [
        IncomingTransaction::STATUS_PENDING => 'bg-amber-50 text-amber-700 border-amber-200',
        IncomingTransaction::STATUS_VERIFIED => 'bg-sky-50 text-sky-700 border-sky-200',
        IncomingTransaction::STATUS_COMPLETED => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        IncomingTransaction::STATUS_REJECTED => 'bg-rose-50 text-rose-700 border-rose-200',
    ];

    $purchaseStatusLabels = [
        IncomingTransaction::STATUS_PENDING => 'Pending',
        IncomingTransaction::STATUS_VERIFIED => 'Verified',
        IncomingTransaction::STATUS_COMPLETED => 'Completed',
        IncomingTransaction::STATUS_REJECTED => 'Rejected',
    ];

    $salesStatusColors = [
        OutgoingTransaction::STATUS_PENDING => 'bg-amber-50 text-amber-700 border-amber-200',
        OutgoingTransaction::STATUS_APPROVED => 'bg-sky-50 text-sky-700 border-sky-200',
        OutgoingTransaction::STATUS_SHIPPED => 'bg-emerald-50 text-emerald-700 border-emerald-200',
    ];

    $salesStatusLabels = [
        OutgoingTransaction::STATUS_PENDING => 'Pending',
        OutgoingTransaction::STATUS_APPROVED => 'Approved',
        OutgoingTransaction::STATUS_SHIPPED => 'Shipped',
    ];

    $restockStatusColors = [
        RestockOrder::STATUS_PENDING => 'bg-amber-50 text-amber-700 border-amber-200',
        RestockOrder::STATUS_CONFIRMED => 'bg-sky-50 text-sky-700 border-sky-200',
        RestockOrder::STATUS_IN_TRANSIT => 'bg-indigo-50 text-indigo-700 border-indigo-200',
        RestockOrder::STATUS_RECEIVED => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        RestockOrder::STATUS_CANCELLED => 'bg-rose-50 text-rose-700 border-rose-200',
    ];
@endphp

@extends('layouts.app')

@section('title', 'Transaction reports')

@section('page-header')
    <div class="flex flex-col">
        <h1 class="text-base font-semibold text-slate-900">Transaction overview</h1>
        <p class="text-xs text-slate-500">
            Ringkasan purchases, sales, dan restocks dalam periode yang dipilih.
        </p>
    </div>
@endsection

@section('content')
    <div class="max-w-6xl mx-auto space-y-5 text-xs">
        {{-- Filter bar --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <form method="GET" action="{{ route('admin.reports.transactions') }}" class="grid gap-3 md:grid-cols-4 lg:grid-cols-6 items-end">
                <div class="flex flex-col gap-1">
                    <label for="date_preset" class="text-[11px] text-slate-600 font-semibold">Date preset</label>
                    <select
                        id="date_preset"
                        name="date_preset"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-[11px]"
                    >
                        @php
                            $datePresets = [
                                'today' => 'Today',
                                'yesterday' => 'Yesterday',
                                'last_7_days' => 'Last 7 days',
                                'this_month' => 'This month',
                                'last_month' => 'Last month',
                                'this_year' => 'This year',
                                'custom' => 'Custom range',
                            ];
                        @endphp
                        @foreach($datePresets as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['date_preset'] ?? 'this_month') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col gap-1">
                    <label for="date_from" class="text-[11px] text-slate-600 font-semibold">Date from</label>
                    <input
                        type="date"
                        id="date_from"
                        name="date_from"
                        value="{{ $filters['date_from'] }}"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-[11px]"
                    >
                </div>

                <div class="flex flex-col gap-1">
                    <label for="date_to" class="text-[11px] text-slate-600 font-semibold">Date to</label>
                    <input
                        type="date"
                        id="date_to"
                        name="date_to"
                        value="{{ $filters['date_to'] }}"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-[11px]"
                    >
                </div>

                <div class="flex flex-col gap-1">
                    <label for="transaction_type" class="text-[11px] text-slate-600 font-semibold">Transaction type</label>
                    <select
                        id="transaction_type"
                        name="transaction_type"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-[11px]"
                    >
                        <option value="all" @selected($transactionType === 'all')>All</option>
                        <option value="purchases" @selected($transactionType === 'purchases')>Purchases</option>
                        <option value="sales" @selected($transactionType === 'sales')>Sales</option>
                        <option value="restocks" @selected($transactionType === 'restocks')>Restocks</option>
                    </select>
                </div>

                <div class="flex flex-col gap-1">
                    <label for="status" class="text-[11px] text-slate-600 font-semibold">Status</label>
                    <select
                        id="status"
                        name="status"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-[11px]"
                    >
                        <option value="">All status</option>
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                        @if(($filters['status'] ?? null) && ! array_key_exists($filters['status'], $statusOptions))
                            <option value="{{ $filters['status'] }}" selected>{{ ucfirst($filters['status']) }}</option>
                        @endif
                    </select>
                </div>

                <div class="flex flex-wrap gap-2 md:col-span-2 lg:col-span-2 justify-end">
                    <a
                        href="{{ route('admin.reports.transactions') }}"
                        class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-2 text-[11px] text-slate-700 hover:bg-slate-50"
                    >
                        Reset
                    </a>
                    <button
                        type="submit"
                        class="inline-flex items-center rounded-lg bg-slate-900 px-3 py-2 text-[11px] font-semibold text-white hover:bg-slate-800"
                    >
                        Apply filters
                    </button>
                    <a
                        href="{{ route('admin.reports.transactions.export', request()->query()) }}"
                        class="inline-flex items-center rounded-lg border border-teal-500 px-3 py-2 text-[11px] font-semibold text-teal-700 hover:bg-teal-50"
                    >
                        Export CSV
                    </a>
                </div>
            </form>
        </div>

        {{-- KPI summary --}}
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-[11px] text-slate-500 font-semibold uppercase tracking-wide">Total purchases</p>
                <div class="mt-2 text-2xl font-semibold text-slate-900">
                    Rp {{ number_format($purchaseSummary['total_amount'], 0, ',', '.') }}
                </div>
                <p class="text-[11px] text-slate-500 mt-1">{{ $purchaseSummary['count'] }} transactions</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-[11px] text-slate-500 font-semibold uppercase tracking-wide">Total sales</p>
                <div class="mt-2 text-2xl font-semibold text-slate-900">
                    Rp {{ number_format($salesSummary['total_amount'], 0, ',', '.') }}
                </div>
                <p class="text-[11px] text-slate-500 mt-1">{{ $salesSummary['count'] }} transactions</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-[11px] text-slate-500 font-semibold uppercase tracking-wide">Net flow</p>
                <div class="mt-2 text-2xl font-semibold {{ $netFlowClass }}">
                    Rp {{ number_format($netFlow, 0, ',', '.') }}
                </div>
                <p class="text-[11px] text-slate-500 mt-1">Sales – Purchases</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-[11px] text-slate-500 font-semibold uppercase tracking-wide">Restock orders</p>
                <div class="mt-2 text-2xl font-semibold text-slate-900">
                    {{ $restockSummary['count'] }}
                </div>
                <p class="text-[11px] text-slate-500 mt-1">
                    Total value Rp {{ number_format($restockSummary['total_amount'], 0, ',', '.') }}
                </p>
            </div>
        </div>

        {{-- Tables --}}
        @if($showPurchases)
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-900">Purchases in selected period</h2>
                        <p class="text-[11px] text-slate-500">Latest purchase transactions within the range.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-xs">
                        <thead class="bg-slate-50 text-[11px] text-slate-500 uppercase tracking-wide">
                            <tr>
                                <th class="px-4 py-2 w-32">Date</th>
                                <th class="px-4 py-2 w-40">Transaction #</th>
                                <th class="px-4 py-2">Supplier</th>
                                <th class="px-4 py-2 w-28 text-center">Status</th>
                                <th class="px-4 py-2 w-32 text-right">Total amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($recentPurchases as $purchase)
                                <tr>
                                    <td class="px-4 py-2 align-top text-[11px] text-slate-600">
                                        {{ optional($purchase->transaction_date)->format('d M Y') }}
                                    </td>
                                    <td class="px-4 py-2 align-top font-mono text-[11px] text-slate-800">
                                        {{ $purchase->transaction_number }}
                                    </td>
                                    <td class="px-4 py-2 align-top">
                                        <div class="text-[11px] text-slate-800">
                                            {{ optional($purchase->supplier)->name ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-2 align-top text-center">
                                        @php
                                            $purchaseBadge = $purchaseStatusColors[$purchase->status] ?? 'bg-slate-50 text-slate-700 border-slate-200';
                                            $purchaseLabel = $purchaseStatusLabels[$purchase->status] ?? ucfirst($purchase->status);
                                        @endphp
                                        <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-[10px] font-semibold {{ $purchaseBadge }}">
                                            {{ $purchaseLabel }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 align-top text-right text-[11px] text-slate-800">
                                        {{ number_format($purchase->total_amount, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-[11px] text-slate-500">
                                        No purchase transactions in this range.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if($showSales)
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-900">Sales in selected period</h2>
                        <p class="text-[11px] text-slate-500">Latest outgoing transactions for the selected period.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-xs">
                        <thead class="bg-slate-50 text-[11px] text-slate-500 uppercase tracking-wide">
                            <tr>
                                <th class="px-4 py-2 w-32">Date</th>
                                <th class="px-4 py-2 w-40">Transaction #</th>
                                <th class="px-4 py-2">Customer</th>
                                <th class="px-4 py-2 w-28 text-center">Status</th>
                                <th class="px-4 py-2 w-32 text-right">Total amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($recentSales as $sale)
                                <tr>
                                    <td class="px-4 py-2 align-top text-[11px] text-slate-600">
                                        {{ optional($sale->transaction_date)->format('d M Y') }}
                                    </td>
                                    <td class="px-4 py-2 align-top font-mono text-[11px] text-slate-800">
                                        {{ $sale->transaction_number }}
                                    </td>
                                    <td class="px-4 py-2 align-top">
                                        <div class="text-[11px] text-slate-800">
                                            {{ $sale->customer_name }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-2 align-top text-center">
                                        @php
                                            $salesBadge = $salesStatusColors[$sale->status] ?? 'bg-slate-50 text-slate-700 border-slate-200';
                                            $salesLabel = $salesStatusLabels[$sale->status] ?? ucfirst($sale->status);
                                        @endphp
                                        <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-[10px] font-semibold {{ $salesBadge }}">
                                            {{ $salesLabel }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 align-top text-right text-[11px] text-slate-800">
                                        {{ number_format($sale->total_amount, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-[11px] text-slate-500">
                                        No sales in this range.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if($showRestocks)
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-900">Restock orders in selected period</h2>
                        <p class="text-[11px] text-slate-500">Latest purchase orders to suppliers within range.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-xs">
                        <thead class="bg-slate-50 text-[11px] text-slate-500 uppercase tracking-wide">
                            <tr>
                                <th class="px-4 py-2 w-32">Order date</th>
                                <th class="px-4 py-2 w-40">PO number</th>
                                <th class="px-4 py-2">Supplier</th>
                                <th class="px-4 py-2 w-28 text-center">Status</th>
                                <th class="px-4 py-2 w-32 text-right">Total amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($recentRestocks as $restock)
                                <tr>
                                    <td class="px-4 py-2 align-top text-[11px] text-slate-600">
                                        {{ optional($restock->order_date)->format('d M Y') }}
                                    </td>
                                    <td class="px-4 py-2 align-top font-mono text-[11px] text-slate-800">
                                        {{ $restock->po_number }}
                                    </td>
                                    <td class="px-4 py-2 align-top">
                                        <div class="text-[11px] text-slate-800">
                                            {{ optional($restock->supplier)->name ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-2 align-top text-center">
                                        @php
                                            $restockBadge = $restockStatusColors[$restock->status] ?? 'bg-slate-50 text-slate-700 border-slate-200';
                                        @endphp
                                        <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-[10px] font-semibold {{ $restockBadge }}">
                                            {{ $restock->status_label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 align-top text-right text-[11px] text-slate-800">
                                        {{ number_format($restock->total_amount, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-[11px] text-slate-500">
                                        No restock orders in this range.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
