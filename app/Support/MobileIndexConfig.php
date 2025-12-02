<?php

namespace App\Support;

class MobileIndexConfig
{
    public static function users(array $roles, array $statuses): array
    {
        return [
            'search' => [
                'enabled' => true,
                'param' => 'q',
                'placeholder' => 'Cari nama atau email...',
            ],

            'filters' => [
                'role' => [
                    'enabled' => true,
                    'type' => 'checkbox-list',
                    'label' => 'Role',
                    'param' => 'role',
                    'multiple' => true,
                    'options' => $roles,
                ],
                'status' => [
                    'enabled' => true,
                    'type' => 'checkbox-list',
                    'label' => 'Status',
                    'param' => 'status',
                    'multiple' => true,
                    'options' => $statuses,
                ],
            ],

            'sort' => [
                'enabled' => true,
                'param' => 'sort',
                'direction_param' => 'direction',
                'default' => [
                    'field' => 'created_at',
                    'direction' => 'desc',
                ],
                'options' => [
                    [
                        'key' => 'created_at|desc',
                        'label' => 'Terbaru ditambahkan',
                        'field' => 'created_at',
                        'direction' => 'desc',
                    ],
                    [
                        'key' => 'created_at|asc',
                        'label' => 'Terlama ditambahkan',
                        'field' => 'created_at',
                        'direction' => 'asc',
                    ],
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
                ],
            ],

            'fab' => [
                'enabled' => true,
                'route' => route('users.create'),
                'label' => 'Tambah User',
                'icon' => 'plus',
            ],

            'empty_state' => [
                'icon' => 'search-x',
                'title' => 'Tidak ada user ditemukan.',
                'description' => 'Coba ubah kata kunci atau reset filter.',
                'reset_route' => route('users.index'),
            ],

            'hidden_query' => [],
        ];
    }

    public static function products($categories): array
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

    public static function suppliers(): array
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
                'enabled' => true,
                'route' => route('suppliers.create'),
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

    public static function restocks($statusOptions): array
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

    public static function purchases($statusOptions): array
    {
        return [
            'search' => [
                'enabled' => true,
                'param' => 'q',
                'placeholder' => 'Cari No Transaksi atau Supplier...',
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
                'route' => route('purchases.create'),
                'label' => 'Catat Pembelian',
                'icon' => 'plus',
            ],

            'empty_state' => [
                'icon' => 'shopping-cart',
                'title' => 'Tidak ada transaksi pembelian.',
                'description' => 'Coba ubah filter atau catat transaksi baru.',
                'reset_route' => route('purchases.index'),
            ],

            'hidden_query' => [],
        ];
    }

    public static function sales($statusOptions): array
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
                'reset_route' => route('sales.index'),
            ],

            'hidden_query' => [],
        ];
    }

    public static function categories(): array
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

    public static function units(): array
    {
        return [
            'search' => [
                'enabled' => true,
                'param' => 'q',
                'placeholder' => 'Cari nama satuan...',
            ],

            'filters' => [],

            'sort' => [
                'enabled' => false,
            ],

            'fab' => [
                'enabled' => true,
                'route' => route('units.create'),
                'label' => 'Tambah Satuan',
                'icon' => 'plus',
            ],

            'empty_state' => [
                'icon' => 'scale',
                'title' => 'Tidak ada satuan.',
                'description' => 'Coba ubah kata kunci.',
                'reset_route' => route('units.index'),
            ],

            'hidden_query' => [],
        ];
    }
}
