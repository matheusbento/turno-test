<?php

namespace App\Providers;

use App\Models\UserTransaction;
use App\Policies\UserTransactionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        UserTransaction::class => UserTransactionPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
