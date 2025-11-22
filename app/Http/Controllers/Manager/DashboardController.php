<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\IncomingTransaction;
use App\Models\OutgoingTransaction;
use App\Models\Product;
use App\Models\RestockOrder;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $pendingPurchases = IncomingTransaction::where('status', IncomingTransaction::STATUS_PENDING)->count();
        $pendingSales = OutgoingTransaction::where('status', OutgoingTransaction::STATUS_PENDING)->count();
        $pendingRestocks = RestockOrder::where('status', RestockOrder::STATUS_PENDING)->count();

        $lowStockProducts = Product::whereColumn('current_stock', '<', 'min_stock')
            ->orderBy('current_stock')
            ->limit(10)
            ->get();

        $restocksInProgress = RestockOrder::with('supplier')
            ->whereIn('status', [RestockOrder::STATUS_CONFIRMED, RestockOrder::STATUS_IN_TRANSIT])
            ->orderByDesc('order_date')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return view('manager.dashboard', compact(
            'pendingPurchases',
            'pendingSales',
            'pendingRestocks',
            'lowStockProducts',
            'restocksInProgress'
        ));
    }
}
