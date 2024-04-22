<?php

namespace App\Models\Enums;

enum UserTransactionOperationType: string
{
    case DEBIT = 'debit';
    case CREDIT = 'credit';
}
