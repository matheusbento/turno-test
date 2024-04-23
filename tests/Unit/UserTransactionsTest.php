<?php

namespace Tests\Unit;

use App\Models\Enums\UserTransactionStatus;
use App\Models\UserTransaction;
use Tests\TurnoTestCase;

class UserTransactionsTest extends TurnoTestCase
{
    public function testUserCanGetTransactions()
    {
        $user = $this->createCustomerUserAndLogIn(100);

        $transactions = UserTransaction::factory()->count(5)->create([
            'user_id' => $user->id,
            'description' => 'TEST',
        ]);
        $response = $this->getJson('/api/v1/transactions');
        $response->assertOk();

        $response->assertJsonCount(5, 'data');
    }

    public function testAdminUserGetOnlyPendingTransactions()
    {
        $user = $this->createAdminUserAndLogIn(100);

        $transactions = UserTransaction::factory()->count(5)->create([
            'description' => 'TEST',
        ]);

        $approved = UserTransaction::factory()->count(5)->create([
            'description' => 'TEST',
            'current_status' => UserTransactionStatus::APPROVED,
        ]);

        $response = $this->getJson('/api/v1/transactions');
        $response->assertOk();

        $response->assertJsonCount(5, 'data');
    }
}
