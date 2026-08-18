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
        ));
    }

    /**
     * Format an amount in Indian notation: ₹7.86 Cr, ₹1.24 L, or ₹45,230.00.
     */
    public static function money(float $value): string
    {
        $sign = $value < 0 ? '−' : '';
        $abs = abs($value);

        if ($abs >= 10000000) {
            return $sign.'₹'.number_format($abs / 10000000, 2).' Cr';
        }

        if ($abs >= 100000) {
            return $sign.'₹'.number_format($abs / 100000, 2).' L';
        }

        return $sign.'₹'.number_format($abs, 2);
    }
}
