<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\IncomingTransactionController;
use App\Http\Controllers\Admin\OutgoingTransactionController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Role-based dashboards
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
        ->name('admin.dashboard');
});

Route::middleware(['auth', 'role:manager'])->group(function () {
    Route::get('/manager/dashboard', function () {
        return view('manager.dashboard');
    })->name('manager.dashboard');
});

Route::middleware(['auth', 'role:staff'])->group(function () {
    Route::get('/staff/dashboard', function () {
        return view('staff.dashboard');
    })->name('staff.dashboard');
});

Route::middleware(['auth', 'role:supplier'])->group(function () {
    Route::get('/supplier/dashboard', function () {
        return view('supplier.dashboard');
    })->name('supplier.dashboard');
});

/*
|--------------------------------------------------------------------------
| Admin area: products & categories (Admin & Manager)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin,manager'])
    ->prefix('admin')
    ->as('admin.')
    ->group(function () {
        // Product management
        Route::resource('products', ProductController::class);

        // Quick add category + category CRUD
        Route::post('categories/quick-store', [CategoryController::class, 'quickStore'])
            ->name('categories.quick-store');
        Route::resource('categories', CategoryController::class)->except(['show']);

        // Supplier management
        Route::resource('suppliers', SupplierController::class)->except(['show']);
    });

Route::middleware(['auth', 'role:admin,manager,staff'])
    ->prefix('admin')
    ->as('admin.')
    ->group(function () {
        Route::resource('purchases', IncomingTransactionController::class)
            ->only(['index', 'create', 'store', 'show']);
    });

Route::middleware(['auth', 'role:admin,manager'])
    ->prefix('admin')
    ->as('admin.')
    ->group(function () {
        Route::patch('purchases/{purchase}/verify', [IncomingTransactionController::class, 'verify'])
            ->name('purchases.verify');

        Route::patch('purchases/{purchase}/reject', [IncomingTransactionController::class, 'reject'])
            ->name('purchases.reject');

        Route::patch('purchases/{purchase}/complete', [IncomingTransactionController::class, 'complete'])
            ->name('purchases.complete');
    });

Route::middleware(['auth', 'role:admin,manager'])
    ->prefix('admin')
    ->as('admin.')
    ->group(function () {
        Route::patch('sales/{sale}/approve', [OutgoingTransactionController::class, 'approve'])
            ->name('sales.approve');

        Route::patch('sales/{sale}/ship', [OutgoingTransactionController::class, 'ship'])
            ->name('sales.ship');
    });
    
require __DIR__ . '/auth.php';
