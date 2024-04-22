<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserPurchase;
use Illuminate\Database\Seeder;

class UserPurchaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(User $user): void
    {
        UserPurchase::factory()->create([
            'user_id' => $user->id,
            'amount' => 0.01,
            'description' => 'Test purchase',
        ]);
    }
}
