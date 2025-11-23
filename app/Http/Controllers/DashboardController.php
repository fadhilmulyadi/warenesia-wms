<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\IncomingTransaction;
use App\Models\OutgoingTransaction;
use App\Models\Product;
use App\Models\RestockOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $canViewReports = $user->can('view-transactions-report');
        $canViewProducts = $user->can('viewAny', Product::class);
        $canViewRestocks = $user->can('viewAny', RestockOrder::class);
        $canViewSupplierRestocks = $user->can('viewSupplierRestocks', RestockOrder::class);
        $canViewSales = $user->can('viewAny', OutgoingTransaction::class);
        $canCreatePurchases = $user->can('create', IncomingTransaction::class);
        $canCreateSales = $user->can('create', OutgoingTransaction::class);

        $isSupplierPortalOnly = $canViewSupplierRestocks && ! $canViewRestocks;

        $commonStats = $this->buildCommonStats($user, $isSupplierPortalOnly);

        $adminData = $canViewReports ? $this->buildAdminData() : null;
        $managerData = ($canViewProducts || $canViewRestocks || $canViewSales) ? $this->buildManagerData() : null;
        $staffData = ($canCreatePurchases || $canCreateSales) ? $this->buildStaffData($user) : null;
        $supplierData = $canViewSupplierRestocks ? $this->buildSupplierData($user) : null;

        return view('dashboard', [
            'user' => $user,
            'commonStats' => $commonStats,
            'adminData' => $adminData,
            'managerData' => $managerData,
            'staffData' => $staffData,
            'supplierData' => $supplierData,
        ]);
    }

    private function buildCommonStats(User $user, bool $limitToOwnRestocks): array
    {
        $lowStockCount = Product::whereColumn('current_stock', '<', 'min_stock')->count();

        $openRestocksQuery = RestockOrder::query()->whereIn('status', [
            RestockOrder::STATUS_PENDING,
            RestockOrder::STATUS_CONFIRMED,
            RestockOrder::STATUS_IN_TRANSIT,
        ]);

        $pendingSalesQuery = OutgoingTransaction::query()
            ->where('status', OutgoingTransaction::STATUS_PENDING);

        if ($limitToOwnRestocks) {
            $openRestocksQuery->where('supplier_id', $user->id);
            $pendingSalesQuery->where('created_by', $user->id);
        }

        return [
            'products' => Product::count(),
            'suppliers' => Supplier::count(),
            'lowStock' => $lowStockCount,
            'openRestocks' => $openRestocksQuery->count(),
            'pendingSales' => $pendingSalesQuery->count(),
        ];
    }

    private function buildAdminData(): array
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

        return [
            'period' => [
                'start' => $periodStart,
                'end' => $periodEnd,
            ],
            'quickLinks' => $quickLinks,
            'kpi' => $kpi,
            'stockHealth' => $stockHealth,
            'recentRestocks' => $recentRestocks,
            'recentSales' => $recentSales,
            'topProducts' => $topProducts,
            'lowStockProducts' => $lowStockProducts,
        ];
    }

    private function buildManagerData(): array
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

        return [
            'pendingPurchases' => $pendingPurchases,
            'pendingSales' => $pendingSales,
            'pendingRestocks' => $pendingRestocks,
            'lowStockProducts' => $lowStockProducts,
            'restocksInProgress' => $restocksInProgress,
        ];
    }

    private function buildStaffData(User $user): array
    {
        $today = now()->toDateString();

        $incomingToday = IncomingTransaction::with('supplier')
            ->whereDate('transaction_date', $today)
            ->where('created_by', $user->id)
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get();

        $outgoingToday = OutgoingTransaction::whereDate('transaction_date', $today)
            ->where('created_by', $user->id)
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get();

        return [
            'incomingToday' => $incomingToday,
            'outgoingToday' => $outgoingToday,
            'today' => $today,
        ];
    }

    private function buildSupplierData(User $user): array
    {
        $recentRestocks = RestockOrder::where('supplier_id', $user->id)
            ->orderByDesc('order_date')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $pendingRestocksCount = RestockOrder::where('supplier_id', $user->id)
            ->where('status', RestockOrder::STATUS_PENDING)
            ->count();

        $inTransitRestocksCount = RestockOrder::where('supplier_id', $user->id)
            ->where('status', RestockOrder::STATUS_IN_TRANSIT)
            ->count();

        return [
            'recentRestocks' => $recentRestocks,
            'pendingRestocksCount' => $pendingRestocksCount,
            'inTransitRestocksCount' => $inTransitRestocksCount,
        ];
    }
}

