<?php

namespace App\Providers;

use App\Models\UserDeposit;
use App\Policies\UserDepositPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        UserDeposit::class => UserDepositPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
