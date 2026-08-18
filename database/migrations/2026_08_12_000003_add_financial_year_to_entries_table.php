<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Financial year tag (e.g. "FY25") assigned during the Excel import so the
     * ledger can be filtered by the trust's April–March financial year.
     */
    public function up(): void
    {
        Schema::table('entries', function (Blueprint $table) {
            $table->string('financial_year', 10)->nullable()->after('entry_date');
            $table->index('financial_year');
        });
    }

    public function down(): void
    {
        Schema::table('entries', function (Blueprint $table) {
            $table->dropIndex(['financial_year']);
            $table->dropColumn('financial_year');
        });
    }
};
