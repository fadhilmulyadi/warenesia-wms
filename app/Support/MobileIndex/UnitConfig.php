<?php

namespace App\Support\MobileIndex;

class UnitConfig
{
    public static function config(): array
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
