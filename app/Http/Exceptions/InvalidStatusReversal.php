<?php

namespace App\Http\Exceptions;

use Exception;

class InvalidStatusReversal extends Exception
{
    protected $message = 'Cannot revert initial status.';
}
