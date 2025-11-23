<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\IncomingTransaction;
use App\Models\OutgoingTransaction;
use App\Models\Product;
use App\Models\RestockOrder;
use App\Models\Supplier;
use App\Models\User;
use App\Policies\CategoryPolicy;
use App\Policies\IncomingTransactionPolicy;
use App\Policies\OutgoingTransactionPolicy;
use App\Policies\ProductPolicy;
use App\Policies\ReportPolicy;
use App\Policies\RestockOrderPolicy;
use App\Policies\SupplierPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Product::class             => ProductPolicy::class,
        Category::class            => CategoryPolicy::class,
        Supplier::class            => SupplierPolicy::class,
        IncomingTransaction::class => IncomingTransactionPolicy::class,
        OutgoingTransaction::class => OutgoingTransactionPolicy::class,
        RestockOrder::class        => RestockOrderPolicy::class,
        User::class                => UserPolicy::class,
        // ReportPolicy is wired through a Gate (non-model ability) below.
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('view-transactions-report', [ReportPolicy::class, 'viewTransactionsReport']);
    }
}
