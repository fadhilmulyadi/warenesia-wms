<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Customer;
use App\Models\IncomingTransaction;
use App\Models\OutgoingTransaction;
use App\Models\Product;
use App\Models\RestockOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $periodStart = now()->startOfMonth();
        $periodEnd = now()->endOfMonth();

        $quickLinks = [
            'products' => Product::count(),
            'categories' => Category::count(),
            'suppliers' => Supplier::count(),
            'customers' => Customer::count(),
            'users' => User::count(),
        ];

        $revenue = OutgoingTransaction::whereBetween('transaction_date', [$periodStart, $periodEnd])
            ->whereIn('status', [OutgoingTransaction::STATUS_APPROVED, OutgoingTransaction::STATUS_SHIPPED])
            ->sum('total_amount');

        $inflow = IncomingTransaction::whereBetween('transaction_date', [$periodStart, $periodEnd])
            ->whereIn('status', [IncomingTransaction::STATUS_VERIFIED, IncomingTransaction::STATUS_COMPLETED])
            ->sum('total_amount');

        $pendingOrders = OutgoingTransaction::where('status', OutgoingTransaction::STATUS_PENDING)->count();
        $pendingPurchases = IncomingTransaction::where('status', IncomingTransaction::STATUS_PENDING)->count();

        $kpi = [
            'revenue' => $revenue,
            'net' => $revenue - $inflow,
            'pendingOrders' => $pendingOrders,
            'dueOrders' => 0, // TODO: implement when due date is available.
            'overdueOrders' => 0, // TODO: implement when due date is available.
            'inflow' => $inflow,
            'outflow' => $inflow,
            'pendingPurchases' => $pendingPurchases,
        ];

        $stockHealth = [
            'totalSkus' => $quickLinks['products'],
            'lowStock' => Product::whereColumn('current_stock', '<', 'min_stock')->count(),
            'outOfStock' => Product::where('current_stock', 0)->count(),
            'totalOnHand' => (int) Product::sum('current_stock'),
        ];

        $recentRestocks = RestockOrder::with('supplier')
            ->orderByDesc('order_date')
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        $recentSales = OutgoingTransaction::orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        $topProducts = Product::orderByDesc('current_stock')
            ->limit(5)
            ->get();

        $lowStockProducts = Product::whereColumn('current_stock', '<', 'min_stock')
            ->orderBy('current_stock')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'quickLinks',
            'kpi',
            'stockHealth',
            'recentRestocks',
            'recentSales',
            'topProducts',
            'lowStockProducts'
        ));
    }
}
