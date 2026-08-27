<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Entry;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $accounts = Account::orderBy('type')->orderBy('name')->get();
        $totalBalance = collect($accounts)->sum(fn (Account $a) => $a->balance());

        // Excel summary: INCOME, EXPENSE, SAVINGS, EXP+SAV
        // INCOME = donations received + interest + IT return (excludes maturity principal & investments)
        $incomeTypes = [
            Entry::TYPE_DONATION_INCOME,
            Entry::TYPE_BANK_INTEREST,
            Entry::TYPE_FD_INTEREST,
            Entry::TYPE_RD_INTEREST,
            Entry::TYPE_MF_INTEREST,
            Entry::TYPE_IT_RETURN,
        ];
        // EXPENSE = donations given + charges (excludes investments)
        $expenseTypes = [
            Entry::TYPE_DONATION_EXPENSE,
            Entry::TYPE_BANK_CHARGES,
            Entry::TYPE_PROFESSIONAL_CHARGES,
            Entry::TYPE_STATIONARY_CHARGES,
        ];
        $incomeTotal = Entry::whereIn('entry_type', $incomeTypes)->sum('amount');
        $expenseTotal = Entry::whereIn('entry_type', $expenseTypes)->sum('amount');
        $savings = $incomeTotal - $expenseTotal;

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

        // Count OPS donations separately for dashboard display
        $opsDonationTotal = Entry::where('entry_type', 'donation_to_ops')->sum('amount');

        $latestEntries = Entry::with(['account', 'toAccount'])
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        // Trust-level summary totals (matching the Excel master summary)
        $trustSummary = [
            'Donor / Receipts' => Entry::where('entry_type', 'donation_income')->sum('amount'),
            'Donation (Disbursements)' => Entry::where('entry_type', 'donation_expense')->sum('amount'),
            'Bank Charges' => Entry::whereIn('entry_type', ['bank_charges', 'stationary_charges'])->sum('amount'),
            'Bank Interest' => Entry::where('entry_type', 'bank_interest')->sum('amount'),
            'FD/RD/MF Interest' => Entry::whereIn('entry_type', ['fd_interest', 'rd_interest', 'mf_interest'])->sum('amount'),
            'Professional Charges' => Entry::where('entry_type', 'professional_charges')->sum('amount'),
            'IT Return' => Entry::where('entry_type', 'it_return')->sum('amount'),
            'FD + MF + RD (Investments)' => Entry::whereIn('entry_type', ['fd_investment', 'rd_investment', 'mf_investment'])->sum('amount'),
        ];

        return view('admin.dashboard', compact(
            'accounts',
            'totalBalance',
            'incomeTotal',
            'expenseTotal',
            'savings',
            'incomeByType',
            'expenseByType',
            'latestEntries',
            'trustSummary',
            'opsDonationTotal',
        ));
    }
}
