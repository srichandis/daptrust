<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Unified trust ledger. Each row is one entry; the entry_type discriminator
     * decides which type-specific columns apply (donor fields, expense breakdown,
     * maturity interest/charges, etc.).
     */
    public function up(): void
    {
        Schema::create('entries', function (Blueprint $table) {
            $table->id();

            $table->enum('entry_type', [
                'donation_income',
                'donation_expense',
                'bank_interest',
                'fd_interest',
                'mf_interest',
                'rd_interest',
                'bank_charges',
                'bank_transfer',
                'fd_investment',
                'rd_investment',
                'mf_investment',
                'fd_maturity',
                'rd_maturity',
                'mf_maturity',
                'professional_charges',
                'stationary_charges',
                'courier_charges',
                'it_return',
                'it_paid',
                'bank_balance',
                'data_correction',
            ]);

            $table->date('entry_date');
            $table->foreignId('account_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('to_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->decimal('amount', 14, 2)->default(0);
            $table->text('particulars')->nullable();

            // Donation income fields
            $table->string('donor_name')->nullable();
            $table->string('donor_address')->nullable();
            $table->string('nature_of_donation')->nullable();
            $table->string('mode_of_receipt')->nullable();
            $table->string('section_code')->nullable();
            $table->string('donor_pan')->nullable();
            $table->string('donor_bank_details')->nullable();
            $table->string('receipt_number')->nullable();

            // Donation expense breakdown
            $table->decimal('ops_amount', 14, 2)->nullable();
            $table->decimal('tsp_amount', 14, 2)->nullable();
            $table->decimal('others_amount', 14, 2)->nullable();

            // Investment / maturity fields
            $table->string('scheme_name')->nullable();
            $table->decimal('interest_amount', 14, 2)->nullable();
            $table->decimal('bank_charges_amount', 14, 2)->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['entry_date']);
            $table->index(['entry_type', 'entry_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entries');
    }
};
