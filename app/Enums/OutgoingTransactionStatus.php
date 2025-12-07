<?php

namespace App\Enums;

enum OutgoingTransactionStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case SHIPPED = 'shipped';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::APPROVED => 'Approved',
            self::SHIPPED => 'Shipped',
        };
    }

    public static function values(): array
    {
        return array_map(static fn (self $status) => $status->value, self::cases());
    }
}
