<?php

namespace Database\Seeders;

use App\Models\Enums\UserTransactionOperationType;
use App\Models\Enums\UserTransactionStatus;
use App\Models\Enums\UserTransactionType;
use App\Models\User;
use App\Models\UserTransaction;
use App\Models\UserTransactionCheck;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;

class UserTransactionDepositSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(User $user, int $amount = 5): void
    {
        $file = UploadedFile::fake()->image('img.png');
        $uploadedDocument = UserTransactionCheck::upload($file, "user-transactions/{$user->id}/checks", UserTransactionCheck::PUBLIC_DISK);

        $balance = 0;
        for ($i = 0; $i < $amount; $i++) {
            $deposit = UserTransaction::factory()->create([
                'user_id' => $user->id,
                'current_status' => UserTransactionStatus::APPROVED,
                'operation' => UserTransactionOperationType::CREDIT,
                'type' => UserTransactionType::DEPOSIT,
            ]);

            $file = UserTransactionCheck::create(
                array_merge($uploadedDocument, [
                    'owner_type' => UserTransaction::class,
                    'owner_id' => $deposit->id,
                    'created_by_user_id' => $user->id,
                    'file_type' => UserTransactionCheck::FILE_TYPE,
                ])
            );

            $deposit->update([
                'file_id' => $file->id,
            ]);

            $balance += $deposit->amount;
        }

        $user->update([
            'balance' => $balance,
        ]);

    }
}
