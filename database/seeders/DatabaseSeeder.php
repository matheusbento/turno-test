<?php

namespace Database\Seeders;

use App\Models\Enums\UserType;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::where('email', 'admin@example.com')->update([
            'email' => fake()->unique()->safeEmail(),
        ]);
        User::where('email', 'customer@example.com')->update([
            'email' => fake()->unique()->safeEmail(),
        ]);
        // User::factory(10)->create();

        $customer = User::factory()->create([
            'name' => 'customer',
            'email' => 'customer@example.com',
        ]);

        echo '> Seeding new Customer: ' . $customer->email . "\n";

        $admin = User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'type' => UserType::ADMIN,
        ]);

        echo '> Seeding new Admin: ' . $admin->email . "\n";

        $this->call(UserTransactionDepositSeeder::class, false, [
            'user' => $customer,
        ]);

        $this->call(UserTransactionPurchaseSeeder::class, false, [
            'user' => $customer,
        ]);
    }
}
