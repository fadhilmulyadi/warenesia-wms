<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IncomingTransactionController;
use App\Http\Controllers\OutgoingTransactionController;
use App\Http\Controllers\ProductBarcodeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductScanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RestockOrderController;
use App\Http\Controllers\SupplierController;
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

/*
|--------------------------------------------------------------------------
| Authenticated user profile
|--------------------------------------------------------------------------
| Profile dikelola di luar area admin agar tetap konsisten
| untuk semua role.
*/

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::redirect('/admin/dashboard', '/dashboard');
    Route::redirect('/manager/dashboard', '/dashboard');
    Route::redirect('/staff/dashboard', '/dashboard');
    Route::redirect('/supplier/dashboard', '/dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Admin & Manager: master data dan konfigurasi gudang
|--------------------------------------------------------------------------
| Hanya Admin dan Warehouse Manager yang boleh mengelola
| master data inti: produk, kategori, supplier, dan restock orders.
*/

Route::middleware(['auth', 'role:admin,manager'])
    ->group(function () {
        Route::get('products/export', [ProductController::class, 'export'])
            ->name('products.export');

        // Product management (inventory master)
        Route::resource('products', ProductController::class);

        // Category management, termasuk quick add dari form produk
        Route::post('categories/quick-store', [CategoryController::class, 'quickStore'])
            ->name('categories.quick-store');

        Route::get('categories/export', [CategoryController::class, 'export'])
            ->name('categories.export');
        Route::resource('categories', CategoryController::class)
            ->except(['show']);

        Route::get('suppliers/export', [SupplierController::class, 'export'])
            ->name('suppliers.export');
        // Supplier master data
        Route::resource('suppliers', SupplierController::class)
            ->except(['show']);

        Route::get('restocks/export', [RestockOrderController::class, 'export'])
            ->name('restocks.export');
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
    ->group(function () {
        Route::get('products/{product}/barcode', [ProductBarcodeController::class, 'show'])
            ->name('products.barcode');

        Route::get('products/{product}/barcode/label', [ProductBarcodeController::class, 'label'])
            ->name('products.barcode.label');

        Route::get('barcode/scan', [ProductScanController::class, 'showForm'])
            ->name('barcode.scan');

        Route::post('barcode/scan', [ProductScanController::class, 'handleScan'])
            ->name('barcode.scan.handle');

        Route::get('purchases/export', [IncomingTransactionController::class, 'export'])
            ->name('purchases.export');
        // Incoming transactions (barang masuk / purchases)
        Route::resource('purchases', IncomingTransactionController::class)
            ->only(['index', 'create', 'store', 'show']);

        Route::get('sales/export', [OutgoingTransactionController::class, 'export'])
            ->name('sales.export');
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
