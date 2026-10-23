<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Entry;
use App\Services\TrustSummary;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $accounts = Account::orderBy('type')->orderBy('name')->get();
        $totalBalance = collect($accounts)->sum(fn (Account $a) => $a->balance());

        // Excel summary: INCOME, EXPENSE, SAVINGS, EXP+SAV
        $summary = new TrustSummary();
        $trustSummary = $summary->lines();
        $summaryTotals = $summary->totals();

        $incomeTotal = $summaryTotals['income'];
        $expenseTotal = $summaryTotals['expense'];
        $savings = $summaryTotals['savings'];
        $expPlusSav = $summaryTotals['exp_plus_sav'];

        // Totals per group (excluding transfers/balances/utilities which are not income/expense)
        $incomeByType = Entry::whereIn('entry_type', Entry::INCOME_TYPES)
            ->selectRaw('entry_type, SUM(amount) as total')
            ->groupBy('entry_type')
            ->pluck('total', 'entry_type')
            ->map(fn ($v) => (float) $v)
            ->toArray();

        $expenseByType = Entry::whereIn('entry_type', Entry::EXPENSE_TYPES)
            ->selectRaw('entry_type, SUM(amount) as total')
            ->groupBy('entry_type')
            ->pluck('total', 'entry_type')
            ->map(fn ($v) => (float) $v)
            ->toArray();

        // Merge stationary_charges into bank_charges (matches Excel grouping)
        if (isset($expenseByType['stationary_charges'])) {
            $expenseByType['bank_charges'] = ($expenseByType['bank_charges'] ?? 0) + $expenseByType['stationary_charges'];
            unset($expenseByType['stationary_charges']);
        }

        // FD, RD and MF interest are shown as one "FD/RD/MF INTEREST" line, the
        // same grouping the Excel sheet and the trust summary use.
        $incomeByType[Entry::TYPE_FD_INTEREST] = ($incomeByType[Entry::TYPE_FD_INTEREST] ?? 0)
            + ($incomeByType[Entry::TYPE_RD_INTEREST] ?? 0)
            + ($incomeByType[Entry::TYPE_MF_INTEREST] ?? 0);
        unset($incomeByType[Entry::TYPE_RD_INTEREST], $incomeByType[Entry::TYPE_MF_INTEREST]);

        $latestEntries = Entry::with(['account', 'toAccount'])
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'accounts',
            'totalBalance',
            'incomeTotal',
            'expenseTotal',
            'savings',
            'expPlusSav',
            'incomeByType',
            'expenseByType',
            'latestEntries',
            'trustSummary',
            'summaryTotals',
        ));
    }
}
