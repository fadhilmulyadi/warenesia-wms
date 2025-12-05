@php
    $statusLabel = $item->is_active ? 'Active' : 'Inactive';
    $statusVariant = $item->is_active ? 'success' : 'neutral';
@endphp

<x-mobile.card>
    {{-- HEADER --}}
    <div class="flex items-start justify-between gap-3">
        <div class="text-base font-semibold text-slate-900 leading-snug line-clamp-2">
            {{ $item->name }}
        </div>
        <x-badge :variant="$statusVariant" class="shrink-0">
            {{ $statusLabel }}
        </x-badge>
    </div>

    {{-- CONTACT PERSON --}}
    <div class="mt-3 flex items-center gap-2 text-slate-600">
        <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-500">
            <x-lucide-user class="h-3.5 w-3.5" />
        </div>
        <span class="text-sm font-medium truncate">
            {{ $item->contact_person ?? 'Tidak ada kontak' }}
        </span>
    </div>

    {{-- META INFO --}}
    <div class="mt-2 flex items-center gap-4 text-xs text-slate-500 ml-8">
        {{-- Kota --}}
        <div class="flex items-center gap-1.5">
            <x-lucide-map-pin class="w-3.5 h-3.5 text-slate-400" />
            <span>{{ $item->city ?? '-' }}</span>
        </div>

        {{-- Rating --}}
        @if($item->average_rating > 0)
            <div class="flex items-center gap-1">
                <x-lucide-star class="w-3.5 h-3.5 text-amber-400 fill-amber-400" />
                <span class="font-semibold text-slate-700">{{ number_format($item->average_rating, 1) }}</span>
            </div>
        @endif
    </div>

    {{-- PHONE --}}
    @if(!empty($item->phone))
        <div class="mt-3 pt-3 border-t border-slate-50">
            <a href="tel:{{ $item->phone }}" class="flex items-center gap-2 text-sm text-slate-600 hover:text-teal-600 transition group">
                <div class="p-1.5 rounded-md bg-slate-50 group-hover:bg-teal-50 text-slate-400 group-hover:text-teal-600 transition">
                    <x-lucide-phone class="w-4 h-4" />
                </div>
                <span class="font-mono font-medium">{{ $item->phone }}</span>
            </a>
        </div>
    @endif

    {{-- ACTION BUTTONS --}}
    <div class="mt-4 flex gap-3">
        <a href="{{ route('suppliers.edit', $item) }}"
           class="flex-1 h-11 rounded-xl bg-slate-100 text-slate-700 text-sm font-medium flex items-center justify-center gap-2 hover:bg-slate-200 active:scale-95 transition">
            <x-lucide-pencil class="w-5 h-5" /> Edit
        </a>

        @if(auth()->user()->can('delete', $item))
            <button x-on:click="$dispatch('open-delete-modal', { 
                        action: '{{ route('suppliers.destroy', $item) }}',
                        title: 'Hapus Supplier',
                        message: 'Yakin ingin menghapus supplier ini?',
                        itemName: '{{ addslashes($item->name) }}'
                    })"
                class="w-11 h-11 flex items-center justify-center rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 active:scale-95 transition">
                <x-lucide-trash class="w-5 h-5" />
            </button>
        @endif
    </div>
</x-mobile.card>