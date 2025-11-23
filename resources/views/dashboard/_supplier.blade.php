<div class="space-y-4 text-xs max-w-5xl mx-auto">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <p class="text-[11px] text-slate-500">Pending restock orders</p>
            <p class="text-2xl font-semibold text-slate-900">{{ number_format($pendingRestocksCount) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <p class="text-[11px] text-slate-500">In transit</p>
            <p class="text-2xl font-semibold text-slate-900">{{ number_format($inTransitRestocksCount) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <p class="text-[11px] text-slate-500">View all</p>
            <a
                href="{{ route('supplier.restocks.index') }}"
                class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-800"
            >
                Open restocks
                <x-lucide-arrow-right class="h-3 w-3" />
            </a>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-4 space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-[11px] font-semibold text-slate-800 uppercase tracking-wide">
                Recent restock orders
            </h2>
        </div>

        <div class="rounded-xl border border-slate-200 overflow-hidden">
            <table class="min-w-full text-left text-xs">
                <thead class="bg-slate-50 text-[11px] text-slate-500 uppercase tracking-wide">
                    <tr>
                        <th class="px-3 py-2">PO #</th>
                        <th class="px-3 py-2 w-32">Order date</th>
                        <th class="px-3 py-2 w-32">Expected</th>
                        <th class="px-3 py-2 w-24 text-right">Quantity</th>
                        <th class="px-3 py-2 w-28 text-center">Status</th>
                        <th class="px-3 py-2 w-20 text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentRestocks as $restock)
                        <tr>
                            <td class="px-3 py-2 font-mono text-[11px] text-slate-800">
                                {{ $restock->po_number }}
                            </td>
                            <td class="px-3 py-2 text-[11px] text-slate-600">
                                {{ $restock->order_date?->format('d M Y') ?? '-' }}
                            </td>
                            <td class="px-3 py-2 text-[11px] text-slate-600">
                                {{ $restock->expected_delivery_date?->format('d M Y') ?? '-' }}
                            </td>
                            <td class="px-3 py-2 text-right text-[12px] text-slate-900">
                                {{ number_format((int) $restock->total_quantity) }}
                            </td>
                            <td class="px-3 py-2 text-center">
                                @include('components.status-badge', [
                                    'status' => $restock->status,
                                    'label' => $restock->status_label,
                                ])
                            </td>
                            <td class="px-3 py-2 text-right">
                                <a
                                    href="{{ route('supplier.restocks.show', $restock) }}"
                                    class="inline-flex items-center rounded-lg border border-slate-200 px-2 py-1 text-[11px] text-slate-700 hover:bg-slate-50"
                                >
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-4 text-center text-[11px] text-slate-500">
                                No recent restock orders.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
