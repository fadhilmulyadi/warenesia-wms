<?php

namespace App\Services\Dashboard;

use App\Models\RestockOrder;
use App\Models\User;

class SupplierDashboardService
{
    public function getData(User $supplier): array
    {
        return [
            'pendingRestockOrders' => $this->pending($supplier),
            'deliveryHistory' => $this->history($supplier),
        ];
    }

    private function pending(User $supplier): array
    {
        return RestockOrder::where('supplier_id', $supplier->id)
            ->where('status', 'pending')
            ->limit(5)
            ->get()
            ->map(fn ($o) => [
                'icon' => 'clock',
                'title' => $o->po_number,
                'description' => $o->expected_delivery_date?->format('d M Y'),
                'meta' => "{$o->total_items} items",
                'meta_color' => 'border-amber-100 bg-amber-50 text-amber-700',
            ])
            ->all();
    }

    private function history(User $supplier): array
    {
        return RestockOrder::where('supplier_id', $supplier->id)
            ->whereIn('status', ['confirmed', 'in_transit', 'received'])
            ->limit(6)
            ->get()
            ->map(fn ($o) => [
                'icon' => 'truck',
                'title' => $o->po_number,
                'description' => $o->order_date?->format('d M Y'),
                'meta' => ucfirst(str_replace('_', ' ', $o->status)),
                'meta_color' => $o->isReceived()
                    ? 'border-emerald-100 bg-emerald-50 text-emerald-700'
                    : 'border-sky-100 bg-sky-50 text-sky-700',
            ])
            ->all();
    }
}