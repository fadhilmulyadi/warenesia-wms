@props(['unit'])

<x-table.actions>
    <x-table.action-item icon="pencil" href="{{ route('units.edit', $unit) }}">
        Edit
    </x-table.action-item>

    @if(!$unit->products_count)
        <x-table.action-item
            icon="trash-2"
            danger="true"
            x-on:click="$dispatch('open-delete-modal', { 
                action: '{{ route('units.destroy', $unit) }}',
                title: 'Hapus Satuan',
                message: 'Yakin ingin menghapus satuan ini?',
                itemName: '{{ addslashes($unit->name) }}'
            })"
        >
            Hapus
        </x-table.action-item>
    @else
        <span class="text-[11px] text-slate-400">Digunakan</span>
    @endif
</x-table.actions>
