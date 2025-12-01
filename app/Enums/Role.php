<?php

namespace App\Enums;

enum Role: string
{
    case ADMIN = 'admin';
    case MANAGER = 'manager';
    case STAFF = 'staff';
    case SUPPLIER = 'supplier';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Admin',
            self::MANAGER => 'Warehouse Manager',
            self::STAFF => 'Staff Gudang',
            self::SUPPLIER => 'Supplier',
        };
    }

    public static function values(): array
    {
        return array_map(fn (self $role) => $role->value, self::cases());
    }
}