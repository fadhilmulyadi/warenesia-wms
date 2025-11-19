@extends('layouts.app')

@section('title', 'Outgoing Transaction Detail')

@section('page-header')
    <div class="flex flex-col">
        <h1 class="text-base font-semibold text-slate-900">
            Outgoing transaction
        </h1>
        <p class="text-xs text-slate-500">
            Detail barang keluar ke customer.
        </p>
    </div>

    <div class="flex items-center gap-2">
        <a
            href="{{ route('admin.sales.index') }}"
            class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50"
        >
            Back to list
        </a>
    </div>
@endsection

@section('content')
    @php
        /** @var \App\Models\User|null $currentUser */
        $currentUser = auth()->user();
        $currentUserRole = $currentUser->role ?? null;
        $canManageStatus = in_array($currentUserRole, ['admin', 'manager'], true);
    @endphp

    <div class="max-w-5xl mx-auto space-y-4 text-xs">
        @if(session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-red-700">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $errorMessage)
                        <li>{{ $errorMessage }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($canManageStatus)
            <div class="rounded-2xl border border-slate-200 bg-white p-3 mb-2 flex flex-wrap items-center justify-between gap-2 text-xs">
                <div class="text-[11px] text-slate-600">
                    Manage status
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    @if($sale->canBeApproved())
                        <form
                            method="POST"
                            action="{{ route('admin.sales.approve', $sale) }}"
                            onsubmit="return confirm('Approve this transaction and reduce stock?');"
                        >
                            @csrf
                            @method('PATCH')
                            <button
                                type="submit"
                                class="inline-flex items-center rounded-lg bg-emerald-500 px-3 py-1.5 text-[11px] font-semibold text-white hover:bg-emerald-600"
                            >
                                Approve & reduce stock
                            </button>
                        </form>
                    @elseif($sale->canBeShipped())
                        <form
                            method="POST"
                            action="{{ route('admin.sales.ship', $sale) }}"
                            onsubmit="return confirm('Mark this transaction as shipped?');"
                        >
                            @csrf
                            @method('PATCH')
                            <button
                                type="submit"
                                class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-[11px] font-semibold text-slate-800 hover:bg-slate-50"
                            >
                                Mark as shipped
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endif

        <div class="rounded-2xl border border-slate-200 bg-white p-4 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <div class="text-[11px] text-slate-500">Transaction #</div>
                    <div class="font-mono text-[12px] text-slate-900">
                        {{ $sale->transaction_number }}
                    </div>

                    <div class="mt-2 text-[11px] text-slate-500">Date</div>
                    <div class="text-[12px] text-slate-900">
                        {{ $sale->transaction_date->format('d M Y') }}
                    </div>

                    <div class="mt-2 text-[11px] text-slate-500">Status</div>
                    <div>
                        @if($sale->isPending())
                            <span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-700">
                                Pending
                            </span>
                        @elseif($sale->isApproved())
                            <span class="inline-flex items-center rounded-full bg-sky-50 px-2 py-0.5 text-[10px] font-semibold text-sky-700">
                                Approved
                            </span>
                        @elseif($sale->isShipped())
                            <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">
                                Shipped
                            </span>
                        @endif
                    </div>
                </div>

                <div class="space-y-1">
                    <div class="text-[11px] text-slate-500">Customer</div>
                    <div class="text-[12px] text-slate-900">
                        {{ $sale->customer_name }}
                    </div>

                    <div class="mt-2 text-[11px] text-slate-500">Created by</div>
                    <div class="text-[12px] text-slate-900">
                        {{ optional($sale->createdBy)->name ?? '-' }}
                    </div>

                    <div class="mt-2 text-[11px] text-slate-500">Approved by</div>
                    <div class="text-[12px] text-slate-900">
                        {{ optional($sale->approvedBy)->name ?? '-' }}
                    </div>

                    <div class="mt-2 text-[11px] text-slate-500">Totals</div>
                    <div class="text-[12px] text-slate-900">
                        {{ number_format($sale->total_quantity, 0, ',', '.') }} items ·
                        Rp {{ number_format($sale->total_amount, 2, ',', '.') }}
                    </div>
                </div>
            </div>

            @if($sale->notes)
                <div class="pt-2 border-t border-slate-100">
                    <div class="text-[11px] text-slate-500 mb-1">Notes</div>
                    <div class="text-[12px] text-slate-800 whitespace-pre-line">
                        {{ $sale->notes }}
                    </div>
                </div>
            @endif
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-4">
            <h2 class="text-[11px] font-semibold text-slate-800 uppercase tracking-wide mb-2">
                Products
            </h2>

            <div class="rounded-xl border border-slate-200 overflow-hidden">
                <table class="min-w-full text-left text-xs">
                    <thead class="bg-slate-50 text-[11px] text-slate-500 uppercase tracking-wide">
                        <tr>
                            <th class="px-3 py-2">Product</th>
                            <th class="px-3 py-2 text-right w-20">Qty</th>
                            <th class="px-3 py-2 text-right w-32">Unit price (Rp)</th>
                            <th class="px-3 py-2 text-right w-32">Line total (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($sale->items as $item)
                            <tr>
                                <td class="px-3 py-2">
                                    <div class="text-[12px] text-slate-900">
                                        {{ optional($item->product)->name ?? '-' }}
                                    </div>
                                    @if($item->product)
                                        <div class="text-[11px] text-slate-500">
                                            SKU: {{ $item->product->sku }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-right text-[11px] text-slate-800">
                                    {{ number_format($item->quantity, 0, ',', '.') }}
                                </td>
                                <td class="px-3 py-2 text-right text-[11px] text-slate-800">
                                    {{ number_format($item->unit_price, 2, ',', '.') }}
                                </td>
                                <td class="px-3 py-2 text-right text-[11px] text-slate-800">
                                    {{ number_format($item->line_total, 2, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-3 py-4 text-center text-[11px] text-slate-500">
                                    No products in this transaction.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection