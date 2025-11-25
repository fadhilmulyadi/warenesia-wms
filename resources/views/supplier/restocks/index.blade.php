@extends('layouts.app')

@section('title', 'My Restock Orders')

@section('page-header')
    <div class="flex flex-col">
        <h1 class="text-base font-semibold text-slate-900">My restock orders</h1>
        <p class="text-xs text-slate-500">
            Monitor purchase orders assigned to your company.
        </p>
    </div>
@endsection

@section('content')
    <div class="space-y-4 text-xs max-w-5xl mx-auto">
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

        <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
            <table class="min-w-full text-left text-xs">
                <thead class="bg-slate-50 text-[11px] text-slate-500 uppercase tracking-wide">
                    <tr>
                        <th class="px-4 py-2 w-40">PO #</th>
                        <th class="px-4 py-2 w-32">Order date</th>
                        <th class="px-4 py-2 w-32">Expected date</th>
                        <th class="px-4 py-2 text-right w-28">Total qty</th>
                        <th class="px-4 py-2 text-right w-32">Total (Rp)</th>
                        <th class="px-4 py-2 text-center w-28">Status</th>
                        @can('viewSupplierRestocks', \App\Models\RestockOrder::class)
                            <th class="px-4 py-2 text-right w-24"></th>
                        @endcan
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($restockOrders as $restockOrder)
                        <tr>
                            <td class="px-4 py-2 align-top">
                                <div class="font-mono text-[11px] text-slate-800">
                                    <a
                                        href="{{ route('supplier.restocks.show', $restockOrder) }}"
                                        class="hover:underline"
                                    >
                                        {{ $restockOrder->po_number }}
                                    </a>
                                </div>
                            </td>
                            <td class="px-4 py-2 align-top text-[11px] text-slate-600">
                                {{ $restockOrder->order_date?->format('d M Y') ?? '-' }}
                            </td>
                            <td class="px-4 py-2 align-top text-[11px] text-slate-600">
                                {{ $restockOrder->expected_delivery_date?->format('d M Y') ?? '-' }}
                            </td>
                            <td class="px-4 py-2 align-top text-right text-[11px] text-slate-700">
                                {{ number_format($restockOrder->total_quantity, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-2 align-top text-right text-[11px] text-slate-700">
                                {{ number_format($restockOrder->total_amount, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-2 align-top text-center">
                                @include('components.status-badge', [
                                    'status' => $restockOrder->status,
                                    'label' => $restockOrder->status_label,
                                ])
                            </td>
                            @can('viewSupplierRestocks', \App\Models\RestockOrder::class)
                                <td class="px-4 py-2 align-top text-right">
                                    <a
                                        href="{{ route('supplier.restocks.show', $restockOrder) }}"
                                        class="inline-flex items-center rounded-lg border border-slate-200 px-2 py-1 text-[11px] text-slate-700 hover:bg-slate-50"
                                    >
                                        View
                                    </a>
                                </td>
                            @endcan
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-[11px] text-slate-500">
                                No restock orders assigned to you.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if($restockOrders->hasPages())
                <div class="border-t border-slate-100 px-4 py-2">
                    {{ $restockOrders->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
