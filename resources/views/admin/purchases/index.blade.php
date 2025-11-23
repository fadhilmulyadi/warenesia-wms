@extends('layouts.app')

@section('title', 'Purchases')

@section('page-header')
    <div class="flex flex-col">
        <h1 class="text-base font-semibold text-slate-900">Incoming transactions</h1>
        <p class="text-xs text-slate-500">
            Barang masuk dari supplier, menunggu verifikasi manager.
        </p>
    </div>

    <div class="flex items-center gap-2">
        <a
            href="{{ route('admin.purchases.create') }}"
            class="inline-flex items-center rounded-lg bg-teal-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-teal-600"
        >
            <x-lucide-plus class="h-3 w-3 mr-1" />
            New incoming transaction
        </a>
    </div>
@endsection

@section('content')
    <div class="max-w-6xl mx-auto space-y-4 text-xs">
        @if(session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->has('general'))
            <div class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-red-700">
                {{ $errors->first('general') }}
            </div>
        @endif

        <form method="GET" action="{{ route('admin.purchases.index') }}" class="flex flex-wrap items-center gap-2">
            <input
                type="text"
                name="q"
                value="{{ $search }}"
                placeholder="Search by transaction number or supplier..."
                class="w-full md:w-64 rounded-lg border border-slate-200 px-3 py-2 text-[11px]"
            >

            <select
                name="status"
                class="w-full md:w-40 rounded-lg border border-slate-200 px-2 py-2 text-[11px]"
            >
                <option value="">All status</option>
                @foreach($statusOptions as $value => $label)
                    <option value="{{ $value }}" @selected($statusFilter === $value)>{{ $label }}</option>
                @endforeach
            </select>

            <button
                type="submit"
                class="rounded-lg border border-slate-200 px-3 py-2 text-[11px] text-slate-700 hover:bg-slate-50"
            >
                Filter
            </button>
            <a
                href="{{ route('admin.purchases.export', request()->query()) }}"
                class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-2 text-[11px] text-slate-700 hover:bg-slate-50"
            >
                <x-lucide-download class="h-3 w-3 mr-1" />
                Export CSV
            </a>
        </form>

        <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
            <table class="min-w-full text-left text-xs">
                <thead class="bg-slate-50 text-[11px] text-slate-500 uppercase tracking-wide">
                    <tr>
                        <th class="px-4 py-2">No.</th>
                        <th class="px-4 py-2">Date</th>
                        <th class="px-4 py-2">Supplier</th>
                        <th class="px-4 py-2 text-right">Items</th>
                        <th class="px-4 py-2 text-right">Qty</th>
                        <th class="px-4 py-2 text-right">Total (Rp)</th>
                        <th class="px-4 py-2 text-center">Status</th>
                        <th class="px-4 py-2 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $transaction)
                        <tr>
                            <td class="px-4 py-2 font-mono text-[11px]">
                                {{ $transaction->transaction_number }}
                            </td>
                            <td class="px-4 py-2">
                                {{ $transaction->transaction_date->format('d M Y') }}
                            </td>
                            <td class="px-4 py-2">
                                {{ $transaction->supplier->name }}
                                <div class="text-[10px] text-slate-500">
                                    By {{ $transaction->createdBy->name }}
                                </div>
                            </td>
                            <td class="px-4 py-2 text-right">
                                {{ $transaction->total_items }}
                            </td>
                            <td class="px-4 py-2 text-right">
                                {{ $transaction->total_quantity }}
                            </td>
                            <td class="px-4 py-2 text-right">
                                {{ number_format($transaction->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-2 text-center">
                                @if($transaction->isPending())
                                    <span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-700">
                                        Pending
                                    </span>
                                @elseif($transaction->isVerified())
                                    <span class="inline-flex items-center rounded-full bg-sky-50 px-2 py-0.5 text-[10px] font-semibold text-sky-700">
                                        Verified
                                    </span>
                                @elseif($transaction->isCompleted())
                                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">
                                        Completed
                                    </span>
                                @elseif($transaction->isRejected())
                                    <span class="inline-flex items-center rounded-full bg-red-50 px-2 py-0.5 text-[10px] font-semibold text-red-700">
                                        Rejected
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-right">
                                <a
                                    href="{{ route('admin.purchases.show', $transaction) }}"
                                    class="rounded-lg border border-slate-200 px-2 py-1 text-[11px] text-slate-700 hover:bg-slate-50"
                                >
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-6 text-center text-[11px] text-slate-500">
                                No incoming transactions found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if($transactions->hasPages())
                <div class="border-top border-slate-100 px-4 py-2 flex items-center justify-between text-[11px] text-slate-500">
                    <div>
                        Showing
                        <span class="font-semibold text-slate-700">{{ $transactions->firstItem() }}</span>
                        to
                        <span class="font-semibold text-slate-700">{{ $transactions->lastItem() }}</span>
                        of
                        <span class="font-semibold text-slate-700">{{ $transactions->total() }}</span>
                        transactions
                    </div>
                    <div>
                        {{ $transactions->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
