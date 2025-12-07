<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IncomingTransactionController;
use App\Http\Controllers\OutgoingTransactionController;
use App\Http\Controllers\ProductBarcodeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RestockOrderController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SupplierRegistrationController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// --- AUTHENTICATED COMMON ROUTES (Dashboards & Profile) ---
Route::middleware('auth')->group(function () {
    // Dashboard Routing
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

    // Dashboard Redirections
    Route::redirect('/admin/dashboard', '/dashboard');
    Route::redirect('/manager/dashboard', '/dashboard');
    Route::redirect('/staff/dashboard', '/dashboard');
    Route::redirect('/supplier/dashboard', '/dashboard');

    // User Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    // --- USER MANAGEMENT (Super Admin) ---
    Route::middleware('can:manage-users')->group(function () {
        Route::resource('users', UserController::class);

        Route::patch('users/{user}/approve', [UserController::class, 'approveSupplier'])
            ->name('users.approve');
    });
});

// --- GUEST ROUTES (Registration) ---
Route::middleware('guest')->group(function () {
    Route::get('supplier/register', [SupplierRegistrationController::class, 'create'])
        ->name('supplier.register');
    Route::post('supplier/register', [SupplierRegistrationController::class, 'store']);
});

// --- MANAGEMENT CORE (Admin & Manager) ---
// Includes: Products, Categories, Units, Suppliers, Restocks
Route::middleware(['auth', 'role:admin,manager'])
    ->group(function () {
        // Product Management
        Route::get('products/export', [ProductController::class, 'export'])
            ->name('products.export');

        Route::resource('products', ProductController::class);

        // Category Management
        Route::post('categories/quick-store', [CategoryController::class, 'quickStore'])
            ->name('categories.quick-store');

        Route::get('categories/export', [CategoryController::class, 'export'])
            ->name('categories.export');
        Route::resource('categories', CategoryController::class)
            ->except(['show']);

        // Unit Management
        Route::post('units/quick-store', [UnitController::class, 'quickStore'])
            ->name('units.quick-store');
        Route::resource('units', UnitController::class)
            ->except(['show']);

        // Supplier Management
        Route::get('suppliers/export', [SupplierController::class, 'export'])
            ->name('suppliers.export');

        Route::resource('suppliers', SupplierController::class)
            ->except(['show']);

        // Restock Management
        Route::get('restocks/export', [RestockOrderController::class, 'export'])
            ->name('restocks.export');

        Route::resource('restocks', RestockOrderController::class)
            ->only(['index', 'create', 'store', 'show']);

        // Restock Actions
        Route::patch('restocks/{restockOrder}/rating', [RestockOrderController::class, 'rate'])
            ->name('restocks.rate');

        Route::patch('restocks/{restock}/mark-in-transit', [RestockOrderController::class, 'markInTransit'])
            ->name('restocks.mark-in-transit');

        Route::patch('restocks/{restock}/mark-received', [RestockOrderController::class, 'markReceived'])
            ->name('restocks.mark-received');

        Route::patch('restocks/{restock}/cancel', [RestockOrderController::class, 'cancel'])
            ->name('restocks.cancel');
    });

// --- OPERATIONAL TRANSACTIONS (Admin, Manager, Staff) ---
// Includes: Scanning, Incoming (Purchases), Outgoing (Sales)
Route::middleware(['auth', 'role:admin,manager,staff'])
    ->group(function () {
        // Barcode & Scanning
        Route::get('products/{product}/barcode', [ProductBarcodeController::class, 'show'])
            ->name('products.barcode');

        Route::get('products/{product}/barcode/label', [ProductBarcodeController::class, 'label'])
            ->name('products.barcode.label');

        // Incoming Transactions (Purchases)
        Route::get('purchases/export', [IncomingTransactionController::class, 'export'])
            ->name('purchases.export');

        Route::resource('purchases', IncomingTransactionController::class);

        // Outgoing Transactions (Sales)
        Route::get('sales/export', [OutgoingTransactionController::class, 'export'])
            ->name('sales.export');

        Route::resource('sales', OutgoingTransactionController::class);

        // Transaction History
        Route::get('/transactions/export', [TransactionController::class, 'export'])
            ->name('transactions.export');

        Route::get('/transactions', [TransactionController::class, 'index'])
            ->name('transactions.index');
    });

// --- TRANSACTION APPROVALS & VERIFICATIONS (Admin & Manager) ---
Route::middleware(['auth', 'role:admin,manager'])
    ->group(function () {
        // Purchase Verification
        Route::patch('purchases/{purchase}/verify', [IncomingTransactionController::class, 'verify'])
            ->name('purchases.verify');

        Route::patch('purchases/{purchase}/reject', [IncomingTransactionController::class, 'reject'])
            ->name('purchases.reject');

        Route::patch('purchases/{purchase}/complete', [IncomingTransactionController::class, 'complete'])
            ->name('purchases.complete');

        // Sales Approval & Shipping
        Route::patch('sales/{sale}/approve', [OutgoingTransactionController::class, 'approve'])
            ->name('sales.approve');

        Route::patch('sales/{sale}/ship', [OutgoingTransactionController::class, 'ship'])
            ->name('sales.ship');
    });

// --- SUPPLIER PORTAL ---
Route::middleware(['auth', 'role:supplier'])
    ->prefix('supplier')
    ->as('supplier.')
    ->group(function () {
        Route::get('restocks', [RestockOrderController::class, 'supplierIndex'])
            ->name('restocks.index');

        Route::get('restocks/{restock}', [RestockOrderController::class, 'supplierShow'])
            ->name('restocks.show');

        // Supplier Actions on Restoock
        Route::patch('restocks/{restock}/confirm', [RestockOrderController::class, 'supplierConfirm'])
            ->name('restocks.confirm');

        Route::patch('restocks/{restock}/reject', [RestockOrderController::class, 'supplierReject'])
            ->name('restocks.reject');
    });

require __DIR__ . '/auth.php';
