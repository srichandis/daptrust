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

        // Card totals use the same type sets as the breakdown tables below,
        // so the headline numbers and the per-type rows always agree.
        $incomeTotal = Entry::whereIn('entry_type', Entry::GROUPS['Income'])->sum('amount');
        $expenseTotal = Entry::whereIn('entry_type', Entry::GROUPS['Expenses'])->sum('amount');

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
            'incomeByType',
            'expenseByType',
            'latestEntries',
        ));
    }
}
