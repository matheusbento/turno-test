<?php

namespace Database\Factories;

use App\Models\Enums\UserTransactionOperationType;
use App\Models\Enums\UserTransactionStatus;
use App\Models\Enums\UserTransactionType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserTransaction>
 */
class UserTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'amount' => $this->faker->randomFloat(2, 1, 1000),
            'description' => $this->faker->sentence,
            'current_status' => UserTransactionStatus::PENDING,
            'operation' => $this->faker->randomElement([UserTransactionOperationType::CREDIT, UserTransactionOperationType::DEBIT]),
            'type' => UserTransactionType::DEPOSIT,
        ];
    }
}
