<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IncomingTransactionController;
use App\Http\Controllers\OutgoingTransactionController;
use App\Http\Controllers\ProductBarcodeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductScanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RestockOrderController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SupplierRegistrationController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/dashboard/admin', [DashboardController::class, 'admin'])
        ->middleware('role:admin')
        ->name('dashboard.admin');

    Route::get('/dashboard/manager', [DashboardController::class, 'manager'])
        ->middleware('role:manager')
        ->name('dashboard.manager');

    Route::get('/dashboard/staff', [DashboardController::class, 'staff'])
        ->middleware('role:staff')
        ->name('dashboard.staff');

    Route::get('/dashboard/supplier', [DashboardController::class, 'supplier'])
        ->middleware('role:supplier')
        ->name('dashboard.supplier');

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

    Route::middleware('can:manage-users')->group(function () {
        Route::resource('users', UserController::class);

        Route::patch('users/{user}/approve', [UserController::class, 'approveSupplier'])
            ->name('users.approve');
    });
});

Route::middleware('guest')->group(function () {
    Route::get('supplier/register', [SupplierRegistrationController::class, 'create'])
        ->name('supplier.register');
    Route::post('supplier/register', [SupplierRegistrationController::class, 'store']);
});

Route::middleware(['auth', 'role:admin,manager'])
    ->group(function () {
        Route::get('products/export', [ProductController::class, 'export'])
            ->name('products.export');

        Route::resource('products', ProductController::class);

        Route::post('categories/quick-store', [CategoryController::class, 'quickStore'])
            ->name('categories.quick-store');

        Route::get('categories/export', [CategoryController::class, 'export'])
            ->name('categories.export');
        Route::resource('categories', CategoryController::class)
            ->except(['show']);

        Route::post('units/quick-store', [UnitController::class, 'quickStore'])
            ->name('units.quick-store');
        Route::resource('units', UnitController::class)
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
    });

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

        Route::resource('purchases', IncomingTransactionController::class);

        Route::get('sales/export', [OutgoingTransactionController::class, 'export'])
            ->name('sales.export');

        Route::resource('sales', OutgoingTransactionController::class);

        Route::get('/transactions', [TransactionController::class, 'index'])
            ->name('transactions.index');
    });

Route::middleware(['auth', 'role:admin,manager'])
    ->group(function () {
        Route::patch('purchases/{purchase}/verify', [IncomingTransactionController::class, 'verify'])
            ->name('purchases.verify');

        Route::patch('purchases/{purchase}/reject', [IncomingTransactionController::class, 'reject'])
            ->name('purchases.reject');

        Route::patch('purchases/{purchase}/complete', [IncomingTransactionController::class, 'complete'])
            ->name('purchases.complete');

        Route::patch('sales/{sale}/approve', [OutgoingTransactionController::class, 'approve'])
            ->name('sales.approve');

        Route::patch('sales/{sale}/ship', [OutgoingTransactionController::class, 'ship'])
            ->name('sales.ship');
    });


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
