<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Entry;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TransparencyController extends Controller
{
    /** Sections used to build the sidebar menu. */
    public const MENU = [
        'About & Governance' => [
            ['introduction', 'DAP Trust Introduction'],
            ['financial-report', 'DAP Trust Financial Report'],
            ['trustees', 'DAP Trustees Details'],
            ['notice-board', 'DAP Trust Notice Board'],
        ],
        'Financial Transparency' => [
            ['endowment', 'DAP Trust Endowment Details'],
            ['donors', 'DAP Trust Donors Details'],
        ],
        'Donations' => [
            ['donation-distribution', 'DAP Trust Donation Distribution Details'],
            ['donation-particulars', 'DAP Trust Donation Amount Particulars'],
        ],
        'Compliance' => [
            ['resolutions', 'DAP Trust Resolution'],
            ['certificates', 'DAP Trust Govt Certificate'],
        ],
        'Investments' => [
            ['mf', 'DAP Trust Mutual Fund Details'],
            ['fd', 'DAP Trust Fixed Deposit Details'],
            ['rd', 'DAP Trust RD Details'],
            ['it-return', 'DAP Trust IT Return Details'],
            ['ops', 'DAP Trust OPS Donation'],
            ['audit-report', 'DAP Trust Audit Report'],
            ['bank-particulars', 'DAP Trust Bank Particulars'],
        ],
        'OPS Donations' => [
            ['income-trend', 'DAP Trust Income Trend'],
            ['donation-expense-trend', 'DAP Trust Donation Expense Trend'],
            ['investment-trend', 'DAP Trust Total Investment MF + FD + RD Trend'],
            ['it-return-trend', 'DAP Trust IT Return Trend'],
            ['ops-trend', 'DAP Trust OPS Donation Trend'],
        ],
    ];

    public const TRUST_NAME = 'DAP Educational Trust';
    public const ESTABLISHED = 'February 1, 2003';

    /* ================================================================ About & Governance */

    public function index(): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('admin.transparency.financial-report');
    }

    public function introduction(): View
    {
        return $this->render('transparency.introduction', [
            'donationIncome' => $this->donationIncome(),
            'donationCount' => Entry::where('entry_type', Entry::TYPE_DONATION_INCOME)->count(),
            'since' => Entry::min('financial_year'),
        ]);
    }

    public function financialReport(): View
    {
        $accounts = Account::orderBy('type')->orderBy('name')->get();
        $totalBalance = collect($accounts)->sum(fn (Account $a) => $a->balance());

        // Dashboard-style totals (specific subset of entry types)
        $incomeTypes = [
            Entry::TYPE_DONATION_INCOME,
            Entry::TYPE_BANK_INTEREST,
            Entry::TYPE_FD_INTEREST,
            Entry::TYPE_RD_INTEREST,
            Entry::TYPE_MF_INTEREST,
            Entry::TYPE_IT_RETURN,
        ];
        $expenseTypes = [
            Entry::TYPE_DONATION_EXPENSE,
            Entry::TYPE_BANK_CHARGES,
            Entry::TYPE_PROFESSIONAL_CHARGES,
            Entry::TYPE_STATIONARY_CHARGES,
        ];
        $incomeTotal = Entry::whereIn('entry_type', $incomeTypes)->sum('amount');
        $expenseTotal = Entry::whereIn('entry_type', $expenseTypes)->sum('amount');
        $savings = $incomeTotal - $expenseTotal;

        $incomeByType = $this->byType(Entry::INCOME_TYPES);
        $expenseByType = $this->byType(Entry::EXPENSE_TYPES);

        $latestEntries = Entry::with(['account', 'toAccount'])
            ->whereIn('entry_type', array_merge(Entry::GROUPS['Income'], Entry::GROUPS['Expenses']))
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        $donationIncome = $this->donationIncome();

        return $this->render('transparency.financial-report', compact(
            'totalBalance', 'incomeTotal', 'expenseTotal', 'savings', 'incomeByType', 'expenseByType',
            'latestEntries', 'donationIncome',
        ));
    }

    public function trustees(): View
    {
        return $this->render('transparency.trustees');
    }

    public function noticeBoard(): View
    {
        return $this->render('transparency.notice-board', [
            'latestReport' => Entry::max('financial_year'),
            'latestEntry' => Entry::orderByDesc('entry_date')->orderByDesc('id')->first(),
        ]);
    }

    /* ================================================================ Financial Transparency */

    public function endowment(): View
    {
        $accounts = Account::orderBy('type')->orderBy('name')->get();
        $totalBalance = collect($accounts)->sum(fn (Account $a) => $a->balance());

        $investments = Entry::with('account')
            ->whereIn('entry_type', [Entry::TYPE_FD_INVESTMENT, Entry::TYPE_RD_INVESTMENT, Entry::TYPE_MF_INVESTMENT])
            ->orderByDesc('entry_date')
            ->get();

        $byKind = $investments->groupBy('entry_type')->map(fn ($g) => $g->sum('amount'));

        return $this->render('transparency.endowment', compact('totalBalance', 'investments', 'byKind'));
    }

    public function donors(): View
    {
        $donationIncome = $this->donationIncome();
        $donationCount = Entry::where('entry_type', Entry::TYPE_DONATION_INCOME)->count();

        $topDonors = Entry::where('entry_type', Entry::TYPE_DONATION_INCOME)
            ->whereNotNull('donor_name')
            ->where('donor_name', '!=', '')
            ->selectRaw('donor_name, COUNT(*) as donations, SUM(amount) as total')
            ->groupBy('donor_name')
            ->orderByDesc('total')
            ->limit(20)
            ->get();

        $recent = Entry::with('account')
            ->where('entry_type', Entry::TYPE_DONATION_INCOME)
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->limit(12)
            ->get();

        return $this->render('transparency.donors', compact('donationIncome', 'donationCount', 'topDonors', 'recent'));
    }

    /* ================================================================ Donations */

    public function donationDistribution(): View
    {
        $donationExpense = Entry::where('entry_type', Entry::TYPE_DONATION_EXPENSE)->sum('amount');
        $opsTotal = Entry::where('entry_type', Entry::TYPE_DONATION_EXPENSE)
            ->where('particulars', 'like', '%OPS%')
            ->sum('amount');

        $byParticulars = Entry::where('entry_type', Entry::TYPE_DONATION_EXPENSE)
            ->whereNotNull('particulars')
            ->where('particulars', '!=', '')
            ->selectRaw('particulars, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('particulars')
            ->orderByDesc('total')
            ->limit(25)
            ->get();

        return $this->render('transparency.donation-distribution', compact('donationExpense', 'opsTotal', 'byParticulars'));
    }

    public function donationParticulars(): View
    {
        $donationIncome = $this->donationIncome();
        $donationCount = Entry::where('entry_type', Entry::TYPE_DONATION_INCOME)->count();

        $byYear = Entry::where('entry_type', Entry::TYPE_DONATION_INCOME)
            ->selectRaw('financial_year, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('financial_year')
            ->orderBy('financial_year')
            ->get();

        $byMode = Entry::where('entry_type', Entry::TYPE_DONATION_INCOME)
            ->whereNotNull('mode_of_receipt')
            ->where('mode_of_receipt', '!=', '')
            ->selectRaw('mode_of_receipt, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('mode_of_receipt')
            ->orderByDesc('total')
            ->get();

        return $this->render('transparency.donation-particulars', compact('donationIncome', 'donationCount', 'byYear', 'byMode'));
    }

    /* ================================================================ Compliance */

    public function resolutions(): View
    {
        return $this->render('transparency.resolutions');
    }

    public function certificates(): View
    {
        return $this->render('transparency.certificates');
    }

    /* ================================================================ Investments */

    public function mf(): View
    {
        return $this->investmentPage('mf', 'Mutual Fund');
    }

    public function fd(): View
    {
        return $this->investmentPage('fd', 'Fixed Deposit');
    }

    public function rd(): View
    {
        return $this->investmentPage('rd', 'Recurring Deposit');
    }

    public function itReturn(): View
    {
        $entries = Entry::with('account')
            ->whereIn('entry_type', [Entry::TYPE_IT_RETURN, Entry::TYPE_IT_PAID])
            ->orderByDesc('entry_date')
            ->get();

        $refunds = $entries->where('entry_type', Entry::TYPE_IT_RETURN)->sum('amount');
        $paid = $entries->where('entry_type', Entry::TYPE_IT_PAID)->sum('amount');

        return $this->render('transparency.it-return', compact('entries', 'refunds', 'paid'));
    }

    public function ops(): View
    {
        $entries = Entry::with('account')
            ->where('entry_type', Entry::TYPE_DONATION_EXPENSE)
            ->where('particulars', 'like', '%OPS%')
            ->orderByDesc('entry_date')
            ->get();

        $total = $entries->sum('amount');

        $byPlace = $entries->groupBy('particulars')->map(fn ($g) => $g->sum('amount'));

        return $this->render('transparency.ops', compact('entries', 'total', 'byPlace'));
    }

    public function auditReport(): View
    {
        return $this->render('transparency.audit-report', [
            'years' => collect(Entry::query()->select('financial_year')->distinct()->pluck('financial_year'))
                ->map(fn ($fy) => (int) substr((string) $fy, 2))->sort()->reverse()->values(),
        ]);
    }

    public function bankParticulars(): View
    {
        return $this->render('transparency.bank-particulars', [
            'accounts' => Account::orderBy('type')->orderBy('name')->get(),
        ]);
    }

    /* ================================================================ OPS Donations (trends) */

    public function incomeTrend(): View
    {
        return $this->trend(
            'Income Trend',
            'Annual income received by the trust, financial year by financial year.',
            Entry::whereIn('entry_type', Entry::INCOME_TYPES)
        );
    }

    public function donationExpenseTrend(): View
    {
        return $this->trend(
            'Donation Expense Trend',
            'Annual donations distributed by the trust to beneficiaries and organisations.',
            Entry::where('entry_type', Entry::TYPE_DONATION_EXPENSE)
        );
    }

    public function investmentTrend(): View
    {
        return $this->trend(
            'Total Investment (MF + FD + RD) Trend',
            'Annual fresh investment into mutual funds, fixed deposits and recurring deposits.',
            Entry::whereIn('entry_type', [Entry::TYPE_MF_INVESTMENT, Entry::TYPE_FD_INVESTMENT, Entry::TYPE_RD_INVESTMENT])
        );
    }

    public function itReturnTrend(): View
    {
        return $this->trend(
            'IT Return Trend',
            'Annual IT return refunds received by the trust.',
            Entry::where('entry_type', Entry::TYPE_IT_RETURN)
        );
    }

    public function opsTrend(): View
    {
        return $this->trend(
            'OPS Donation Trend',
            'Annual donations made to OPS Saiva Peravai and its centres.',
            Entry::where('entry_type', Entry::TYPE_DONATION_EXPENSE)->where('particulars', 'like', '%OPS%')
        );
    }

    /* ================================================================ Helpers */

    /**
     * Render a transparency page with the shared money formatter available.
     */
    private function render(string $view, array $data = []): View
    {
        $data['money'] ??= fn (float $value): string => LandingController::money($value);

        return view($view, $data);
    }

    private function trend(string $title, string $subtitle, $query): View
    {
        $rows = (clone $query)
            ->selectRaw('financial_year, SUM(amount) as total')
            ->groupBy('financial_year')
            ->orderBy('financial_year')
            ->get()
            ->keyBy('financial_year');

        // Derive FY range from actual data
        $allYears = Entry::query()->select('financial_year')->distinct()->pluck('financial_year')->filter()->values();
        if ($allYears->isEmpty()) {
            $start = 2;
            $end = 2;
        } else {
            $fyNums = $allYears->map(fn ($fy) => (int) substr((string) $fy, 2))->sort()->values();
            $start = $fyNums->first();
            $end = $fyNums->last();
        }

        $series = [];
        for ($y = $start; $y <= $end; $y++) {
            $fy = sprintf('FY%02d', $y);
            $series[] = [
                'label' => "'".substr((string) $fy, 2),
                'fy' => $fy,
                'value' => (float) ($rows[$fy]->total ?? 0),
            ];
        }

        $max = max(array_column($series, 'value')) ?: 1;
        $total = array_sum(array_column($series, 'value'));

        return $this->render('transparency.trends', compact('title', 'subtitle', 'series', 'max', 'total'));
    }

    private function investmentPage(string $prefix, string $label): View
    {
        $investmentType = Entry::INVEST_TYPES[strtoupper($prefix)];
        $interestType = Entry::INTEREST_TYPE_MAP[strtoupper($prefix)];
        $maturityType = Entry::MATURITY_TYPE_MAP[strtoupper($prefix)];

        $investments = Entry::with('account')->where('entry_type', $investmentType)->orderByDesc('entry_date')->get();
        $interests = Entry::with('account')->where('entry_type', $interestType)->orderByDesc('entry_date')->get();
        $maturities = Entry::with('account')->where('entry_type', $maturityType)->orderByDesc('entry_date')->get();

        $invested = $investments->sum('amount');
        $interestTotal = $interests->sum('amount');
        $matured = $maturities->sum('amount');

        return $this->render('transparency.investment', compact(
            'label', 'prefix', 'investments', 'interests', 'maturities',
            'invested', 'interestTotal', 'matured',
        ));
    }

    private function totals(): array
    {
        return [
            Entry::whereIn('entry_type', Entry::GROUPS['Income'])->sum('amount'),
            Entry::whereIn('entry_type', Entry::GROUPS['Expenses'])->sum('amount'),
        ];
    }

    private function byType(array $types): array
    {
        return Entry::whereIn('entry_type', $types)
            ->selectRaw('entry_type, SUM(amount) as total')
            ->groupBy('entry_type')
            ->pluck('total', 'entry_type')
            ->map(fn ($v) => (float) $v)
            ->toArray();
    }

    private function donationIncome(): float
    {
        return (float) Entry::where('entry_type', Entry::TYPE_DONATION_INCOME)->sum('amount');
    }
}
