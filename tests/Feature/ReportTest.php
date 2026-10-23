<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Entry;
use App\Services\TrustSummary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        session(['admin_authenticated' => true]);
    }

    private function seedLedger(): void
    {
        $sbi = Account::factory()->create([
            'name' => 'SBI',
            'type' => 'bank',
            'investment_type' => 'none',
        ]);

        Entry::create([
            'entry_type' => Entry::TYPE_DONATION_INCOME,
            'entry_date' => '2026-01-10',
            'account_id' => $sbi->id,
            'amount' => 15000,
            'donor_name' => 'Ramesh',
            'donor_address' => 'Chennai',
            'donor_pan' => 'ABCDE1234F',
            'mode_of_receipt' => 'Bank transfer',
            'section_code' => '80G',
            'receipt_number' => 'DAP-001',
        ]);

        Entry::create([
            'entry_type' => Entry::TYPE_BANK_INTEREST,
            'entry_date' => '2026-01-20',
            'account_id' => $sbi->id,
            'amount' => 800,
        ]);

        Entry::create([
            'entry_type' => Entry::TYPE_BANK_CHARGES,
            'entry_date' => '2026-01-24',
            'account_id' => $sbi->id,
            'amount' => 250,
        ]);

        Entry::create([
            'entry_type' => Entry::TYPE_FD_INVESTMENT,
            'entry_date' => '2026-02-15',
            'account_id' => $sbi->id,
            'amount' => 50000,
            'scheme_name' => 'FD',
        ]);

        Entry::create([
            'entry_type' => Entry::TYPE_FD_MATURITY,
            'entry_date' => '2026-03-31',
            'account_id' => $sbi->id,
            'amount' => 20000,
            'interest_amount' => 1500,
            'scheme_name' => 'FD',
        ]);

        Entry::create([
            'entry_type' => Entry::TYPE_DONATION_EXPENSE,
            'entry_date' => '2026-02-02',
            'account_id' => $sbi->id,
            'amount' => 3000,
            'particulars' => 'DON TO CHENNAI',
        ]);

        Entry::create([
            'entry_type' => Entry::TYPE_DONATION_EXPENSE,
            'entry_date' => '2026-02-10',
            'account_id' => $sbi->id,
            'amount' => 7000,
            'particulars' => 'DON TO CHENNAI',
        ]);
    }

    private function report(string $key, array $params = []): \Illuminate\Testing\TestResponse
    {
        return $this->get(route('admin.reports.index', array_merge(['report' => $key], $params)));
    }

    public function test_trust_summary_sums_fd_rd_mf_interest_columns(): void
    {
        $account = Account::factory()->create(['opening_balance' => 0]);

        foreach ([Entry::TYPE_FD_INTEREST => 1000.50, Entry::TYPE_RD_INTEREST => 2000.25, Entry::TYPE_MF_INTEREST => 3000.00] as $type => $amount) {
            Entry::create([
                'entry_type' => $type,
                'entry_date' => '2026-02-01',
                'account_id' => $account->id,
                'amount' => $amount,
            ]);
        }

        // Maturity interest lives in its own column and is not part of row 2537.
        Entry::create([
            'entry_type' => Entry::TYPE_FD_MATURITY,
            'entry_date' => '2026-03-31',
            'account_id' => $account->id,
            'amount' => 50000,
            'interest_amount' => 1500,
        ]);

        $lines = (new TrustSummary())->lines();

        $this->assertSame(6000.75, $lines['FD/RD/MF INTEREST']);
    }

    public function test_trust_summary_bank_balance_sums_account_closing_balances(): void
    {
        $cash = Account::factory()->create(['name' => 'Cash', 'type' => 'cash', 'opening_balance' => 250.50]);
        $bank = Account::factory()->create(['name' => 'SBI', 'type' => 'bank', 'opening_balance' => 1000]);

        Entry::create([
            'entry_type' => Entry::TYPE_DONATION_INCOME,
            'entry_date' => '2026-02-01',
            'account_id' => $bank->id,
            'amount' => 500,
        ]);

        Entry::create([
            'entry_type' => Entry::TYPE_BANK_CHARGES,
            'entry_date' => '2026-02-02',
            'account_id' => $bank->id,
            'amount' => 100,
        ]);

        Entry::create([
            'entry_type' => Entry::TYPE_DONATION_INCOME,
            'entry_date' => '2026-02-03',
            'account_id' => $cash->id,
            'amount' => 99.50,
        ]);

        // Cash 350.00 + SBI 1400.00
        $this->assertSame(1750.0, (new TrustSummary())->lines()['BANK BALANCE']);
    }

    public function test_master_report_renders_period_income_expense_and_balances(): void
    {
        $this->seedLedger();

        $response = $this->report('master', ['from' => '2026-01-01', 'to' => '2026-01-31']);

        $response->assertOk();
        $response->assertSee('Master Report');
        $response->assertSee('Donation Receipts');
        $response->assertSee('Bank Charges');
        $response->assertSee('Bank Balance');
    }

    public function test_snapshot_report_shows_income_expense_savings_totals(): void
    {
        $this->seedLedger();

        $response = $this->report('snapshot', ['from' => '2026-01-01', 'to' => '2026-01-31']);

        $response->assertOk()
            ->assertSee('Total income')
            ->assertSee('₹15,800.00')
            ->assertSee('Total expenses')
            ->assertSee('₹250.00');
    }

    public function test_donor_report_renders_donation_fields(): void
    {
        $this->seedLedger();

        $this->report('donors', ['from' => '2026-01-01', 'to' => '2026-01-31'])
            ->assertOk()
            ->assertSee('Ramesh')
            ->assertSee('ABCDE1234F')
            ->assertSee('DAP-001');
    }

    public function test_fd_details_report_renders_investments_and_maturities(): void
    {
        $this->seedLedger();

        $this->report('fd-details', ['from' => '2026-01-01', 'to' => '2026-03-31'])
            ->assertOk()
            ->assertSee('FD investments')
            ->assertSee('FD maturities')
            ->assertSee('₹50,000.00');
    }

    public function test_statement_report_renders_running_balance(): void
    {
        $this->seedLedger();

        $response = $this->report('statement', ['from' => '2026-01-01', 'to' => '2026-01-31']);

        $response->assertOk()
            ->assertSee('Opening')
            ->assertSee('Closing — SBI');
    }

    public function test_distribution_report_groups_donation_expenses_by_recipient(): void
    {
        $this->seedLedger();

        $response = $this->report('distribution', ['from' => '2026-01-01', 'to' => '2026-03-31']);

        $response->assertOk()
            ->assertSee('DON TO CHENNAI')
            ->assertSee('₹10,000.00');
    }

    public function test_year_end_report_lists_march_31_entries(): void
    {
        $this->seedLedger();

        $response = $this->report('year-end', ['from' => '2026-01-01', 'to' => '2026-03-31']);

        $response->assertOk()
            ->assertSee('FD Maturity')
            ->assertSee('₹20,000.00');
    }

    public function test_unknown_report_falls_back_to_master(): void
    {
        $this->seedLedger();

        $this->report('does-not-exist')->assertOk()->assertSee('Master Report');
    }
}
