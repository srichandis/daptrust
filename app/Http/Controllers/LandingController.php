<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Entry;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function index(): View
    {
        $accounts = Account::orderBy('type')->orderBy('name')->get();
        $totalBalance = collect($accounts)->sum(fn (Account $a) => $a->balance());

        $incomeTotal = Entry::whereIn('entry_type', Entry::GROUPS['Income'])->sum('amount');
        $expenseTotal = Entry::whereIn('entry_type', Entry::GROUPS['Expenses'])->sum('amount');
        $donationIncome = Entry::where('entry_type', Entry::TYPE_DONATION_INCOME)->sum('amount');
        $donationCount = Entry::where('entry_type', Entry::TYPE_DONATION_INCOME)->count();

        $latestEntries = Entry::with(['account', 'toAccount'])
            ->whereIn('entry_type', array_merge(Entry::GROUPS['Income'], Entry::GROUPS['Expenses']))
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $latestEntry = $latestEntries->first();

        // Dynamic FY range from data
        $fyNums = Entry::query()->select('financial_year')->distinct()->pluck('financial_year')
            ->filter()
            ->map(fn ($fy) => (int) substr((string) $fy, 2))
            ->sort()->values();
        $latestFy = Entry::max('financial_year') ?? 'FY' . date('y');
        $firstFyYear = $fyNums->isNotEmpty() ? 2000 + $fyNums->first() : 2002;
        $fyLabel = str_replace('FY', '', $latestFy);
        $fyYearStart = (int) $fyLabel;
        $fyDisplay = "FY " . (2000 + $fyYearStart) . '-' . str_pad((string) (($fyYearStart + 1) % 100), 2, '0', STR_PAD_LEFT);

        $money = fn (float $value): string => static::money($value);

        return view('welcome', compact(
            'totalBalance',
            'incomeTotal',
            'expenseTotal',
            'donationIncome',
            'donationCount',
            'latestEntries',
            'latestEntry',
            'money',
            'firstFyYear',
            'fyDisplay',
        ));
    }

    /**
     * Format an amount in Indian notation: ₹7.86 Cr, ₹1.24 L, or ₹45,230.00.
     */
    public static function money(float $value): string
    {
        return indian_money($value);
    }
}
