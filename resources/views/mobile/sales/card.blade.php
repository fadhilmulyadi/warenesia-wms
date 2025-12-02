@php
    $statusVariants = [
        'pending' => 'warning',
        'approved' => 'info',
        'shipped' => 'success',
    ];
    $statusVariant = $statusVariants[$item->status] ?? 'neutral';
    // Assuming statusOptions is passed or available globally, otherwise fallback to ucfirst
    $statusLabel = isset($statusOptions) ? ($statusOptions[$item->status] ?? ucfirst($item->status)) : ucfirst($item->status);
@endphp

<x-mobile.card>
    <div class="flex items-center justify-between">
        <div class="font-semibold text-slate-900">{{ $item->transaction_number }}</div>
        <x-badge :variant="$statusVariant">{{ $statusLabel }}</x-badge>
    </div>

    <div class="text-xs text-slate-500">
        {{ $item->customer_name }}
    </div>

    <div class="text-xs">
        <x-badge variant="neutral">Total: Rp {{ number_format($item->total_amount, 0, ',', '.') }}</x-badge>
    </div>

    <div class="pt-2 flex items-center justify-between text-xs text-slate-500 border-t border-slate-50 mt-1">
        <span>Tgl: {{ $item->transaction_date?->format('d M Y') }}</span>
        <span>Item: {{ $item->total_items }} items</span>
    </div>

    <div class="pt-3 flex gap-2">
        <a href="{{ route('sales.show', $item) }}"
            class="flex-1 h-9 rounded-lg bg-slate-100 text-slate-700 text-xs flex items-center justify-center gap-2 hover:bg-slate-200 transition">
            <x-lucide-eye class="w-4 h-4" /> Detail
        </a>

        @if($item->status === 'pending')
            <a href="{{ route('sales.edit', $item) }}"
                class="w-9 h-9 flex items-center justify-center rounded-lg bg-teal-50 text-teal-600 hover:bg-teal-100 transition">
                <x-lucide-pencil class="w-4 h-4" />
            </a>
        @endif
    </div>
</x-mobile.card>