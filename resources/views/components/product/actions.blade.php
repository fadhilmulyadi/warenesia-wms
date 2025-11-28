@props(['product'])

<x-table.actions>
    @can('update', $product)
        <x-table.action-item icon="pencil" href="{{ route('products.edit', $product) }}">
            Edit Produk
        </x-table.action-item>
    @endcan

    @can('viewBarcode', $product)
        <x-table.action-item icon="printer" href="{{ route('products.barcode.label', $product) }}" target="_blank">
            Cetak Barcode
        </x-table.action-item>
    @endcan

    @can('delete', $product)
        <x-table.action-item 
            icon="trash-2" 
            danger="true" 
            on-click="$dispatch('open-delete-modal', { 
                action: '{{ route('products.destroy', $product) }}',
                title: 'Hapus Produk',
                itemName: '{{ $product->name }}'
            })"
        >
            Hapus
        </x-table.action-item>
    @endcan
</x-table.actions>