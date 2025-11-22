<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use App\Models\RestockOrder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $supplier = $request->user();

        $recentRestocks = RestockOrder::where('supplier_id', $supplier->id)
            ->orderByDesc('order_date')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $pendingRestocksCount = RestockOrder::where('supplier_id', $supplier->id)
            ->where('status', RestockOrder::STATUS_PENDING)
            ->count();

        $inTransitRestocksCount = RestockOrder::where('supplier_id', $supplier->id)
            ->where('status', RestockOrder::STATUS_IN_TRANSIT)
            ->count();

        return view('supplier.dashboard', compact(
            'recentRestocks',
            'pendingRestocksCount',
            'inTransitRestocksCount'
        ));
    }
}
