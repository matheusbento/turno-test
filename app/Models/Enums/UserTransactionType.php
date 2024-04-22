<?php

namespace App\Models\Enums;

enum UserTransactionType: string
{
    case DEPOSIT = 'deposit';
    case PURCHASE = 'purchase';
}
