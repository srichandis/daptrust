<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Entry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransparencyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        session(['admin_authenticated' => true]);
    }

    public function test_transparency_pages_are_guarded_for_guests(): void
    {
        session()->forget('admin_authenticated');

        $this->get(route('admin.transparency.financial-report'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_index_redirects_to_financial_report(): void
    {
        $this->get(route('admin.transparency.index'))
            ->assertRedirect(route('admin.transparency.financial-report'));
    }

    public function test_financial_report_renders_live_totals(): void
    {
        $account = Account::factory()->create(['opening_balance' => 0]);

        Entry::create([
            'entry_type' => Entry::TYPE_DONATION_INCOME,
            'entry_date' => now(),
            'account_id' => $account->id,
            'amount' => 25000,
            'donor_name' => 'TEST DONOR',
        ]);

        $this->get(route('admin.transparency.financial-report'))
            ->assertOk()
            ->assertSee('INCOME')
            ->assertSee('EXPENSE')
            ->assertSee('Income by Type')
            ->assertSee('25,000.00')
            ->assertSee('About & Governance');
    }

    public function test_donors_page_lists_top_donors(): void
    {
        $account = Account::factory()->create();

        Entry::create([
            'entry_type' => Entry::TYPE_DONATION_INCOME,
            'entry_date' => now(),
            'account_id' => $account->id,
            'amount' => 5000,
            'donor_name' => 'RAMANUJAM',
        ]);

        $this->get(route('admin.transparency.donors'))
            ->assertOk()
            ->assertSee('RAMANUJAM')
            ->assertSee('Top Donors');
    }

    public function test_trend_page_renders_series(): void
    {
        $account = Account::factory()->create();

        Entry::create([
            'entry_type' => Entry::TYPE_DONATION_INCOME,
            'entry_date' => '2025-01-15', // FY24
            'financial_year' => 'FY24',
            'account_id' => $account->id,
            'amount' => 100,
        ]);

        $this->get(route('admin.transparency.income-trend'))
            ->assertOk()
            ->assertSee('FY24');
    }
}
