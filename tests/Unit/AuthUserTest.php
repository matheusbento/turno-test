<?php

namespace Tests\Unit;

use App\Models\Enums\UserType;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TurnoTestCase;

class AuthUserTest extends TurnoTestCase
{
    public function testUserCanLogin()
    {
        $user = User::factory()->create([
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
            'type' => UserType::ADMIN,
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'admin@admin.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200);
    }

    public function testUserCanLogout()
    {
        $user = $this->createAdminUserAndLogIn();

        $response = $this->postJson('/api/v1/logout');

        $response->assertStatus(200);
    }

    public function testUserCanRegister()
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        $user = User::where('email', 'test@example.com')->first();

        $this->assertNotNull($user);
        $this->assertEquals('test', $user->name);
        $this->assertEquals(UserType::CUSTOMER, $user->type);
    }

    public function testUserTryCreateDuplicatedUser()
    {
        $response = $this->postJson('/api/v1/register', [
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response = $this->postJson('/api/v1/register', [
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonPath('errors.email.0', 'The email has already been taken.');
    }

    public function testGetLoggedInfo()
    {
        $user = $this->createAdminUserAndLogIn();

        $response = $this->getJson('/api/v1/me');

        $response->assertStatus(200);

        $response->assertJsonPath('user.id', $user->id);
        $response->assertJsonPath('user.name', $user->name);
        $response->assertJsonPath('user.email', $user->email);
        $response->assertJsonPath('user.type', $user->type->value);
    }
}
