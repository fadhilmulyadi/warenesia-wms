@extends('layouts.app')

@section('title', 'Outgoing Transactions')

@section('page-header')
    <div class="flex flex-col">
        <h1 class="text-base font-semibold text-slate-900">Outgoing transactions</h1>
        <p class="text-xs text-slate-500">
            Daftar barang keluar dari gudang ke customer.
        </p>
    </div>

    <div class="flex items-center gap-2">
        @can('create', \App\Models\OutgoingTransaction::class)
            <a
                href="{{ route('sales.create') }}"
                class="inline-flex items-center rounded-lg bg-teal-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-teal-600"
            >
                <x-lucide-plus class="h-3 w-3 mr-1" />
                New outgoing transaction
            </a>
        @endcan
    </div>
@endsection

@section('content')
    <div class="space-y-4 text-xs max-w-6xl mx-auto">
        <form method="GET" class="rounded-2xl border border-slate-200 bg-white p-3 flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[200px]">
                <input
                    type="text"
                    name="q"
                    value="{{ $search }}"
                    class="w-full rounded-lg border border-slate-200 px-3 py-1.5 text-[11px]"
                    placeholder="Search by transaction number or customer name"
                >
            </div>

            <div>
                <select
                    name="status"
                    class="rounded-lg border border-slate-200 px-3 py-1.5 text-[11px]"
                >
                    <option value="">All status</option>
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected($statusFilter === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <button
                type="submit"
                class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-[11px] text-slate-700 hover:bg-slate-50"
            >
                Filter
            </button>
            @can('export', \App\Models\OutgoingTransaction::class)
                <a
                    href="{{ route('sales.export', request()->query()) }}"
                    class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-[11px] text-slate-700 hover:bg-slate-50"
                >
                    <x-lucide-download class="h-3 w-3 mr-1" />
                    Export CSV
                </a>
            @endcan
        </form>

        <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
            <table class="min-w-full text-left text-xs">
                <thead class="bg-slate-50 text-[11px] text-slate-500 uppercase tracking-wide">
                    <tr>
                        <th class="px-4 py-2 w-40">Transaction #</th>
                        <th class="px-4 py-2 w-32">Date</th>
                        <th class="px-4 py-2">Customer</th>
                        <th class="px-4 py-2 text-center w-24">Status</th>
                        <th class="px-4 py-2 text-right w-24">Qty</th>
                        <th class="px-4 py-2 text-right w-32">Total (Rp)</th>
                        @can('viewAny', \App\Models\OutgoingTransaction::class)
                            <th class="px-4 py-2 text-right w-24"></th>
                        @endcan
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $transaction)
                        <tr>
                            <td class="px-4 py-2 align-top">
                                <div class="font-mono text-[11px] text-slate-800">
                                    {{ $transaction->transaction_number }}
                                </div>
                            </td>
                            <td class="px-4 py-2 align-top text-[11px] text-slate-600">
                                {{ $transaction->transaction_date->format('d M Y') }}
                            </td>
                            <td class="px-4 py-2 align-top">
                                <div class="text-[11px] text-slate-800">
                                    {{ $transaction->customer_name }}
                                </div>
                            </td>
                            <td class="px-4 py-2 align-top text-center">
                                @if($transaction->isPending())
                                    <span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-700">
                                        Pending
                                    </span>
                                @elseif($transaction->isApproved())
                                    <span class="inline-flex items-center rounded-full bg-sky-50 px-2 py-0.5 text-[10px] font-semibold text-sky-700">
                                        Approved
                                    </span>
                                @elseif($transaction->isShipped())
                                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">
                                        Shipped
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-2 align-top text-right text-[11px] text-slate-700">
                                {{ number_format($transaction->total_quantity, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-2 align-top text-right text-[11px] text-slate-700">
                                {{ number_format($transaction->total_amount, 2, ',', '.') }}
                            </td>
                            @can('viewAny', \App\Models\OutgoingTransaction::class)
                                <td class="px-4 py-2 align-top text-right">
                                    @can('view', $transaction)
                                        <a
                                            href="{{ route('sales.show', $transaction) }}"
                                            class="inline-flex items-center rounded-lg border border-slate-200 px-2 py-1 text-[11px] text-slate-700 hover:bg-slate-50"
                                        >
                                            View
                                        </a>
                                    @endcan
                                </td>
                            @endcan
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-[11px] text-slate-500">
                                No outgoing transactions yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if($transactions->hasPages())
                <div class="border-t border-slate-100 px-4 py-2">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
