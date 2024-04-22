<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class UserDepositCheck extends File
{
    use SoftDeletes;

    public const FILE_TYPE = 'user_deposit_check';
    public const ACCEPTABLE_FILE_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    protected $table = 'files';
}
