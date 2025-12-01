<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\IncomingTransaction;
use App\Models\OutgoingTransaction;
use App\Models\Product;
use App\Models\RestockOrder;
use App\Models\Unit;
use App\Models\Supplier;
use App\Models\User;
use App\Policies\CategoryPolicy;
use App\Policies\IncomingTransactionPolicy;
use App\Policies\OutgoingTransactionPolicy;
use App\Policies\ProductPolicy;
use App\Policies\RestockOrderPolicy;
use App\Policies\UnitPolicy;
use App\Policies\SupplierPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{

    protected $policies = [
        Product::class             => ProductPolicy::class,
        Category::class            => CategoryPolicy::class,
        Supplier::class            => SupplierPolicy::class,
        IncomingTransaction::class => IncomingTransactionPolicy::class,
        OutgoingTransaction::class => OutgoingTransactionPolicy::class,
        RestockOrder::class        => RestockOrderPolicy::class,
        User::class                => UserPolicy::class,
        Unit::class                => UnitPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('manage-users', function (User $user) {
            return $user->can('viewAny', User::class);
        });
    }
}