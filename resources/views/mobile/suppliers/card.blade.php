@php
    $statusLabel = $item->is_active ? 'Active' : 'Inactive';
    $statusVariant = $item->is_active ? 'success' : 'neutral';
@endphp

<x-mobile.card>
    <div class="flex items-center justify-between">
        <div class="font-semibold text-slate-900">{{ $item->name }}</div>
        <x-badge :variant="$statusVariant">{{ $statusLabel }}</x-badge>
    </div>

    <div class="text-xs text-slate-500">
        {{ $item->contact_person }}
    </div>

    <div class="text-xs">
        @if($item->average_rating > 0)
            <x-badge variant="warning">Rating: {{ number_format($item->average_rating, 1) }} ⭐</x-badge>
        @else
            <x-badge variant="neutral">Belum ada rating</x-badge>
        @endif
    </div>

    <div class="pt-2 flex items-center justify-between text-xs text-slate-500 border-t border-slate-50 mt-1">
        <span>Telp: {{ $item->phone ?? '-' }}</span>
        <span>Kota: {{ $item->city ?? '-' }}</span>
    </div>

    <div class="pt-3 flex gap-2">
        <a href="{{ route('suppliers.edit', $item) }}"
            class="flex-1 h-9 rounded-lg bg-slate-100 text-slate-700 text-xs flex items-center justify-center gap-2 hover:bg-slate-200 transition">
            <x-lucide-pencil class="w-4 h-4" /> Edit
        </a>

        @if(auth()->user()->can('delete', $item))
            <button x-on:click="$dispatch('open-delete-modal', { 
                        action: '{{ route('suppliers.destroy', $item) }}',
                        title: 'Hapus Supplier',
                        message: 'Yakin ingin menghapus supplier ini?',
                        itemName: '{{ addslashes($item->name) }}'
                    })"
                class="w-9 h-9 flex items-center justify-center rounded-lg bg-rose-100 text-rose-600 hover:bg-rose-200 transition">
                <x-lucide-trash class="w-4 h-4" />
            </button>
        @endif
    </div>
</x-mobile.card>