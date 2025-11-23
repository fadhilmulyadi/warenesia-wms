@extends('layouts.app')

@section('title', 'Incoming Transaction ' . $purchase->transaction_number)

@section('page-header')
    <div class="flex flex-col">
        <h1 class="text-base font-semibold text-slate-900">
            Incoming transaction {{ $purchase->transaction_number }}
        </h1>
        <p class="text-xs text-slate-500">
            Supplier {{ $purchase->supplier->name }} &mdash;
            {{ $purchase->transaction_date->format('d M Y') }}
        </p>
    </div>

    <div class="flex items-center gap-2">
        <a
            href="{{ route('purchases.index') }}"
            class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50"
        >
            Back to list
        </a>
    </div>
@endsection

@section('content')
    <div class="max-w-5xl mx-auto space-y-4 text-xs">
        @if(session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @canany(['verify', 'reject', 'complete'], $purchase)
            <div class="rounded-2xl border border-slate-200 bg-white p-3 mb-2 flex flex-wrap items-center justify-between gap-2 text-xs">
                <div class="text-[11px] text-slate-600">
                    Manage status
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    @can('verify', $purchase)
                        <form
                            method="POST"
                            action="{{ route('purchases.verify', $purchase) }}"
                            onsubmit="return confirm('Verify this transaction and update stock?');"
                        >
                            @csrf
                            @method('PATCH')
                            <button
                                type="submit"
                                class="inline-flex items-center rounded-lg bg-emerald-500 px-3 py-1.5 text-[11px] font-semibold text-white hover:bg-emerald-600"
                            >
                                Verify & update stock
                            </button>
                        </form>
                    @endcan

                    @can('reject', $purchase)
                        <form
                            method="POST"
                            action="{{ route('purchases.reject', $purchase) }}"
                            onsubmit="return confirm('Reject this transaction?');"
                            class="flex items-center gap-1"
                        >
                            @csrf
                            @method('PATCH')
                            <input
                                type="text"
                                name="reason"
                                class="rounded-lg border border-slate-200 px-2 py-1 text-[11px] w-40"
                                placeholder="Reason (optional)"
                            >
                            <button
                                type="submit"
                                class="inline-flex items-center rounded-lg border border-red-200 px-3 py-1.5 text-[11px] font-semibold text-red-600 hover:bg-red-50"
                            >
                                Reject
                            </button>
                        </form>
                    @endcan

                    @can('complete', $purchase)
                        <form
                            method="POST"
                            action="{{ route('purchases.complete', $purchase) }}"
                            onsubmit="return confirm('Mark this transaction as completed?');"
                        >
                            @csrf
                            @method('PATCH')
                            <button
                                type="submit"
                                class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-[11px] font-semibold text-slate-800 hover:bg-slate-50"
                            >
                                Mark as completed
                            </button>
                        </form>
                    @endcan
                </div>
            </div>
        @endcanany

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 space-y-2">
                <div class="text-[11px] text-slate-500 uppercase tracking-wide">Transaction info</div>
                <div class="space-y-1">
                    <div>
                        <span class="text-[11px] text-slate-500">Number</span>
                        <div class="font-mono text-[11px] text-slate-900">
                            {{ $purchase->transaction_number }}
                        </div>
                    </div>
                    <div>
                        <span class="text-[11px] text-slate-500">Date</span>
                        <div class="text-[11px] text-slate-900">
                            {{ $purchase->transaction_date->format('d M Y') }}
                        </div>
                    </div>

                    <div>
                        <span class="text-[11px] text-slate-500">Status</span>
                        <div class="mt-0.5">
                            @if($purchase->isPending())
                                <span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-700">
                                    Pending
                                </span>
                            @elseif($purchase->isVerified())
                                <span class="inline-flex items-center rounded-full bg-sky-50 px-2 py-0.5 text-[10px] font-semibold text-sky-700">
                                    Verified
                                </span>
                            @elseif($purchase->isCompleted())
                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">
                                    Completed
                                </span>
                            @elseif($purchase->isRejected())
                                <span class="inline-flex items-center rounded-full bg-red-50 px-2 py-0.5 text-[10px] font-semibold text-red-700">
                                    Rejected
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Supplier card --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-4 space-y-2">
                <div class="text-[11px] text-slate-500 uppercase tracking-wide">Supplier</div>
                <div class="space-y-1">
                    <div class="text-[11px] font-semibold text-slate-900">
                        {{ $purchase->supplier->name }}
                    </div>
                    @if($purchase->supplier->contact_person)
                        <div class="text-[11px] text-slate-700">
                            Contact: {{ $purchase->supplier->contact_person }}
                        </div>
                    @endif
                    @if($purchase->supplier->email)
                        <div class="text-[11px] text-slate-700">
                            Email: {{ $purchase->supplier->email }}
                        </div>
                    @endif
                    @if($purchase->supplier->phone)
                        <div class="text-[11px] text-slate-700">
                            Phone: {{ $purchase->supplier->phone }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- Summary card --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-4 space-y-2">
                <div class="text-[11px] text-slate-500 uppercase tracking-wide">Summary</div>
                <div class="space-y-1">
                    <div class="flex justify-between">
                        <span class="text-[11px] text-slate-500">Total items</span>
                        <span class="text-[11px] font-semibold text-slate-900">
                            {{ $purchase->total_items }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[11px] text-slate-500">Total quantity</span>
                        <span class="text-[11px] font-semibold text-slate-900">
                            {{ $purchase->total_quantity }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[11px] text-slate-500">Total amount</span>
                        <span class="text-[11px] font-semibold text-slate-900">
                            Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Products list --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-4">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-[11px] font-semibold text-slate-800 uppercase tracking-wide">
                    Products
                </h2>
            </div>

            <div class="rounded-xl border border-slate-100 overflow-hidden">
                <table class="min-w-full text-left text-xs">
                    <thead class="bg-slate-50 text-[11px] text-slate-500 uppercase tracking-wide">
                        <tr>
                            <th class="px-3 py-2">Product</th>
                            <th class="px-3 py-2 text-right">Qty</th>
                            <th class="px-3 py-2 text-right">Unit cost (Rp)</th>
                            <th class="px-3 py-2 text-right">Line total (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($purchase->items as $item)
                            <tr>
                                <td class="px-3 py-2">
                                    <div class="flex flex-col">
                                        <span class="text-[11px] font-semibold text-slate-900">
                                            {{ $item->product->name }}
                                        </span>
                                        <span class="text-[10px] text-slate-500">
                                            SKU: {{ $item->product->sku }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-3 py-2 text-right">
                                    {{ $item->quantity }}
                                </td>
                                <td class="px-3 py-2 text-right">
                                    {{ number_format($item->unit_cost, 0, ',', '.') }}
                                </td>
                                <td class="px-3 py-2 text-right">
                                    {{ number_format($item->line_total, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($purchase->notes)
                <div class="mt-4">
                    <div class="text-[11px] text-slate-500 mb-1">Notes</div>
                    <div class="rounded-lg border border-slate-100 bg-slate-50 px-3 py-2 text-[11px] text-slate-800">
                        {{ $purchase->notes }}
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
