<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\IncomingTransactionController;
use App\Http\Controllers\Admin\OutgoingTransactionController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RestockOrderController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Manager\DashboardController as ManagerDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Staff\DashboardController as StaffDashboardController;
use App\Http\Controllers\Supplier\DashboardController as SupplierDashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public & generic dashboard routes
|--------------------------------------------------------------------------
| Halaman publik dan fallback dashboard generik.
| Dashboard generik tetap disiapkan jika suatu saat
| ada user yang tidak punya role spesifik.
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

/*
|--------------------------------------------------------------------------
| Authenticated user profile
|--------------------------------------------------------------------------
| Profile dikelola di luar area admin agar tetap konsisten
| untuk semua role.
*/

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Role-based dashboards
|--------------------------------------------------------------------------
| Setiap role punya dashboard awal sendiri untuk memisahkan
| perspektif Admin, Manager, Staff Gudang, dan Supplier.
*/

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
        ->name('admin.dashboard');
});

Route::middleware(['auth', 'role:manager'])->group(function () {
    Route::get('/manager/dashboard', [ManagerDashboardController::class, 'index'])
        ->name('manager.dashboard');
});

Route::middleware(['auth', 'role:staff'])->group(function () {
    Route::get('/staff/dashboard', [StaffDashboardController::class, 'index'])
        ->name('staff.dashboard');
});

Route::middleware(['auth', 'role:supplier'])->group(function () {
    Route::get('/supplier/dashboard', [SupplierDashboardController::class, 'index'])
        ->name('supplier.dashboard');
});

/*
|--------------------------------------------------------------------------
| Admin & Manager: master data dan konfigurasi gudang
|--------------------------------------------------------------------------
| Hanya Admin dan Warehouse Manager yang boleh mengelola
| master data inti: produk, kategori, supplier, dan restock orders.
*/

Route::middleware(['auth', 'role:admin,manager'])
    ->prefix('admin')
    ->as('admin.')
    ->group(function () {
        // Product management (inventory master)
        Route::resource('products', ProductController::class);

        // Category management, termasuk quick add dari form produk
        Route::post('categories/quick-store', [CategoryController::class, 'quickStore'])
            ->name('categories.quick-store');

        Route::resource('categories', CategoryController::class)
            ->except(['show']);

        // Supplier master data
        Route::resource('suppliers', SupplierController::class)
            ->except(['show']);

        // Restock orders (PO ke supplier)
        Route::resource('restocks', RestockOrderController::class)
            ->only(['index', 'create', 'store', 'show']);

        Route::patch('restocks/{restockOrder}/rating', [RestockOrderController::class, 'rate'])
            ->name('restocks.rate');

        Route::patch('restocks/{restock}/mark-in-transit', [RestockOrderController::class, 'markInTransit'])
            ->name('restocks.mark-in-transit');

        Route::patch('restocks/{restock}/mark-received', [RestockOrderController::class, 'markReceived'])
            ->name('restocks.mark-received');

        Route::patch('restocks/{restock}/cancel', [RestockOrderController::class, 'cancel'])
            ->name('restocks.cancel');

        Route::get('reports/transactions', [ReportController::class, 'transactions'])
            ->name('reports.transactions');

        Route::get('reports/transactions/export', [ReportController::class, 'exportTransactions'])
            ->name('reports.transactions.export');
    });

/*
|--------------------------------------------------------------------------
| Admin, Manager, Staff: transaksi harian (purchases & sales)
|--------------------------------------------------------------------------
| Ketiga role ini terlibat di transaksi harian. Pembatasan hak akses
| lebih detail (misalnya siapa boleh approve) diatur di group lain.
*/

Route::middleware(['auth', 'role:admin,manager,staff'])
    ->prefix('admin')
    ->as('admin.')
    ->group(function () {
        // Incoming transactions (barang masuk / purchases)
        Route::resource('purchases', IncomingTransactionController::class)
            ->only(['index', 'create', 'store', 'show']);

        // Outgoing transactions (barang keluar / sales)
        Route::resource('sales', OutgoingTransactionController::class)
            ->only(['index', 'create', 'store', 'show']);
    });

/*
|--------------------------------------------------------------------------
| Admin & Manager: approval dan status perubahan transaksi
|--------------------------------------------------------------------------
| Aksi yang mengubah status bisnis penting (approve, verify, ship)
| dibatasi hanya untuk Admin dan Manager.
*/

Route::middleware(['auth', 'role:admin,manager'])
    ->prefix('admin')
    ->as('admin.')
    ->group(function () {
        // Approval flow untuk incoming transactions (purchases)
        Route::patch('purchases/{purchase}/verify', [IncomingTransactionController::class, 'verify'])
            ->name('purchases.verify');

        Route::patch('purchases/{purchase}/reject', [IncomingTransactionController::class, 'reject'])
            ->name('purchases.reject');

        Route::patch('purchases/{purchase}/complete', [IncomingTransactionController::class, 'complete'])
            ->name('purchases.complete');

        // Approval flow untuk outgoing transactions (sales)
        Route::patch('sales/{sale}/approve', [OutgoingTransactionController::class, 'approve'])
            ->name('sales.approve');

        Route::patch('sales/{sale}/ship', [OutgoingTransactionController::class, 'ship'])
            ->name('sales.ship');
    });

/*
|--------------------------------------------------------------------------
| Supplier portal: restock orders
|--------------------------------------------------------------------------
| Supplier dapat melihat dan mengelola restock order mereka sendiri.
*/
Route::middleware(['auth', 'role:supplier'])
    ->prefix('supplier')
    ->as('supplier.')
    ->group(function () {
        Route::get('restocks', [RestockOrderController::class, 'supplierIndex'])
            ->name('restocks.index');

        Route::get('restocks/{restock}', [RestockOrderController::class, 'supplierShow'])
            ->name('restocks.show');

        Route::patch('restocks/{restock}/confirm', [RestockOrderController::class, 'supplierConfirm'])
            ->name('restocks.confirm');

        Route::patch('restocks/{restock}/reject', [RestockOrderController::class, 'supplierReject'])
            ->name('restocks.reject');
    });

require __DIR__ . '/auth.php';
