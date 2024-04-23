<?php

namespace Tests\Unit;

use Tests\TurnoTestCase;

class UserPurchaseTest extends TurnoTestCase
{
    public function testUserCanPurchase()
    {
        $user = $this->createCustomerUserAndLogIn(100);

        $response = $this->postJson('/api/v1/purchases', [
            'amount' => 100,
            'description' => 'TEST',
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('user_transactions', [
            'user_id' => $user->id,
            'amount' => 100,
            'description' => 'TEST',
        ]);

        $response->assertJsonPath('data.amount', 100);
        $response->assertJsonPath('data.description', 'TEST');
    }

    public function testUserCanNotPurchaseWithInvalidAmount()
    {
        $user = $this->createCustomerUserAndLogIn();

        $response = $this->postJson('/api/v1/purchases', [
            'amount' => -100,
            'description' => 'TEST',
        ]);

        $response->assertStatus(422);
    }

    public function testUserCanNotPurchaseWithInsufficientBalance()
    {
        $user = $this->createCustomerUserAndLogIn(100);

        $response = $this->postJson('/api/v1/purchases', [
            'amount' => 200,
            'description' => 'TEST',
        ]);

        $response->assertStatus(422);
    }

    public function testUserCanNotPurchaseWithInvalidDescription()
    {
        $user = $this->createCustomerUserAndLogIn();

        $response = $this->postJson('/api/v1/purchases', [
            'amount' => 100,
            'description' => '',
        ]);

        $response->assertStatus(422);
    }
}
