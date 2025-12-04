<?php

namespace App\Support\MobileIndex;

class SupplierRestockConfig
{
    public static function config($statusOptions): array
    {
        return [
            'search' => [
                'enabled' => true,
                'param' => 'q',
                'placeholder' => 'Cari PO Number...',
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

            'fab' => null, // No FAB for suppliers

            'empty_state' => [
                'icon' => 'clipboard-x',
                'title' => 'Tidak ada pesanan.',
                'description' => 'Belum ada pesanan restock yang masuk.',
                'reset_route' => route('supplier.restocks.index'),
            ],

            'hidden_query' => [],
        ];
    }
}
