@php
    $statusVariants = [
        'pending' => 'warning',
        'verified' => 'info',
        'completed' => 'success',
        'canceled' => 'danger', // Tambahan umum
    ];
    $statusValue = $item->status instanceof \BackedEnum ? $item->status->value : (string) $item->status;
    $statusVariant = $statusVariants[$statusValue] ?? 'neutral';

    $statusLabel = isset($statusOptions)
        ? ($statusOptions[$statusValue] ?? ucfirst(str_replace('_', ' ', $statusValue)))
        : ($item->status instanceof \BackedEnum && method_exists($item->status, 'label')
            ? $item->status->label()
            : ucfirst(str_replace('_', ' ', $statusValue)));
@endphp

<x-mobile.card>
    {{-- HEADER --}}
    <div class="flex items-start justify-between gap-3">
        <div class="flex flex-col">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">No. Transaksi</span>
            <span class="text-base font-mono font-bold text-slate-900 leading-tight">
                {{ $item->transaction_number }}
            </span>
        </div>
        <x-badge :variant="$statusVariant" class="shrink-0">
            {{ $statusLabel }}
        </x-badge>
    </div>

    {{-- SUPPLIER INFO --}}
    <div class="mt-3 flex items-center gap-2 text-slate-600">
        <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-500">
            <x-lucide-building-2 class="h-3.5 w-3.5" /> {{-- Atau user icon --}}
        </div>
        <span class="text-sm font-medium truncate">
            {{ $item->supplier->name ?? '-' }}
        </span>
    </div>

    {{-- KEY METRICS --}}
    <div class="mt-4 pt-3 border-t border-slate-100 flex items-end justify-between">
        {{-- Tanggal & Jumlah Item --}}
        <div class="space-y-1">
            <div class="flex items-center gap-1.5 text-xs text-slate-500">
                <x-lucide-calendar class="w-3.5 h-3.5" />
                <span>{{ $item->transaction_date?->format('d M Y') }}</span>
            </div>
            <div class="flex items-center gap-1.5 text-xs text-slate-500">
                <x-lucide-package class="w-3.5 h-3.5" />
                <span>{{ $item->total_items }} Items</span>
            </div>
        </div>

        {{-- Total Harga --}}
        <div class="text-right">
            <span class="text-xs text-slate-400">Total</span>
            <p class="text-base font-bold text-slate-900">
                Rp {{ number_format($item->total_amount, 0, ',', '.') }}
            </p>
        </div>
    </div>

    {{-- ACTIONS --}}
    <div class="mt-4 flex gap-3">
        <a href="{{ route('purchases.show', $item) }}"
           class="flex-1 h-11 rounded-xl bg-slate-100 text-slate-700 text-sm font-medium flex items-center justify-center gap-2 hover:bg-slate-200 active:scale-95 transition">
            <x-lucide-eye class="w-5 h-5" /> Detail
        </a>

        @if($item->status === 'pending')
            <a href="{{ route('purchases.edit', $item) }}"
               class="w-11 h-11 flex items-center justify-center rounded-xl bg-teal-50 text-teal-600 hover:bg-teal-100 active:scale-95 transition">
                <x-lucide-pencil class="w-5 h-5" />
            </a>
        @endif
    </div>
</x-mobile.card>
