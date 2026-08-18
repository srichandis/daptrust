<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Entry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        session(['admin_authenticated' => true]);
    }

    public function test_dashboard_renders_accounts_and_summary_cards(): void
    {
        $rd1 = Account::factory()->create(['name' => 'Bank Account 1 (RD)', 'investment_type' => 'rd']);
        Account::factory()->create(['name' => 'Cash Account', 'type' => 'cash']);

        Entry::create([
            'entry_type' => Entry::TYPE_DONATION_INCOME,
            'entry_date' => now(),
            'account_id' => $rd1->id,
            'amount' => 25000,
        ]);

        $response = $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Account Balances')
            ->assertSee('Bank Account 1 (RD)')
            ->assertSee('Cash Account')
            ->assertSee('Total Income')
            ->assertSee('Income by Type')
            ->assertSee('Expenses by Type');

        $response->assertSee('25,000.00');
    }

    public function test_dashboard_shows_maturity_interest_totals(): void
    {
        $account = Account::factory()->create(['opening_balance' => 0]);

        Entry::create([
            'entry_type' => Entry::TYPE_FD_MATURITY,
            'entry_date' => now(),
            'account_id' => $account->id,
            'amount' => 100000,
            'interest_amount' => 8500,
            'bank_charges_amount' => 120,
        ]);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('108,380.00');
    }
}
