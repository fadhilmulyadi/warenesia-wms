<?php

namespace App\Support\MobileIndex;

class SupplierConfig
{
    public static function config(): array
    {
        return [
            'search' => [
                'enabled' => true,
                'param' => 'q',
                'placeholder' => 'Cari nama supplier...',
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
                        'key' => 'average_rating|desc',
                        'label' => 'Rating Tertinggi',
                        'field' => 'average_rating',
                        'direction' => 'desc',
                    ],
                    [
                        'key' => 'rated_restock_count|desc',
                        'label' => 'Transaksi Terbanyak',
                        'field' => 'rated_restock_count',
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
                'enabled' => false,
                'route' => null,
                'label' => 'Tambah Supplier',
                'icon' => 'plus',
            ],

            'empty_state' => [
                'icon' => 'search-x',
                'title' => 'Tidak ada supplier ditemukan.',
                'description' => 'Coba ubah kata kunci.',
                'reset_route' => route('suppliers.index'),
            ],

            'hidden_query' => [],
        ];
    }
}
