@props(['product'])

@php
    /** @var \App\Models\Product $product */
    $hasStock = $product->current_stock > 0;
    $deleteMessage = $hasStock
        ? "Produk ini masih memiliki stok sebanyak <b>{$product->current_stock}</b>. Menghapus produk ini akan menghilangkan data stok secara permanen. Lanjutkan?"
        : '';

    $deletePayload = [
        'action' => route('products.destroy', $product),
        'title' => 'Hapus Produk',
        'itemName' => $product->name,
        'message' => $deleteMessage,
    ];
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
        <x-table.action-item icon="trash-2" danger="true" data-payload="{{ json_encode($deletePayload) }}"
            x-on:click="$dispatch('open-delete-modal', JSON.parse($el.dataset.payload))">
            Hapus
        </x-table.action-item>
    @endcan
</x-table.actions>