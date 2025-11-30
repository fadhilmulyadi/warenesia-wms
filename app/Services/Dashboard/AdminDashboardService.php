<?php

namespace App\Services\Dashboard;

use App\Models\Product;
use App\Models\IncomingTransaction;
use App\Models\OutgoingTransaction;
use App\Models\RestockOrder;
use Illuminate\Support\Carbon;

class AdminDashboardService
{
    public function getData(): array
    {
        return [
            'kpis' => $this->kpis(),
            'lowStockAlerts' => $this->lowStockAlerts(),
            'quickLinks' => $this->shortcuts(),
        ];
    }

    public function kpis(): array
    {
        return [
            [
                'title' => 'Total Produk',
                'value' => $this->formatNumber(Product::count()),
                'subtitle' => 'SKU aktif dalam katalog',
                'icon' => 'box',
            ],
            [
                'title' => 'Transaksi Bulanan',
                'value' => $this->formatNumber($this->countMonthlyTransactions()),
                'subtitle' => 'Barang masuk dan keluar',
                'icon' => 'bar-chart-3',
            ],
            [
                'title' => 'Nilai Inventori',
                'value' => $this->formatCurrency($this->calculateInventoryValue()),
                'subtitle' => 'Berdasarkan harga rata-rata',
                'icon' => 'wallet',
            ],
        ];
    }

    private function lowStockAlerts(): array
    {
        return Product::query()
            ->whereColumn('current_stock', '<', 'min_stock')
            ->orderBy('current_stock')
            ->limit(6)
            ->get(['id','name', 'sku', 'current_stock', 'min_stock'])
            ->map(function ($product) {
                return [
                    'icon' => 'alert-triangle',
                    'title' => $product->name,
                    'description' => "{$product->sku} - Min {$product->min_stock}",
                    'meta' => "Stok tersisa: {$product->current_stock}",
                    'meta_color' => $product->current_stock == 0 
                        ? 'border-red-100 bg-red-50 text-red-700'
                        : 'border-amber-100 bg-amber-50 text-amber-700',
                    'href' => route('products.edit', $product->id),
                ];
            })
            ->all();
    }

    private function shortcuts(): array
    {
        return [
            ['title' => 'Produk', 'description' => 'Kelola Katalog dan Stok', 'icon' => 'box', 'href' => route('products.index')],
            ['title' => 'Transaksi', 'description' => 'Barang Masuk dan Keluar', 'icon' => 'arrow-right-left', 'href' => route('transactions.index')],
            ['title' => 'Pesanan Restok', 'description' => 'Permintaan Pembelian', 'icon' => 'repeat', 'href' => route('restocks.index')],
        ];
    }

    private function countMonthlyTransactions(): int
    {
        [$start, $end] = [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];

        return IncomingTransaction::whereBetween('transaction_date', [$start, $end])->count()
            + OutgoingTransaction::whereBetween('transaction_date', [$start, $end])->count();
    }

    private function calculateInventoryValue(): float
    {
        return (float) Product::query()
            ->selectRaw('SUM(purchase_price * current_stock) as value')
            ->value('value');
    }

    private function formatNumber($value): string
    {
        return number_format($value, 0, ',', '.');
    }

    private function formatCurrency($value): string
    {
        return 'Rp ' . number_format($value, 0, ',', '.');
    }
}
