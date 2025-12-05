<x-mobile.card>
    {{-- CONTENT --}}
    <div class="flex items-center gap-3">
        {{-- Ikon Visual --}}
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-500">
            <x-lucide-scale class="h-5 w-5" />
        </div>

        {{-- Nama Unit --}}
        <div class="text-base font-semibold text-slate-900 leading-snug">
            {{ $item->name }}
        </div>
    </div>

    {{-- ACTION BUTTONS --}}
    <div class="mt-4 flex gap-3">
        <a href="{{ route('units.edit', $item) }}"
           class="flex-1 h-11 rounded-xl bg-slate-100 text-slate-700 text-sm font-medium flex items-center justify-center gap-2 hover:bg-slate-200 active:scale-95 transition">
            <x-lucide-pencil class="w-5 h-5" /> Edit
        </a>

        @if(auth()->user()->can('delete', $item))
            <button x-on:click="$dispatch('open-delete-modal', { 
                        action: '{{ route('units.destroy', $item) }}',
                        title: 'Hapus Satuan',
                        message: 'Yakin ingin menghapus satuan ini?',
                        itemName: '{{ addslashes($item->name) }}'
                    })"
                class="w-11 h-11 flex items-center justify-center rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-100 active:scale-95 transition">
                <x-lucide-trash class="w-5 h-5" />
            </button>
        @endif
    </div>
</x-mobile.card>