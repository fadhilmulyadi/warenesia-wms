<?php

namespace App\Support;

use App\Support\MobileIndex\CategoryConfig;
use App\Support\MobileIndex\ProductConfig;
use App\Support\MobileIndex\PurchaseConfig;
use App\Support\MobileIndex\RestockConfig;
use App\Support\MobileIndex\SaleConfig;
use App\Support\MobileIndex\SupplierConfig;
use App\Support\MobileIndex\UnitConfig;
use App\Support\MobileIndex\UserConfig;
use App\Support\MobileIndex\SupplierRestockConfig;

class MobileIndexConfig
{
    public static function users(array $roles, array $statuses): array
    {
        return UserConfig::config($roles, $statuses);
    }

    public static function products($categories): array
    {
        return ProductConfig::config($categories);
    }

    public static function suppliers(): array
    {
        return SupplierConfig::config();
    }

    public static function restocks($statusOptions): array
    {
        return RestockConfig::config($statusOptions);
    }

    public static function purchases($statusOptions): array
    {
        return PurchaseConfig::config($statusOptions);
    }

    public static function sales($statusOptions): array
    {
        return SaleConfig::config($statusOptions);
    }

    public static function categories(): array
    {
        return CategoryConfig::config();
    }

    public static function units(): array
    {
        return UnitConfig::config();
    }

    public static function supplierRestocks($statusOptions): array
    {
        return SupplierRestockConfig::config($statusOptions);
    }
}