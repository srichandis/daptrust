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
            ->assertSee('INCOME')
            ->assertSee('EXPENSE')
            ->assertSee('SAVINGS')
            ->assertSee('Income by Type')
            ->assertSee('Expenses by Type');

        $response->assertSee('25,000.00');
    }

    public function test_income_by_type_shows_one_combined_interest_line(): void
    {
        $account = Account::factory()->create(['opening_balance' => 0]);

        foreach ([Entry::TYPE_FD_INTEREST => 1000, Entry::TYPE_RD_INTEREST => 2000, Entry::TYPE_MF_INTEREST => 3000] as $type => $amount) {
            Entry::create([
                'entry_type' => $type,
                'entry_date' => now(),
                'account_id' => $account->id,
                'amount' => $amount,
            ]);
        }

        $html = $this->get(route('admin.dashboard'))->assertOk()->getContent();

        // The Income by Type card runs from its heading to the next card.
        $start = strpos($html, 'Income by Type');
        $end = strpos($html, 'Expenses by Type');
        $card = substr($html, $start, $end - $start);

        $this->assertStringContainsString('FD/RD/MF INTEREST', $card);
        $this->assertStringContainsString('6,000.00', $card);
        $this->assertStringNotContainsString('FD Interest', $card);
        $this->assertStringNotContainsString('MF Interest', $card);
        $this->assertStringNotContainsString('RD Interest', $card);
    }

    public function test_income_by_type_includes_net_fd_mf_rd(): void
    {
        $account = Account::factory()->create(['opening_balance' => 0]);

        foreach ([Entry::TYPE_FD_INVESTMENT => 50000, Entry::TYPE_RD_INVESTMENT => 10000, Entry::TYPE_MF_INVESTMENT => 5000] as $type => $amount) {
            Entry::create([
                'entry_type' => $type,
                'entry_date' => now(),
                'account_id' => $account->id,
                'amount' => $amount,
            ]);
        }

        Entry::create([
            'entry_type' => Entry::TYPE_FD_MATURITY,
            'entry_date' => now(),
            'account_id' => $account->id,
            'amount' => 20000,
        ]);

        $html = $this->get(route('admin.dashboard'))->assertOk()->getContent();
        $card = substr($html, strpos($html, 'Income by Type'), 4000);
        $card = substr($card, 0, strpos($card, 'Expenses by Type'));

        $this->assertStringContainsString('FD + MF + RD', $card);
        // 50000 + 10000 + 5000 invested, less 20000 matured.
        $this->assertStringContainsString('45,000.00', $card);
        $this->assertStringContainsString('text-emerald-600">₹45,000.00', $card);
    }

    public function test_income_by_type_shows_donations_to_ops_below_net_investments(): void
    {
        $account = Account::factory()->create(['opening_balance' => 0]);

        Entry::create([
            'entry_type' => Entry::TYPE_DONATION_TO_OPS,
            'entry_date' => now(),
            'account_id' => $account->id,
            'amount' => 4000,
            'particulars' => 'DON TO OPS SAIVA PERAVAI',
        ]);

        $html = $this->get(route('admin.dashboard'))->assertOk()->getContent();
        $card = substr($html, strpos($html, 'Income by Type'), 4000);
        $card = substr($card, 0, strpos($card, 'Expenses by Type'));

        $this->assertStringContainsString('DON TO OPS', $card);
        $this->assertStringContainsString('4,000.00', $card);
        $this->assertStringContainsString('text-emerald-600">₹4,000.00', $card);
        $this->assertGreaterThan(
            strpos($card, 'FD + MF + RD'),
            strpos($card, 'DON TO OPS'),
            'DON TO OPS should sit below FD + MF + RD.',
        );
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
            ->assertSee('1,08,380.00');
    }
}
