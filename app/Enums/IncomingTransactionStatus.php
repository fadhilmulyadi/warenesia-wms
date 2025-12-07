<?php

namespace App\Enums;

enum IncomingTransactionStatus: string
{
    case PENDING = 'pending';
    case VERIFIED = 'verified';
    case COMPLETED = 'completed';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::VERIFIED => 'Verified',
            self::COMPLETED => 'Completed',
            self::REJECTED => 'Rejected',
        };
    }

    public static function values(): array
    {
        return array_map(static fn (self $status) => $status->value, self::cases());
    }
}
