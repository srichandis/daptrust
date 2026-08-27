<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE entries MODIFY COLUMN entry_type ENUM('donation_income','donation_expense','bank_interest','fd_interest','mf_interest','rd_interest','bank_charges','bank_transfer','fd_investment','rd_investment','mf_investment','fd_maturity','rd_maturity','mf_maturity','professional_charges','stationary_charges','courier_charges','it_return','it_paid','bank_balance','data_correction','donation_to_ops') NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE entries MODIFY COLUMN entry_type ENUM('donation_income','donation_expense','bank_interest','fd_interest','mf_interest','rd_interest','bank_charges','bank_transfer','fd_investment','rd_investment','mf_investment','fd_maturity','rd_maturity','mf_maturity','professional_charges','stationary_charges','courier_charges','it_return','it_paid','bank_balance','data_correction') NOT NULL");
        }
    }
};
