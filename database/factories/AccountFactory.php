<?php

namespace Database\Factories;

use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company().' Account',
            'type' => 'bank',
            'bank_name' => fake()->company(),
            'branch' => fake()->city(),
            'account_number' => fake()->numerify('############'),
            'ifsc' => strtoupper(fake()->lexify('????').'0'.fake()->numerify('######')),
            'investment_type' => fake()->randomElement(['rd', 'mf', 'none']),
            'opening_balance' => fake()->randomFloat(2, 0, 100000),
            'is_active' => true,
        ];
    }
}
