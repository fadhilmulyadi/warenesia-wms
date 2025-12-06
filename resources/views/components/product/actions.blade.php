@props(['product'])

@php
    /** @var \App\Models\Product $product */
    $deleteMessage = $product->current_stock > 0
        ? "Produk ini masih memiliki stok sebanyak <b>{$product->current_stock}</b>. Menghapus produk ini akan menghilangkan data stok secara permanen. Lanjutkan?"
        : null;
@endphp

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
            x-on:click="$dispatch('open-delete-modal', {
                action: '{{ route('products.destroy', $product) }}',
                title: 'Hapus Produk',
                itemName: '{{ addslashes($product->name) }}',
                message: {{ $deleteMessage ? "'" . addslashes($deleteMessage) . "'" : 'null' }}
            })"
        >
            Hapus
        </x-table.action-item>
    @endcan
</x-table.actions>
