<?php

namespace App\Services\Dashboard;

use App\Models\Product;
use App\Models\IncomingTransaction;
use App\Models\OutgoingTransaction;
use App\Models\Supplier;
use Illuminate\Support\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Support\TransactionPrefill;

class StaffDashboardService
{
    public function getData(User $user, Request $request): array
    {
        $prefill = TransactionPrefill::forDashboard($request);

        return [
            'products' => $this->formProducts(),
            'suppliers' => $this->suppliers(),
            'todayTransactions' => $this->todayTransactions($user),
            'prefilledType' => $prefill['type'],
            'prefilledSupplierId' => $prefill['supplier_id'],
            'prefilledCustomerName' => $prefill['customer_name'],
            'prefilledProductId' => $prefill['product_id'],
            'prefilledQuantity' => $prefill['quantity'],
            'prefilledUnitPrice' => $prefill['unit_price'],
            'prefilledUnitCost' => $prefill['unit_cost'],
            'defaultDate' => now()->toDateString(),
        ];
    }

    private function formProducts()
    {
        return Product::orderBy('name')
            ->get([
                'id',
                'name',
                'sku',
                'current_stock',
                'purchase_price',
                'sale_price',
            ]);
    }

    private function suppliers()
    {
        return Supplier::orderBy('name')->get(['id', 'name']);
    }

    private function todayTransactions(User $user): array
    {
        $today = Carbon::today();

        $incoming = IncomingTransaction::query()
            ->where('created_by', $user->id)
            ->whereDate('transaction_date', $today)
            ->get()
            ->map(fn ($trx) => $this->mapTxn($trx, 'download'));

        $outgoing = OutgoingTransaction::query()
            ->where('created_by', $user->id)
            ->whereDate('transaction_date', $today)
            ->get()
            ->map(fn ($trx) => $this->mapTxn($trx, 'upload'));

        return $incoming->concat($outgoing)
            ->sortByDesc('created_at')
            ->values()
            ->all();
    }

    private function mapTxn($trx, $icon): array
    {   
        $type = $trx instanceof \App\Models\IncomingTransaction
            ? 'purchases'
            : 'sales';

        $firstItem = $trx->items()->first();

        return [
            'icon' => $icon,
            'title' => ucfirst($trx->transaction_number),
            'description' => "{$trx->total_quantity} items - " . ucfirst(str_replace('_', ' ', $trx->status)),
            'meta' => $trx->created_at?->format('H:i'),
            'created_at' => $trx->created_at,
            'href' => route('dashboard.staff', [
                'type' => $type,
                'product_id' => $firstItem?->product_id,
                'supplier_id' => $trx instanceof IncomingTransaction ? $trx->supplier_id : null,
                'customer_name' => $trx instanceof OutgoingTransaction ? $trx->customer_name : null,
                'quantity' => $trx->total_quantity,
            ])
        ];
    }
}