<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Entry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        session(['admin_authenticated' => true]);
    }

    public function test_account_index_lists_accounts(): void
    {
        Account::factory()->create(['name' => 'HDFC Main']);

        $this->get(route('admin.accounts.index'))
            ->assertOk()
            ->assertSee('HDFC Main');
    }

    public function test_account_can_be_created(): void
    {
        $this->post(route('admin.accounts.store'), [
            'name' => 'SBI RD Account',
            'type' => 'bank',
            'bank_name' => 'State Bank of India',
            'branch' => 'Jayanagar',
            'account_number' => '123456789',
            'ifsc' => 'SBIN0001234',
            'investment_type' => 'rd',
            'opening_balance' => 1000.50,
            'is_active' => 1,
        ])->assertRedirect(route('admin.accounts.index'));

        $this->assertDatabaseHas('accounts', [
            'name' => 'SBI RD Account',
            'investment_type' => 'rd',
            'opening_balance' => 1000.50,
        ]);
    }

    public function test_account_can_be_updated(): void
    {
        $account = Account::factory()->create(['name' => 'Old Name']);

        $this->put(route('admin.accounts.update', $account), [
            'name' => 'New Name',
            'type' => 'bank',
            'investment_type' => 'mf',
            'opening_balance' => 500,
            'is_active' => 1,
        ])->assertRedirect(route('admin.accounts.index'));

        $this->assertDatabaseHas('accounts', ['id' => $account->id, 'name' => 'New Name', 'investment_type' => 'mf']);
    }

    public function test_account_can_be_deleted(): void
    {
        $account = Account::factory()->create();

        $this->delete(route('admin.accounts.destroy', $account))
            ->assertRedirect(route('admin.accounts.index'));

        $this->assertDatabaseMissing('accounts', ['id' => $account->id]);
    }

    public function test_balance_computes_opening_plus_income_minus_expense(): void
    {
        $account = Account::factory()->create(['opening_balance' => 1000]);

        Entry::create([
            'entry_type' => Entry::TYPE_DONATION_INCOME,
            'entry_date' => now(),
            'account_id' => $account->id,
            'amount' => 500,
        ]);
        Entry::create([
            'entry_type' => Entry::TYPE_BANK_CHARGES,
            'entry_date' => now(),
            'account_id' => $account->id,
            'amount' => 100,
        ]);

        $this->assertSame(1400.0, $account->balance());
    }

    public function test_balance_handles_internal_transfers(): void
    {
        $from = Account::factory()->create(['opening_balance' => 0]);
        $to = Account::factory()->create(['opening_balance' => 0]);

        Entry::create([
            'entry_type' => Entry::TYPE_BANK_TRANSFER,
            'entry_date' => now(),
            'account_id' => $from->id,
            'to_account_id' => $to->id,
            'amount' => 2000,
        ]);

        $this->assertSame(-2000.0, $from->balance());
        $this->assertSame(2000.0, $to->balance());
    }

    public function test_balance_includes_maturity_interest_and_charges(): void
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

        // principal + interest − charges = 108,380
        $this->assertSame(108380.0, $account->balance());
    }
}
