<?php

namespace App\Support\MobileIndex;

class SaleConfig
{
    public static function config($statusOptions): array
    {
        return [
            'search' => [
                'enabled' => true,
                'param' => 'q',
                'placeholder' => 'Cari No Transaksi atau Customer...',
            ],

            'filters' => [
                'status' => [
                    'enabled' => true,
                    'type' => 'select',
                    'label' => 'Status',
                    'param' => 'status',
                    'multiple' => false,
                    'options' => $statusOptions,
                ],
                'date_range' => [
                    'enabled' => true,
                    'type' => 'date-range',
                    'label' => 'Tanggal Transaksi',
                    'param' => ['date_from', 'date_to'],
                ],
            ],

            'sort' => [
                'enabled' => true,
                'param' => 'sort',
                'direction_param' => 'direction',
                'default' => [
                    'field' => 'transaction_date',
                    'direction' => 'desc',
                ],
                'options' => [
                    [
                        'key' => 'transaction_date|desc',
                        'label' => 'Tanggal Terbaru',
                        'field' => 'transaction_date',
                        'direction' => 'desc',
                    ],
                    [
                        'key' => 'transaction_date|asc',
                        'label' => 'Tanggal Terlama',
                        'field' => 'transaction_date',
                        'direction' => 'asc',
                    ],
                    [
                        'key' => 'total_amount|desc',
                        'label' => 'Nominal Tertinggi',
                        'field' => 'total_amount',
                        'direction' => 'desc',
                    ],
                    [
                        'key' => 'total_amount|asc',
                        'label' => 'Nominal Terendah',
                        'field' => 'total_amount',
                        'direction' => 'asc',
                    ],
                ],
            ],

            'fab' => [
                'enabled' => true,
                'route' => route('sales.create'),
                'label' => 'Catat Penjualan',
                'icon' => 'plus',
            ],

            'empty_state' => [
                'icon' => 'trending-up',
                'title' => 'Tidak ada transaksi penjualan.',
                'description' => 'Coba ubah filter atau catat transaksi baru.',
                'reset_route' => route('transactions.index', ['tab' => 'outgoing']),
            ],

            'hidden_query' => ['tab' => 'outgoing'],
        ];
    }
}
