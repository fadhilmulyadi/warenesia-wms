@extends('layouts.app')

@section('title', 'Restock Orders')

@section('page-header')
    <div class="flex flex-col">
        <h1 class="text-base font-semibold text-slate-900">Restock orders</h1>
        <p class="text-xs text-slate-500">
            Track purchase orders to suppliers and their latest status.
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
                    placeholder="Search by PO number or supplier"
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
            <a
                href="{{ route('admin.restocks.export', request()->query()) }}"
                class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-[11px] text-slate-700 hover:bg-slate-50"
            >
                <x-lucide-download class="h-3 w-3 mr-1" />
                Export CSV
            </a>
        </form>

        {{-- Table --}}
        <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
            <table class="min-w-full text-left text-xs">
                <thead class="bg-slate-50 text-[11px] text-slate-500 uppercase tracking-wide">
                    <tr>
                        <th class="px-4 py-2 w-40">PO #</th>
                        <th class="px-4 py-2 w-32">Order date</th>
                        <th class="px-4 py-2">Supplier</th>
                        <th class="px-4 py-2 w-32">Expected date</th>
                        <th class="px-4 py-2 text-center w-28">Status</th>
                        <th class="px-4 py-2 text-right w-28">Total qty</th>
                        <th class="px-4 py-2 text-right w-32">Total (Rp)</th>
                        <th class="px-4 py-2 text-right w-20"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($restockOrders as $restockOrder)
                        <tr>
                            <td class="px-4 py-2 align-top">
                                <div class="font-mono text-[11px] text-slate-800">
                                    <a
                                        href="{{ route('admin.restocks.show', $restockOrder) }}"
                                        class="hover:underline"
                                    >
                                        {{ $restockOrder->po_number }}
                                    </a>
                                </div>
                            </td>
                            <td class="px-4 py-2 align-top text-[11px] text-slate-600">
                                {{ $restockOrder->order_date?->format('d M Y') ?? '-' }}
                            </td>
                            <td class="px-4 py-2 align-top">
                                <div class="text-[11px] text-slate-800">
                                    {{ optional($restockOrder->supplier)->name ?? '-' }}
                                </div>
                            </td>
                            <td class="px-4 py-2 align-top text-[11px] text-slate-600">
                                {{ $restockOrder->expected_delivery_date?->format('d M Y') ?? '-' }}
                            </td>
                            <td class="px-4 py-2 align-top text-center">
                                @include('admin.components.status-badge', [
                                    'status' => $restockOrder->status,
                                    'label' => $restockOrder->status_label,
                                ])
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
                <div class="border-t border-slate-100 px-4 py-2">
                    {{ $restockOrders->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
