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
            'name' => 'Bank Account 1 (RD)',
            'type' => 'bank',
            'bank_name' => '',
            'branch' => '',
            'account_number' => '',
            'ifsc' => '',
            'investment_type' => 'rd',
            'opening_balance' => 0,
            'is_active' => true,
        ]);

        Account::create([
            'name' => 'Bank Account 2 (RD)',
            'type' => 'bank',
            'bank_name' => '',
            'branch' => '',
            'account_number' => '',
            'ifsc' => '',
            'investment_type' => 'rd',
            'opening_balance' => 0,
            'is_active' => true,
        ]);

        Account::create([
            'name' => 'Bank Account 3 (MF)',
            'type' => 'bank',
            'bank_name' => '',
            'branch' => '',
            'account_number' => '',
            'ifsc' => '',
            'investment_type' => 'mf',
            'opening_balance' => 0,
            'is_active' => true,
        ]);

        Account::create([
            'name' => 'Cash Account',
            'type' => 'cash',
            'investment_type' => 'none',
            'opening_balance' => 0,
            'is_active' => true,
        ]);
    }
}
