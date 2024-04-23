<?php

namespace Tests\Unit;

use App\Models\UserTransaction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TurnoTestCase;

class UserDepositTest extends TurnoTestCase
{
    public function testUserCanCreateDeposit()
    {
        $user = $this->createCustomerUserAndLogIn();

        $fakeFile = UploadedFile::fake()->create('check.png', 100, 'image/png');

        $response = $this->postJson('/api/v1/deposits', [
            'amount' => 100,
            'file' => $fakeFile,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('user_transactions', [
            'user_id' => $user->id,
            'amount' => 100,
            'current_status' => 'pending',
            'type' => 'deposit',
        ]);

        $response->assertJsonPath('data.amount', 100);
        $response->assertJsonPath('data.current_status', 'pending');
        $response->assertJsonPath('data.type', 'deposit');
        $response->assertJsonPath('data.operation', 'credit');
    }

    public function testUserCanNotCreateDepositWithInvalidAmount()
    {
        $user = $this->createCustomerUserAndLogIn();

        $fakeFile = UploadedFile::fake()->create('check.png', 100, 'image/png');

        $response = $this->postJson('/api/v1/deposits', [
            'amount' => -100,
            'file' => $fakeFile,
        ]);

        $response->assertStatus(422);
    }

    public function testUserCanNotCreateDepositWithInvalidFile()
    {
        $user = $this->createCustomerUserAndLogIn();

        $response = $this->postJson('/api/v1/deposits', [
            'amount' => 100,
            'file' => 'invalid',
        ]);

        $response->assertStatus(422);
    }

    public function testAdminUserCanApproveDeposit()
    {
        $admin = $this->createAdminUserAndLogIn();

        $deposit = UserTransaction::factory()->create([
            'type' => 'deposit',
            'current_status' => 'pending',
            'operation' => 'credit',
        ]);

        $response = $this->postJson("/api/v1/deposits/{$deposit->id}/approve");

        $response->assertStatus(200);

        $this->assertDatabaseHas('user_transactions', [
            'id' => $deposit->id,
            'current_status' => 'approved',
        ]);

        $response->assertJsonPath('data.current_status', 'approved');
    }

    public function testAdminUserCanRejectDeposit()
    {
        $admin = $this->createAdminUserAndLogIn();

        $deposit = UserTransaction::factory()->create([
            'type' => 'deposit',
            'current_status' => 'pending',
            'operation' => 'credit',
        ]);

        $response = $this->postJson("/api/v1/deposits/{$deposit->id}/reject");

        $response->assertStatus(200);

        $this->assertDatabaseHas('user_transactions', [
            'id' => $deposit->id,
            'current_status' => 'rejected',
        ]);

        $response->assertJsonPath('data.current_status', 'rejected');
    }

    public function testAdminUserCanSeeCheck()
    {
        Storage::fake('local');

        $user = $this->createCustomerUserAndLogIn();

        $fakeFile = UploadedFile::fake()->create('check.png', 100, 'image/png');

        $response = $this->postJson('/api/v1/deposits', [
            'amount' => 100,
            'file' => $fakeFile,
        ]);

        $id = $response->json('data.id');

        $deposit = UserTransaction::find($id);

        $user = $this->createAdminUserAndLogIn();

        $response = $this->getJson("/api/v1/deposits/{$deposit->id}/check");

        $response->assertStatus(200);
    }
}
