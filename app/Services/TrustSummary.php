<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Entry;
use Illuminate\Database\Eloquent\Builder;

/**
 * Reproduces the trust-level summary at the bottom of the Excel MASTER sheet
 * (rows 2541-2559) from the normalised entries table.
 *
 * Mapping to the Excel formulas:
 *   DONOR / RECEIPTS      = donation_income
 *   DONATION              = donation_expense
 *   BANK CHARGES          = bank_charges + stationary_charges (Excel F + K + AC + AU)
 *   BANK INTEREST         = bank_interest
 *   FD/RD/MF INTEREST     = fd_interest + rd_interest + mf_interest
 *                           (Excel M2537+N2537+O2537 + AE2537+AF2537+AG2537
 *                            + AW2537+AX2537+AY2537 — AB, SBI and HDFC)
 *   PROFESSIONAL CHARGES  = professional_charges
 *   IT RETURN             = it_return
 *   FD + MF + RD          = investments - maturities (net, as Excel D2555)
 *   BANK BALANCE          = closing balance of every account
 *                           (Excel H2537+Z2537+AR2537+BJ2537 — cash, AB, SBI, HDFC)
 *   DON TO OPS            = donation_to_ops (Excel BL2537)
 */
class TrustSummary
{
    public function __construct(
        private ?string $from = null,
        private ?string $to = null,
    ) {}

    /**
     * The ten summary lines, in the same order as the Excel summary block.
     *
     * @return array<string, float>
     */
    public function lines(): array
    {
        $investments = $this->sum([
            Entry::TYPE_FD_INVESTMENT,
            Entry::TYPE_RD_INVESTMENT,
            Entry::TYPE_MF_INVESTMENT,
        ]);

        $maturities = $this->sum([
            Entry::TYPE_FD_MATURITY,
            Entry::TYPE_RD_MATURITY,
            Entry::TYPE_MF_MATURITY,
        ]);

        return [
            'DONOR / RECEIPTS' => $this->sum([Entry::TYPE_DONATION_INCOME]),
            'DONATION' => $this->sum([Entry::TYPE_DONATION_EXPENSE]),
            'BANK CHARGES' => $this->sum([Entry::TYPE_BANK_CHARGES, Entry::TYPE_STATIONARY_CHARGES]),
            'BANK INTEREST' => $this->sum([Entry::TYPE_BANK_INTEREST]),
            'FD/RD/MF INTEREST' => $this->sum([
                Entry::TYPE_FD_INTEREST,
                Entry::TYPE_RD_INTEREST,
                Entry::TYPE_MF_INTEREST,
            ]),
            'PROFESSIONAL CHARGES' => $this->sum([Entry::TYPE_PROFESSIONAL_CHARGES]),
            'IT RETURN' => $this->sum([Entry::TYPE_IT_RETURN]),
            'FD + MF + RD' => round($investments - $maturities, 2),
            'BANK BALANCE' => $this->bankBalance(),
            'DON TO OPS' => $this->sum([Entry::TYPE_DONATION_TO_OPS]),
        ];
    }

    /**
     * Excel rows 2581-2587: INCOME, EXPENSE, SAVINGS and EXP + SAV.
     *
     * @return array{income: float, expense: float, savings: float, exp_plus_sav: float}
     */
    public function totals(): array
    {
        $lines = $this->lines();

        $income = $lines['DONOR / RECEIPTS']
            + $lines['BANK INTEREST']
            + $lines['FD/RD/MF INTEREST']
            + $lines['IT RETURN'];

        $expense = $lines['DONATION']
            + $lines['BANK CHARGES']
            + $lines['PROFESSIONAL CHARGES'];

        // Savings = net investments + closing bank balance, as in Excel.
        $savings = $lines['FD + MF + RD'] + $lines['BANK BALANCE'];

        return [
            'income' => round($income, 2),
            'expense' => round($expense, 2),
            'savings' => round($savings, 2),
            'exp_plus_sav' => round($expense + $savings, 2),
        ];
    }

    private function sum(array $types): float
    {
        return round((float) $this->query()->whereIn('entry_type', $types)->sum('amount'), 2);
    }

    private function query(): Builder
    {
        return Entry::query()
            ->when($this->from, fn (Builder $q) => $q->whereDate('entry_date', '>=', $this->from))
            ->when($this->to, fn (Builder $q) => $q->whereDate('entry_date', '<=', $this->to));
    }

    private function bankBalance(): float
    {
        return round(
            Account::all()->sum(fn (Account $account) => $account->balanceAsOf($this->to)),
            2,
        );
    }
}
