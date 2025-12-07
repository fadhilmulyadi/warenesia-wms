<?php

namespace App\Support\MobileIndex;

class CategoryConfig
{
    public static function config(): array
    {
        return [
            'search' => [
                'enabled' => true,
                'param' => 'q',
                'placeholder' => 'Cari nama kategori...',
            ],

            'filters' => [],

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
                        'key' => 'products_count|desc',
                        'label' => 'Produk Terbanyak',
                        'field' => 'products_count',
                        'direction' => 'desc',
                    ],
                    [
                        'key' => 'products_count|asc',
                        'label' => 'Produk Sedikit',
                        'field' => 'products_count',
                        'direction' => 'asc',
                    ],
                ],
            ],

            'fab' => [
                'enabled' => true,
                'route' => route('categories.create'),
                'label' => 'Tambah Kategori',
                'icon' => 'plus',
            ],

            'empty_state' => [
                'icon' => 'tag',
                'title' => 'Tidak ada kategori.',
                'description' => 'Coba ubah kata kunci.',
                'reset_route' => route('categories.index'),
            ],

            'hidden_query' => [],
        ];
    }
}
