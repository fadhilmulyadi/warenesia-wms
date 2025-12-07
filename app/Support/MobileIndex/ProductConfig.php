<?php

namespace App\Support\MobileIndex;

class ProductConfig
{
    public static function config($categories): array
    {
        return [
            'search' => [
                'enabled' => true,
                'param' => 'q',
                'placeholder' => 'Cari nama, SKU...',
            ],

            'filters' => [
                'category_id' => [
                    'enabled' => true,
                    'type' => 'select',
                    'label' => 'Kategori',
                    'param' => 'category_id',
                    'multiple' => false,
                    'options' => $categories->pluck('name', 'id')->toArray(),
                ],
                'stock_status' => [
                    'enabled' => true,
                    'type' => 'checkbox-list',
                    'label' => 'Status Stok',
                    'param' => 'stock_status',
                    'multiple' => true,
                    'options' => [
                        'in_stock' => 'In Stock',
                        'low_stock' => 'Low Stock',
                        'out_of_stock' => 'Out of Stock',
                    ],
                ],
            ],

            'sort' => [
                'enabled' => true,
                'param' => 'sort',
                'direction_param' => 'direction',
                'default' => [
                    'field' => 'name',
                    'direction' => 'asc',
                ],
                'options' => [
                    [
                        'key' => 'name|asc',
                        'label' => 'Nama (A-Z)',
                        'field' => 'name',
                        'direction' => 'asc',
                    ],
                    [
                        'key' => 'name|desc',
                        'label' => 'Nama (Z-A)',
                        'field' => 'name',
                        'direction' => 'desc',
                    ],
                    [
                        'key' => 'sku|asc',
                        'label' => 'SKU (A-Z)',
                        'field' => 'sku',
                        'direction' => 'asc',
                    ],
                    [
                        'key' => 'current_stock|asc',
                        'label' => 'Stok (Sedikit-Banyak)',
                        'field' => 'current_stock',
                        'direction' => 'asc',
                    ],
                    [
                        'key' => 'current_stock|desc',
                        'label' => 'Stok (Banyak-Sedikit)',
                        'field' => 'current_stock',
                        'direction' => 'desc',
                    ],
                    [
                        'key' => 'created_at|desc',
                        'label' => 'Terbaru ditambahkan',
                        'field' => 'created_at',
                        'direction' => 'desc',
                    ],
                ],
            ],

            'fab' => [
                'enabled' => true,
                'route' => route('products.create'),
                'label' => 'Tambah Produk',
                'icon' => 'plus',
            ],

            'empty_state' => [
                'icon' => 'package-search',
                'title' => 'Tidak ada produk ditemukan.',
                'description' => 'Coba ubah kata kunci atau reset filter.',
                'reset_route' => route('products.index'),
            ],

            'hidden_query' => [],
        ];
    }
}
