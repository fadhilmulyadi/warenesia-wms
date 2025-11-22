@extends('layouts.app')

@section('title', 'Restock Order Detail')

@section('page-header')
    <div class="flex flex-col">
        <h1 class="text-base font-semibold text-slate-900">
            Restock order
        </h1>
        <p class="text-xs text-slate-500">
            Detail permintaan restock ke supplier.
        </p>
    </div>

    <div class="flex items-center gap-2">
        <a
            href="{{ route('admin.restocks.index') }}"
            class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50"
        >
            Back to list
        </a>
    </div>
@endsection

@section('content')
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

        {{-- Info utama --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-4 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <div class="text-[11px] text-slate-500">PO number</div>
                    <div class="font-mono text-[12px] text-slate-900">
                        {{ $restockOrder->po_number }}
                    </div>

                    <div class="mt-2 text-[11px] text-slate-500">Order date</div>
                    <div class="text-[12px] text-slate-900">
                        {{ $restockOrder->order_date?->format('d M Y') }}
                    </div>

                    <div class="mt-2 text-[11px] text-slate-500">Expected delivery</div>
                    <div class="text-[12px] text-slate-900">
                        {{ $restockOrder->expected_delivery_date?->format('d M Y') ?? '–' }}
                    </div>

                    <div class="mt-2 text-[11px] text-slate-500">Status</div>
                    <div>
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
                    </div>
                </div>

                <div class="space-y-1">
                    <div class="text-[11px] text-slate-500">Supplier</div>
                    <div class="text-[12px] text-slate-900">
                        {{ optional($restockOrder->supplier)->name ?? '-' }}
                    </div>

                    <div class="mt-2 text-[11px] text-slate-500">Created by</div>
                    <div class="text-[12px] text-slate-900">
                        {{ optional($restockOrder->createdBy)->name ?? '-' }}
                    </div>

                    <div class="mt-2 text-[11px] text-slate-500">Confirmed by</div>
                    <div class="text-[12px] text-slate-900">
                        {{ optional($restockOrder->confirmedBy)->name ?? '-' }}
                    </div>

                    <div class="mt-2 text-[11px] text-slate-500">Totals</div>
                    <div class="text-[12px] text-slate-900">
                        {{ number_format($restockOrder->total_quantity, 0, ',', '.') }} items ·
                        Rp {{ number_format($restockOrder->total_amount, 2, ',', '.') }}
                    </div>
                </div>
            </div>

            @if($restockOrder->notes)
                <div class="pt-2 border-t border-slate-100">
                    <div class="text-[11px] text-slate-500 mb-1">Notes</div>
                    <div class="text-[12px] text-slate-800 whitespace-pre-line">
                        {{ $restockOrder->notes }}
                    </div>
                </div>
            @endif

            {{-- Placeholder info untuk integrasi ke Purchases nanti --}}
            @if($restockOrder->isReceived())
                <div class="mt-3 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-[11px] text-emerald-800">
                    Barang pada restock order ini sudah diterima.
                    Buat incoming transaction untuk memperbarui stok fisik di gudang.
                    {{-- Nanti di 9D bisa ditambah tombol "Create Incoming Transaction" dengan prefill. --}}
                </div>
            @endif
        </div>

        {{-- Items --}}
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
                            <th class="px-3 py-2 text-right w-32">Unit cost (Rp)</th>
                            <th class="px-3 py-2 text-right w-32">Line total (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($restockOrder->items as $item)
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
                                    {{ number_format($item->unit_cost, 2, ',', '.') }}
                                </td>
                                <td class="px-3 py-2 text-right text-[11px] text-slate-800">
                                    {{ number_format($item->line_total, 2, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-3 py-4 text-center text-[11px] text-slate-500">
                                    No products in this restock order.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection