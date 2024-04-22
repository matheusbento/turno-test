<?php

namespace App\Models\Enums;

enum UserTransactionStatus: string implements ModelStatusInterface
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    public function description(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
        };
    }

    public function isPrimary(): bool
    {
        return match ($this) {
            self::PENDING => true,
            self::APPROVED => true,
            self::REJECTED => true,
        };
    }

    public function isSecondary(): bool
    {
        return match ($this) {
            self::PENDING => false,
            self::APPROVED => false,
            self::REJECTED => false,
        };
    }
}
