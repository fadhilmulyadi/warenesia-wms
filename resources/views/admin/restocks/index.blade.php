@extends('layouts.app')

@section('title', 'Restock Orders')

@section('page-header')
    <div class="flex flex-col">
        <h1 class="text-base font-semibold text-slate-900">Restock orders</h1>
        <p class="text-xs text-slate-500">
            Daftar permintaan restock ke supplier.
        </p>
    </div>

    <div class="flex items-center gap-2">
        <a
            href="{{ route('admin.restocks.create') }}"
            class="inline-flex items-center rounded-lg bg-teal-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-teal-600"
        >
            <x-lucide-plus class="h-3 w-3 mr-1" />
            New restock order
        </a>
    </div>
@endsection

@section('content')
    <div class="space-y-4 text-xs max-w-6xl mx-auto">
        {{-- Filters --}}
        <form
            method="GET"
            class="rounded-2xl border border-slate-200 bg-white p-3 flex flex-wrap items-center gap-3"
        >
            <div class="flex-1 min-w-[180px]">
                <input
                    type="text"
                    name="q"
                    value="{{ $search }}"
                    class="w-full rounded-lg border border-slate-200 px-3 py-1.5 text-[11px]"
                    placeholder="Search by PO number"
                >
            </div>

            <div>
                <select
                    name="supplier_id"
                    class="rounded-lg border border-slate-200 px-3 py-1.5 text-[11px]"
                >
                    <option value="0">All suppliers</option>
                    @foreach($suppliers as $supplier)
                        <option
                            value="{{ $supplier->id }}"
                            @selected($supplierFilter === $supplier->id)
                        >
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <select
                    name="status"
                    class="rounded-lg border border-slate-200 px-3 py-1.5 text-[11px]"
                >
                    <option value="">All status</option>
                    @foreach($statusOptions as $value => $label)
                        <option
                            value="{{ $value }}"
                            @selected($statusFilter === $value)
                        >
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button
                type="submit"
                class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-[11px] text-slate-700 hover:bg-slate-50"
            >
                Filter
            </button>
        </form>

        {{-- Table --}}
        <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
            <table class="min-w-full text-left text-xs">
                <thead class="bg-slate-50 text-[11px] text-slate-500 uppercase tracking-wide">
                    <tr>
                        <th class="px-4 py-2 w-40">PO #</th>
                        <th class="px-4 py-2 w-32">Order date</th>
                        <th class="px-4 py-2 w-32">Expected date</th>
                        <th class="px-4 py-2">Supplier</th>
                        <th class="px-4 py-2 text-center w-24">Status</th>
                        <th class="px-4 py-2 text-right w-24">Qty</th>
                        <th class="px-4 py-2 text-right w-32">Total (Rp)</th>
                        <th class="px-4 py-2 text-right w-24"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($restockOrders as $restockOrder)
                        <tr>
                            <td class="px-4 py-2 align-top">
                                <div class="font-mono text-[11px] text-slate-800">
                                    {{ $restockOrder->po_number }}
                                </div>
                            </td>
                            <td class="px-4 py-2 align-top text-[11px] text-slate-600">
                                {{ $restockOrder->order_date?->format('d M Y') }}
                            </td>
                            <td class="px-4 py-2 align-top text-[11px] text-slate-600">
                                {{ $restockOrder->expected_delivery_date?->format('d M Y') ?? '–' }}
                            </td>
                            <td class="px-4 py-2 align-top">
                                <div class="text-[11px] text-slate-800">
                                    {{ optional($restockOrder->supplier)->name ?? '-' }}
                                </div>
                            </td>
                            <td class="px-4 py-2 align-top text-center">
                                @if($restockOrder->isPending())
                                    <span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-700">
                                        Pending
                                    </span>
                                @elseif($restockOrder->isConfirmed())
                                    <span class="inline-flex items-center rounded-full bg-sky-50 px-2 py-0.5 text-[10px] font-semibold text-sky-700">
                                        Confirmed
                                    </span>
                                @elseif($restockOrder->isInTransit())
                                    <span class="inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-[10px] font-semibold text-indigo-700">
                                        In transit
                                    </span>
                                @elseif($restockOrder->isReceived())
                                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">
                                        Received
                                    </span>
                                @elseif($restockOrder->isCancelled())
                                    <span class="inline-flex items-center rounded-full bg-rose-50 px-2 py-0.5 text-[10px] font-semibold text-rose-700">
                                        Cancelled
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-2 align-top text-right text-[11px] text-slate-700">
                                {{ number_format($restockOrder->total_quantity, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-2 align-top text-right text-[11px] text-slate-700">
                                {{ number_format($restockOrder->total_amount, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-2 align-top text-right">
                                <a
                                    href="{{ route('admin.restocks.show', $restockOrder) }}"
                                    class="inline-flex items-center rounded-lg border border-slate-200 px-2 py-1 text-[11px] text-slate-700 hover:bg-slate-50"
                                >
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-6 text-center text-[11px] text-slate-500">
                                No restock orders yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if($restockOrders->hasPages())
                <div class="border-top border-slate-100 px-4 py-2">
                    {{ $restockOrders->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
