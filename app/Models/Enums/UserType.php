<?php

namespace App\Models\Enums;

enum UserType: string
{
    case CUSTOMER = 'customer';
    case ADMIN = 'admin';

    public function getName(): string
    {
        return match ($this) {
            self::CUSTOMER => 'Customer',
            self::ADMIN => 'Admin',
        };
    }
}
