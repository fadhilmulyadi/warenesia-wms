@php
    $statusVariants = [
        'pending' => 'warning',
        'confirmed' => 'info',
        'in_transit' => 'primary',
        'received' => 'success',
        'cancelled' => 'danger',
        'rejected' => 'danger',
    ];
    $statusVariant = $statusVariants[$item->status] ?? 'neutral';
    $statusLabel = $statusOptions[$item->status] ?? ucfirst($item->status);
@endphp

<x-mobile.card>
    <div class="flex items-center justify-between">
        <div class="font-semibold text-slate-900">{{ $item->po_number }}</div>
        <x-badge :variant="$statusVariant">{{ $statusLabel }}</x-badge>
    </div>

    <div class="text-xs text-slate-500">
        {{ $item->supplier->name ?? '-' }}
    </div>

    <div class="text-xs">
        <x-badge variant="neutral">Total: Rp {{ number_format($item->total_amount, 0, ',', '.') }}</x-badge>
    </div>

    <div class="pt-2 flex items-center justify-between text-xs text-slate-500 border-t border-slate-50 mt-1">
        <span>Tgl: {{ $item->order_date?->format('d M Y') }}</span>
        <span>Item: {{ $item->total_items }} items</span>
    </div>

    <div class="pt-3 flex gap-2">
        <a href="{{ route('supplier.restocks.show', $item) }}"
            class="flex-1 h-9 rounded-lg bg-slate-100 text-slate-700 text-xs flex items-center justify-center gap-2 hover:bg-slate-200 transition">
            <x-lucide-eye class="w-4 h-4" /> Detail
        </a>
    </div>
</x-mobile.card>