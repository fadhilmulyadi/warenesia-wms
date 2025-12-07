<?php

namespace App\Services\Dashboard;

use App\Models\IncomingTransaction;
use App\Models\OutgoingTransaction;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Support\TransactionPrefill;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class StaffDashboardService
{
    public function getData(User $user, Request $request): array
    {
        $prefill = TransactionPrefill::forDashboard($request);
        $products = $this->formProducts();

        return [
            'products' => $products,
            'productSkuMap' => $products->mapWithKeys(fn($p) => [$p->sku => $p->id]),
            'suppliers' => $this->suppliers(),
            'todayTransactions' => $this->todayTransactions($user),
            'poReadyToReceive' => $this->poReadyToReceive(),
            'prefilledType' => $prefill['type'],
            'prefilledSupplierId' => $prefill['prefilledSupplierId'] ?? $prefill['supplier_id'], // Fix potential key mismatch if any
            'prefilledCustomerName' => $prefill['customer_name'],
            'prefilledProductId' => $prefill['product_id'],
            'prefilledQuantity' => $prefill['quantity'],
            'prefilledUnitPrice' => $prefill['unit_price'],
            'prefilledUnitCost' => $prefill['unit_cost'],
            'defaultDate' => now()->toDateString(),
        ];
    }

    private function poReadyToReceive()
    {
        return \App\Models\RestockOrder::where('status', \App\Models\RestockOrder::STATUS_RECEIVED)
            ->whereDoesntHave('incomingTransaction')
            ->with(['supplier', 'items'])
            ->orderBy('updated_at', 'desc')
            ->get();
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
            ->map(fn($trx) => $this->mapTxn($trx, 'download'));

        $outgoing = OutgoingTransaction::query()
            ->where('created_by', $user->id)
            ->whereDate('transaction_date', $today)
            ->get()
            ->map(fn($trx) => $this->mapTxn($trx, 'upload'));

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
            'description' => "{$trx->total_quantity} items - " . ucfirst(str_replace('_', ' ', $trx->status->value)),
            'meta' => $trx->created_at?->format('H:i'),
            'created_at' => $trx->created_at,
            'href' => route('dashboard.staff', [
                'type' => $type,
                'product_id' => $firstItem?->product_id,
                'supplier_id' => $trx instanceof IncomingTransaction ? $trx->supplier_id : null,
                'customer_name' => $trx instanceof OutgoingTransaction ? $trx->customer_name : null,
                'quantity' => $trx->total_quantity,
            ]),
        ];
    }
}
