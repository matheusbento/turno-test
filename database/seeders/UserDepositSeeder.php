<?php

namespace Database\Seeders;

use App\Models\Enums\UserDepositStatus;
use App\Models\User;
use App\Models\UserDeposit;
use App\Models\UserDepositCheck;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;

class UserDepositSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(User $user, int $amount = 5): void
    {
        $file = UploadedFile::fake()->image('img.png');
        $uploadedDocument = UserDepositCheck::upload($file, "user-deposits/{$user->id}/checks", UserDepositCheck::PUBLIC_DISK);

        $balance = 0;
        for ($i = 0; $i < $amount; $i++) {
            $deposit = UserDeposit::factory()->create([
                'user_id' => $user->id,
                'current_status' => UserDepositStatus::APPROVED,
            ]);

            $file = UserDepositCheck::create(
                array_merge($uploadedDocument, [
                    'owner_type' => UserDeposit::class,
                    'owner_id' => $deposit->id,
                    'created_by_user_id' => $user->id,
                    'file_type' => UserDepositCheck::FILE_TYPE,
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
