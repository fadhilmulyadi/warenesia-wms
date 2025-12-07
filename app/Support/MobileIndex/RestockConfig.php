<?php

namespace App\Support\MobileIndex;

class RestockConfig
{
    public static function config($statusOptions): array
    {
        return [
            'search' => [
                'enabled' => true,
                'param' => 'q',
                'placeholder' => 'Cari PO Number atau Supplier...',
            ],

            'filters' => [
                'status' => [
                    'enabled' => true,
                    'type' => 'checkbox-list',
                    'label' => 'Status',
                    'param' => 'status',
                    'multiple' => true,
                    'options' => $statusOptions,
                ],
                'date_range' => [
                    'enabled' => true,
                    'type' => 'date-range',
                    'label' => 'Tanggal Order',
                    'param' => ['date_from', 'date_to'],
                ],
            ],

            'sort' => [
                'enabled' => true,
                'param' => 'sort',
                'direction_param' => 'direction',
                'default' => [
                    'field' => 'order_date',
                    'direction' => 'desc',
                ],
                'options' => [
                    [
                        'key' => 'order_date|desc',
                        'label' => 'Tanggal Terbaru',
                        'field' => 'order_date',
                        'direction' => 'desc',
                    ],
                    [
                        'key' => 'order_date|asc',
                        'label' => 'Tanggal Terlama',
                        'field' => 'order_date',
                        'direction' => 'asc',
                    ],
                    [
                        'key' => 'po_number|asc',
                        'label' => 'PO Number (A-Z)',
                        'field' => 'po_number',
                        'direction' => 'asc',
                    ],
                    [
                        'key' => 'po_number|desc',
                        'label' => 'PO Number (Z-A)',
                        'field' => 'po_number',
                        'direction' => 'desc',
                    ],
                ],
            ],

            'fab' => [
                'enabled' => true,
                'route' => route('restocks.create'),
                'label' => 'Buat Restock',
                'icon' => 'plus',
            ],

            'empty_state' => [
                'icon' => 'clipboard-x',
                'title' => 'Tidak ada restock order.',
                'description' => 'Coba ubah filter atau buat order baru.',
                'reset_route' => route('restocks.index'),
            ],

            'hidden_query' => [],
        ];
    }
}
