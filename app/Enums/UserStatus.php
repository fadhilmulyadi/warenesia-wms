<?php

namespace App\Enums;

enum UserStatus: string
{
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';
    case PENDING = 'pending';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::SUSPENDED => 'Suspended',
            self::PENDING => 'Pending Approval',
        };
    }

    public static function values(): array
    {
        return array_map(fn (self $status) => $status->value, self::cases());
    }
}
