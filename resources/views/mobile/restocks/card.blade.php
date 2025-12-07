@php
    $statusVariants = [
        'pending' => 'warning',
        'confirmed' => 'info',
        'in_transit' => 'primary', // Warna biru/utama untuk barang dalam perjalanan
        'received' => 'success',
        'cancelled' => 'danger',
    ];
    $statusValue = $item->status instanceof \BackedEnum ? $item->status->value : (string) $item->status;
    $statusVariant = $statusVariants[$statusValue] ?? 'neutral';
    
    // Fallback status label logic
    $statusLabel = isset($statusOptions)
        ? ($statusOptions[$statusValue] ?? ucfirst(str_replace('_', ' ', $statusValue)))
        : ($item->status instanceof \BackedEnum && method_exists($item->status, 'label')
            ? $item->status->label()
            : ucfirst(str_replace('_', ' ', $statusValue)));
@endphp

<x-mobile.card>
    {{-- 1. HEADER: PO Number & Status --}}
    <div class="flex items-start justify-between gap-3">
        <div class="flex flex-col">
            {{-- Label kecil untuk konteks --}}
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">No. PO</span>
            {{-- PO Number: Font Mono agar karakter jelas (misal 0 vs O) --}}
            <span class="text-base font-mono font-bold text-slate-900 leading-tight">
                {{ $item->po_number }}
            </span>
        </div>
        <x-badge :variant="$statusVariant" class="shrink-0">
            {{ $statusLabel }}
        </x-badge>
    </div>

    {{-- 2. SUPPLIER INFO: Icon Building/Truck + Text-sm --}}
    <div class="mt-3 flex items-center gap-2 text-slate-600">
        <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-500">
            {{-- Ikon Truck cocok untuk Restock/Pengiriman --}}
            <x-lucide-truck class="h-3.5 w-3.5" /> 
        </div>
        <span class="text-sm font-medium truncate">
            {{ $item->supplier->name ?? '-' }}
        </span>
    </div>

    {{-- 3. KEY METRICS: Layout Invoice (Kiri: Tanggal/Item, Kanan: Harga) --}}
    <div class="mt-4 pt-3 border-t border-slate-100 flex items-end justify-between">
        {{-- Kiri: Tanggal & Jumlah Item --}}
        <div class="space-y-1">
            <div class="flex items-center gap-1.5 text-xs text-slate-500">
                <x-lucide-calendar class="w-3.5 h-3.5" />
                <span>{{ $item->order_date?->format('d M Y') }}</span>
            </div>
            <div class="flex items-center gap-1.5 text-xs text-slate-500">
                <x-lucide-package class="w-3.5 h-3.5" />
                <span>{{ $item->total_items }} Items</span>
            </div>
        </div>

        {{-- Kanan: Total Harga (Hero Content) --}}
        <div class="text-right">
            <span class="text-xs text-slate-400">Total</span>
            <p class="text-base font-bold text-slate-900">
                Rp {{ number_format($item->total_amount, 0, ',', '.') }}
            </p>
        </div>
    </div>

    {{-- 4. ACTION BUTTONS: Full Width, h-11 --}}
    <div class="mt-4 flex gap-3">
        <a href="{{ route('restocks.show', $item) }}"
           class="flex-1 h-11 rounded-xl bg-slate-100 text-slate-700 text-sm font-medium flex items-center justify-center gap-2 hover:bg-slate-200 active:scale-95 transition">
            <x-lucide-eye class="w-5 h-5" /> Detail
        </a>
        
        {{-- Jika nanti ada tombol Edit/Delete, bisa ditambahkan di sini --}}
    </div>
</x-mobile.card>
