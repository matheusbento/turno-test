<?php

namespace Database\Seeders;

use App\Models\Enums\UserTransactionOperationType;
use App\Models\Enums\UserTransactionType;
use App\Models\User;
use App\Models\UserTransaction;
use Illuminate\Database\Seeder;

class UserTransactionPurchaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(User $user): void
    {
        UserTransaction::factory()->create([
            'user_id' => $user->id,
            'operation' => UserTransactionOperationType::DEBIT,
            'type' => UserTransactionType::PURCHASE,
            'amount' => 0.01,
            'description' => 'Test purchase',
        ]);
    }
}
