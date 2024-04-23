<?php

namespace Tests;

use App\Models\Enums\UserType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Hash;

abstract class TurnoTestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function createAdminUserAndLogIn(): User
    {
        $loggedUser = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('123qwe'),
            'type' => UserType::ADMIN,
        ]);

        $this->actingAs($loggedUser);

        return $loggedUser;
    }

    protected function createCustomerUserAndLogIn($balance = 0): User
    {
        $loggedUser = User::factory()->create([
            'email' => 'customer@example.com',
            'password' => Hash::make('123qwe'),
            'type' => UserType::CUSTOMER,
            'balance' => $balance,
        ]);

        $this->actingAs($loggedUser);

        return $loggedUser;
    }
}
