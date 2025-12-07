<?php

namespace App\Services\Dashboard;

use App\Enums\RestockStatus;
use App\Models\Category;
use App\Models\IncomingTransaction;
use App\Models\OutgoingTransaction;
use App\Models\Product;
use App\Models\RestockOrder;

class ManagerDashboardService
{
    public function getData(): array
    {
        return [
            'overview' => $this->overview(),
            'pendingApprovals' => $this->pendingApprovals(),
            'activeRestocks' => $this->activeRestocks(),
        ];
    }

    private function overview(): array
    {
        return [
            ['title' => 'Total Barang', 'value' => Product::count(), 'subtitle' => 'SKU Aktif', 'icon' => 'box'],
            ['title' => 'Stok Rendah', 'value' => Product::whereColumn('current_stock', '<', 'min_stock')->count(), 'subtitle' => 'Di bawah batas minimum', 'icon' => 'alert-octagon'],
            ['title' => 'Kategori', 'value' => Category::count(), 'subtitle' => 'Grup Aktif', 'icon' => 'tags'],
        ];
    }

    private function pendingApprovals(): array
    {
        return [
            [
                'icon' => 'clipboard-check',
                'title' => 'Persetujuan Barang Masuk',
                'description' => 'Pembelian menunggu verifikasi',
                'meta' => IncomingTransaction::where('status', 'pending')->count().' pending',
                'meta_color' => 'bg-amber-50 text-amber-700 border-amber-200',
                'href' => route('transactions.index', ['tab' => 'incoming', 'status' => 'pending']),
            ],
            [
                'icon' => 'truck',
                'title' => 'Persetujuan Barang Keluar',
                'description' => 'Penjualan menunggu persetujuan',
                'meta' => OutgoingTransaction::where('status', 'pending')->count().' pending',
                'meta_color' => 'bg-amber-50 text-amber-700 border-amber-200',
                'href' => route('transactions.index', ['tab' => 'outgoing', 'status' => 'pending']),
            ],
        ];
    }

    private function activeRestocks(): array
    {
        return RestockOrder::query()
            ->with('supplier')
            ->whereIn('status', ['pending', 'confirmed', 'in_transit'])
            ->orderByDesc('order_date')
            ->limit(6)
            ->get()
            ->map(fn ($order) => [
                'title' => $order->po_number,
                'description' => $order->supplier?->name ?? 'Supplier',
                'progress' => $this->progress($order->status),
                // 'status' => ucfirst(str_replace('_', ' ', $order->status)),
                'status' => $order->status, // ← KIRIM STATUS ASLI (bukan yang di-ucfirst)
                'eta' => optional($order->expected_delivery_date)->format('d M'),
            ])
            ->all();
    }

    private function progress(RestockStatus|string|null $status): int
    {
        $value = $status instanceof RestockStatus
            ? $status->value
            : (string) $status;

        $map = [
            RestockStatus::PENDING->value => 20,
            RestockStatus::CONFIRMED->value => 45,
            RestockStatus::IN_TRANSIT->value => 75,
            RestockStatus::RECEIVED->value => 100,
        ];

        return $map[$value] ?? 10;
    }
}
