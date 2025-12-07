<?php

namespace App\Support\MobileIndex;

class UserConfig
{
    public static function config(array $roles, array $statuses): array
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
}
