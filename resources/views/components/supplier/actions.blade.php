@props(['supplier'])

<x-table.actions>
    @can('update', $supplier)
        <x-table.action-item icon="pencil" href="{{ route('suppliers.edit', $supplier) }}">
            Edit
        </x-table.action-item>
    @endcan

    @can('delete', $supplier)
        <x-table.action-item
            icon="trash-2"
            danger="true"
            x-on:click="$dispatch('open-delete-modal', { 
                action: '{{ route('suppliers.destroy', $supplier) }}',
                title: 'Hapus Supplier',
                message: 'Hapus supplier ini? Tindakan tidak dapat dibatalkan.',
                itemName: '{{ addslashes($supplier->name) }}'
            })"
        >
            Hapus
        </x-table.action-item>
    @endcan
</x-table.actions>
