<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Entry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EntryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        session(['admin_authenticated' => true]);
    }

    public function test_entry_index_renders(): void
    {
        $this->get(route('admin.entries.index'))->assertOk()->assertSee('Entry type');
    }

    public function test_donation_income_creates_with_donor_fields(): void
    {
        $account = Account::factory()->create();

        $this->post(route('admin.entries.store'), [
            'entry_type' => Entry::TYPE_DONATION_INCOME,
            'entry_date' => '2026-08-01',
            'account_id' => $account->id,
            'amount' => 25000,
            'particulars' => 'General donation',
            'donor_name' => 'Ramesh Kumar',
            'donor_address' => 'Bangalore',
            'nature_of_donation' => 'General',
            'mode_of_receipt' => 'Bank transfer',
            'section_code' => '80G',
            'donor_pan' => 'ABCDE1234F',
            'donor_bank_details' => 'HDFC 0011',
            'receipt_number' => 'DAP/2026/0001',
        ])->assertRedirect(route('admin.entries.index'));

        $this->assertDatabaseHas('entries', [
            'entry_type' => Entry::TYPE_DONATION_INCOME,
            'donor_name' => 'Ramesh Kumar',
            'receipt_number' => 'DAP/2026/0001',
            'amount' => 25000,
        ]);
    }

    public function test_non_donation_entries_discard_donor_fields(): void
    {
        $account = Account::factory()->create();

        $this->post(route('admin.entries.store'), [
            'entry_type' => Entry::TYPE_BANK_INTEREST,
            'entry_date' => '2026-08-01',
            'account_id' => $account->id,
            'amount' => 500,
            'donor_name' => 'Should Not Persist',
            'receipt_number' => 'X',
        ])->assertRedirect(route('admin.entries.index'));

        $entry = Entry::first();
        $this->assertNull($entry->donor_name);
        $this->assertNull($entry->receipt_number);
    }

    public function test_maturity_entry_records_interest_and_charges(): void
    {
        $account = Account::factory()->create();

        $this->post(route('admin.entries.store'), [
            'entry_type' => Entry::TYPE_FD_MATURITY,
            'entry_date' => '2026-08-05',
            'account_id' => $account->id,
            'amount' => 100000,
            'scheme_name' => 'SBI FD 5yr',
            'interest_amount' => 8500,
            'bank_charges_amount' => 120,
        ])->assertRedirect(route('admin.entries.index'));

        $this->assertDatabaseHas('entries', [
            'entry_type' => Entry::TYPE_FD_MATURITY,
            'interest_amount' => 8500,
            'bank_charges_amount' => 120,
        ]);
    }

    public function test_transfer_entry_requires_from_and_to(): void
    {
        $from = Account::factory()->create();
        $to = Account::factory()->create();

        $this->post(route('admin.entries.store'), [
            'entry_type' => Entry::TYPE_BANK_TRANSFER,
            'entry_date' => '2026-08-03',
            'account_id' => $from->id,
            'to_account_id' => $to->id,
            'amount' => 5000,
        ])->assertRedirect(route('admin.entries.index'));

        $this->assertDatabaseHas('entries', ['entry_type' => Entry::TYPE_BANK_TRANSFER, 'to_account_id' => $to->id]);
    }

    public function test_transfer_cannot_target_same_account(): void
    {
        $account = Account::factory()->create();

        $this->post(route('admin.entries.store'), [
            'entry_type' => Entry::TYPE_BANK_TRANSFER,
            'entry_date' => '2026-08-03',
            'account_id' => $account->id,
            'to_account_id' => $account->id,
            'amount' => 5000,
        ])->assertSessionHasErrors('to_account_id');
    }

    public function test_entry_can_be_updated(): void
    {
        $entry = Entry::create([
            'entry_type' => Entry::TYPE_BANK_CHARGES,
            'entry_date' => now(),
            'amount' => 50,
        ]);

        $this->put(route('admin.entries.update', $entry), [
            'entry_type' => Entry::TYPE_BANK_CHARGES,
            'entry_date' => '2026-08-10',
            'amount' => 75,
            'particulars' => 'Statement charges',
        ])->assertRedirect(route('admin.entries.index'));

        $this->assertDatabaseHas('entries', ['id' => $entry->id, 'amount' => 75, 'particulars' => 'Statement charges']);
    }

    public function test_entry_can_be_deleted(): void
    {
        $entry = Entry::create([
            'entry_type' => Entry::TYPE_IT_PAID,
            'entry_date' => now(),
            'amount' => 12000,
        ]);

        $this->delete(route('admin.entries.destroy', $entry))->assertRedirect(route('admin.entries.index'));
        $this->assertDatabaseMissing('entries', ['id' => $entry->id]);
    }

    public function test_entries_can_be_filtered_by_type(): void
    {
        Entry::create(['entry_type' => Entry::TYPE_DONATION_INCOME, 'entry_date' => now(), 'amount' => 100]);
        Entry::create(['entry_type' => Entry::TYPE_BANK_CHARGES, 'entry_date' => now(), 'amount' => 50]);

        $this->get(route('admin.entries.index', ['entry_type' => Entry::TYPE_DONATION_INCOME]))
            ->assertOk()
            ->assertSee('Donation Income');
    }

    public function test_entry_gets_financial_year_tagged_from_date(): void
    {
        $account = Account::factory()->create();

        $this->post(route('admin.entries.store'), [
            'entry_type' => Entry::TYPE_DONATION_INCOME,
            'entry_date' => '2025-01-15', // Jan 2025 -> FY24 (Apr 2024 – Mar 2025)
            'account_id' => $account->id,
            'amount' => 100,
        ])->assertRedirect(route('admin.entries.index'));

        $this->assertDatabaseHas('entries', ['financial_year' => 'FY24']);

        $this->post(route('admin.entries.store'), [
            'entry_type' => Entry::TYPE_DONATION_INCOME,
            'entry_date' => '2025-04-02', // Apr 2025 -> FY25
            'account_id' => $account->id,
            'amount' => 200,
        ])->assertRedirect(route('admin.entries.index'));

        $this->assertDatabaseHas('entries', ['financial_year' => 'FY25']);
    }

    public function test_entries_can_be_filtered_by_financial_year(): void
    {
        Entry::create(['entry_type' => Entry::TYPE_DONATION_INCOME, 'entry_date' => '2025-01-15', 'amount' => 100, 'financial_year' => 'FY24']);
        Entry::create(['entry_type' => Entry::TYPE_DONATION_INCOME, 'entry_date' => '2025-04-02', 'amount' => 200, 'financial_year' => 'FY25']);

        $this->get(route('admin.entries.index', ['financial_year' => 'FY25']))
            ->assertOk()
            ->assertSee('FY25');

        $this->assertSame(1, Entry::where('financial_year', 'FY25')->count());
        $this->assertSame(0, Entry::where('financial_year', 'FY26')->count());
    }

    public function test_csv_export_returns_download(): void
    {
        Entry::create(['entry_type' => Entry::TYPE_DONATION_INCOME, 'entry_date' => now(), 'amount' => 100, 'donor_name' => 'ABC']);

        $response = $this->get(route('admin.entries.export'));
        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Donation Income', $response->streamedContent());
        $this->assertStringContainsString('ABC', $response->streamedContent());
    }
}
