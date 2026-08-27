<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        Account::create([
            'name' => 'Cash Account',
            'type' => 'cash',
            'investment_type' => 'none',
            'opening_balance' => 31200.00,
            'is_active' => true,
        ]);

        Account::create([
            'name' => 'Andhra Bank',
            'type' => 'bank',
            'bank_name' => 'Andhra Bank',
            'investment_type' => 'none',
            'opening_balance' => 2788170.00,
            'is_active' => true,
        ]);

        Account::create([
            'name' => 'State Bank of India',
            'type' => 'bank',
            'bank_name' => 'SBI',
            'investment_type' => 'none',
            'opening_balance' => 699901.00,
            'is_active' => true,
        ]);

        Account::create([
            'name' => 'HDFC Bank',
            'type' => 'bank',
            'bank_name' => 'HDFC',
            'investment_type' => 'none',
            'opening_balance' => 0.00,
            'is_active' => true,
        ]);
    }
}
