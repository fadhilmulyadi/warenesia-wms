@extends('layouts.app')

@section('title', 'Staff Dashboard')

@section('page-header')
    <div class="flex flex-col">
        <h1 class="text-base font-semibold text-slate-900">Staff dashboard</h1>
        <p class="text-xs text-slate-500">
            Your transactions for {{ \Carbon\Carbon::parse($today)->format('d M Y') }}.
        </p>
    </div>
@endsection

@section('content')
    @php
        $statusColors = [
            \App\Models\IncomingTransaction::STATUS_PENDING => 'bg-amber-50 text-amber-700 border-amber-200',
            \App\Models\IncomingTransaction::STATUS_VERIFIED => 'bg-sky-50 text-sky-700 border-sky-200',
            \App\Models\IncomingTransaction::STATUS_COMPLETED => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            \App\Models\IncomingTransaction::STATUS_REJECTED => 'bg-rose-50 text-rose-700 border-rose-200',
            \App\Models\OutgoingTransaction::STATUS_PENDING => 'bg-amber-50 text-amber-700 border-amber-200',
            \App\Models\OutgoingTransaction::STATUS_APPROVED => 'bg-sky-50 text-sky-700 border-sky-200',
            \App\Models\OutgoingTransaction::STATUS_SHIPPED => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        ];
    @endphp

    <div class="space-y-4 text-xs max-w-6xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-[11px] font-semibold text-slate-800 uppercase tracking-wide">
                        Incoming transactions today
                    </h2>
                    <span class="text-[11px] text-slate-500">
                        Total: {{ $incomingToday->count() }}
                    </span>
                </div>

                <div class="rounded-xl border border-slate-200 overflow-hidden">
                    <table class="min-w-full text-left text-xs">
                        <thead class="bg-slate-50 text-[11px] text-slate-500 uppercase tracking-wide">
                            <tr>
                                <th class="px-3 py-2">PO #</th>
                                <th class="px-3 py-2">Supplier</th>
                                <th class="px-3 py-2 w-24 text-right">Amount (Rp)</th>
                                <th class="px-3 py-2 w-28 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($incomingToday as $purchase)
                                <tr>
                                    <td class="px-3 py-2 font-mono text-[11px] text-slate-800">
                                        {{ $purchase->transaction_number }}
                                    </td>
                                    <td class="px-3 py-2 text-[11px] text-slate-800">
                                        {{ $purchase->supplier->name ?? '-' }}
                                    </td>
                                    <td class="px-3 py-2 text-right text-[12px] text-slate-900">
                                        {{ number_format((float) $purchase->total_amount, 2, ',', '.') }}
                                    </td>
                                    @php
                                        $badgeClass = $statusColors[$purchase->status] ?? 'bg-slate-50 text-slate-700 border-slate-200';
                                        $statusLabel = ucfirst(str_replace('_', ' ', $purchase->status));
                                    @endphp
                                    <td class="px-3 py-2 text-center">
                                        <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-[10px] font-semibold {{ $badgeClass }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-3 py-4 text-center text-[11px] text-slate-500">
                                        No incoming transactions created today.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-[11px] font-semibold text-slate-800 uppercase tracking-wide">
                        Outgoing transactions today
                    </h2>
                    <span class="text-[11px] text-slate-500">
                        Total: {{ $outgoingToday->count() }}
                    </span>
                </div>

                <div class="rounded-xl border border-slate-200 overflow-hidden">
                    <table class="min-w-full text-left text-xs">
                        <thead class="bg-slate-50 text-[11px] text-slate-500 uppercase tracking-wide">
                            <tr>
                                <th class="px-3 py-2">SO #</th>
                                <th class="px-3 py-2">Customer</th>
                                <th class="px-3 py-2 w-24 text-right">Amount (Rp)</th>
                                <th class="px-3 py-2 w-28 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($outgoingToday as $sale)
                                @php
                                    $badgeClass = $statusColors[$sale->status] ?? 'bg-slate-50 text-slate-700 border-slate-200';
                                    $statusLabel = ucfirst(str_replace('_', ' ', $sale->status));
                                @endphp
                                <tr>
                                    <td class="px-3 py-2 font-mono text-[11px] text-slate-800">
                                        {{ $sale->transaction_number }}
                                    </td>
                                    <td class="px-3 py-2 text-[11px] text-slate-800">
                                        {{ $sale->customer_name ?? '-' }}
                                    </td>
                                    <td class="px-3 py-2 text-right text-[12px] text-slate-900">
                                        {{ number_format((float) $sale->total_amount, 2, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-[10px] font-semibold {{ $badgeClass }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-3 py-4 text-center text-[11px] text-slate-500">
                                        No outgoing transactions created today.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a
                href="{{ route('admin.purchases.create') }}"
                class="inline-flex items-center rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-800"
            >
                Create purchase
            </a>
            <a
                href="{{ route('admin.sales.create') }}"
                class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50"
            >
                Create sale
            </a>
        </div>
    </div>
@endsection
