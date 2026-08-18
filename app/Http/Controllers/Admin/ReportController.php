<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Entry;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ReportController extends Controller
{
    /** All reports offered on the reports page (key => label). */
    public const REPORTS = [
        'master' => 'Master Report',
        'snapshot' => 'Snapshot Report (Income / Expense / Savings)',
        'it-returns' => 'IT Return Details',
        'fd-details' => 'FD Account Details',
        'rd-details' => 'RD Account Details',
        'mf-details' => 'MF Account Details',
        'donors' => 'Donor Details',
        'income' => 'Specific Income Details',
        'expense' => 'Specific Expense Details',
        'statement' => 'Trust Account Statement',
        'accounts' => 'Trust Account Details',
        'year-end' => 'Financial Year End Report (31 March)',
        'fd-maturity' => 'FD Maturity Date Notifications',
        'rd-maturity' => 'RD Maturity Date Notifications',
        'snapshot-income' => 'Trust Snapshot Income',
        'snapshot-expense' => 'Trust Snapshot Expense',
        'donation-expense' => 'DAP Trust Donation Expense Details',
        'distribution' => 'Donation Distribution Details',
    ];

    public function index(Request $request): View
    {
        $from = $this->cleanDate($request->query('from'));
        $to = $this->cleanDate($request->query('to'));
        $report = $request->query('report', 'master');

        if (! array_key_exists($report, self::REPORTS)) {
            $report = 'master';
        }

        $query = Entry::query()
            ->when($from, fn ($q) => $q->whereDate('entry_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('entry_date', '<=', $to));

        $data = match ($report) {
            'master' => $this->masterData($query, $to),
            'snapshot' => $this->snapshotData($query),
            'it-returns' => $this->itReturnData($query),
            'fd-details' => $this->investmentData($query, 'FD'),
            'rd-details' => $this->investmentData($query, 'RD'),
            'mf-details' => $this->investmentData($query, 'MF'),
            'donors' => $this->donorData($query),
            'income' => $this->specificData($query, Entry::INCOME_TYPES),
            'expense' => $this->specificData($query, Entry::EXPENSE_TYPES),
            'statement' => $this->statementData($query, $from),
            'accounts' => $this->accountData($to),
            'year-end' => $this->yearEndData($query),
            'fd-maturity' => $this->maturityData($query, 'FD'),
            'rd-maturity' => $this->maturityData($query, 'RD'),
            'snapshot-income' => $this->snapshotBreakdownData($query, Entry::INCOME_TYPES),
            'snapshot-expense' => $this->snapshotBreakdownData($query, Entry::EXPENSE_TYPES),
            'donation-expense' => $this->donationExpenseData($query),
            'distribution' => $this->distributionData($query),
        };

        return view('admin.reports.index', array_merge([
            'from' => $from,
            'to' => $to,
            'report' => $report,
            'reports' => self::REPORTS,
        ], $data));
    }

    // ------------------------------------------------------------------ data

    private function masterData($query, ?string $to): array
    {
        $income = $this->sumByTypes($query, Entry::INCOME_TYPES);
        $expense = $this->sumByTypes($query, Entry::EXPENSE_TYPES);

        // Interest earned at maturity lives in the interest_amount column of
        // maturity entries (the import folds it there), so it must be added
        // explicitly or the income figures understate interest income.
        $maturityInterest = (float) (clone $query)
            ->whereIn('entry_type', Entry::MATURITY_TYPES)
            ->sum('interest_amount');

        $masterIncome = [
            'Donation Receipts' => $income['donation_income'] ?? 0,
            'Bank Interest' => $income['bank_interest'] ?? 0,
            'FD Interest' => $income['fd_interest'] ?? 0,
            'RD Interest' => $income['rd_interest'] ?? 0,
            'MF Interest' => $income['mf_interest'] ?? 0,
            'IT Returns' => $income['it_return'] ?? 0,
            'FD / RD / MF Maturities' => ($income['fd_maturity'] ?? 0) + ($income['rd_maturity'] ?? 0) + ($income['mf_maturity'] ?? 0),
            'Maturity Interest' => $maturityInterest,
        ];

        $masterExpense = [
            'Donation Expense' => $expense['donation_expense'] ?? 0,
            'Bank Charges' => $expense['bank_charges'] ?? 0,
            'Professional Charges' => $expense['professional_charges'] ?? 0,
            'Stationary Charges' => $expense['stationary_charges'] ?? 0,
            'Courier Charges' => $expense['courier_charges'] ?? 0,
            'IT Paid' => $expense['it_paid'] ?? 0,
            'FD + RD + MF (Investments)' => ($expense['fd_investment'] ?? 0) + ($expense['rd_investment'] ?? 0) + ($expense['mf_investment'] ?? 0),
        ];

        $bankBalances = Account::orderBy('type')->orderBy('name')->get()->map(fn (Account $a) => [
            'name' => $a->name,
            'type' => $a->type,
            'balance' => $a->balanceAsOf($to),
        ]);

        return compact('masterIncome', 'masterExpense', 'bankBalances');
    }

    private function snapshotData($query): array
    {
        $incomeTotal = (float) (clone $query)->whereIn('entry_type', Entry::INCOME_TYPES)->sum('amount');
        $maturityInterest = (float) (clone $query)->whereIn('entry_type', Entry::MATURITY_TYPES)->sum('interest_amount');
        $expenseTotal = (float) (clone $query)->whereIn('entry_type', Entry::EXPENSE_TYPES)->sum('amount');

        return [
            'incomeTotal' => $incomeTotal + $maturityInterest,
            'expenseTotal' => $expenseTotal,
            'savingsTotal' => $incomeTotal + $maturityInterest - $expenseTotal,
        ];
    }

    private function itReturnData($query): array
    {
        $itReturns = (clone $query)
            ->where('entry_type', Entry::TYPE_IT_RETURN)
            ->with('account')
            ->orderBy('entry_date')
            ->get();

        return ['itReturns' => $itReturns];
    }

    private function investmentData($query, string $prefix): array
    {
        $investmentType = Entry::INVEST_TYPES[$prefix] ?? null;
        $maturityType = Entry::MATURITY_TYPE_MAP[$prefix] ?? null;
        $interestType = Entry::INTEREST_TYPE_MAP[$prefix] ?? null;

        $investments = (clone $query)
            ->where('entry_type', $investmentType)
            ->with('account')
            ->orderBy('entry_date')
            ->get();

        $maturities = (clone $query)
            ->where('entry_type', $maturityType)
            ->with('account')
            ->orderBy('entry_date')
            ->get();

        $interestTotal = (float) (clone $query)
            ->where('entry_type', $interestType)
            ->sum('amount');

        $interestOnMaturity = (float) (clone $query)
            ->where('entry_type', $maturityType)
            ->sum('interest_amount');

        return [
            'investmentType' => $prefix,
            'investments' => $investments,
            'maturities' => $maturities,
            'investmentTotal' => (float) $investments->sum('amount'),
            'maturityTotal' => (float) $maturities->sum('amount'),
            'interestTotal' => $interestTotal + $interestOnMaturity,
        ];
    }

    private function donorData($query): array
    {
        $donors = (clone $query)
            ->where('entry_type', Entry::TYPE_DONATION_INCOME)
            ->with('account')
            ->orderBy('entry_date')
            ->get();

        return ['donors' => $donors];
    }

    private function specificData($query, array $types): array
    {
        $entries = (clone $query)
            ->whereIn('entry_type', $types)
            ->with(['account', 'toAccount'])
            ->orderBy('entry_date')
            ->get();

        return ['entries' => $entries];
    }

    private function statementData($query, ?string $from): array
    {
        $accounts = Account::orderBy('type')->orderBy('name')->get();
        $periodEntries = (clone $query)
            ->with(['account', 'toAccount'])
            ->orderBy('entry_date')
            ->orderBy('id')
            ->get();

        $opening = [];
        foreach ($accounts as $account) {
            $opening[$account->id] = $from
                ? $account->balanceAsOf(Carbon::parse($from)->subDay()->toDateString())
                : 0.0;
        }

        $running = $opening;
        $rows = [];
        foreach ($periodEntries as $entry) {
            // For transfers show the destination account's balance, which is
            // where the money landed and what the reader most wants to see.
            $primary = $entry->entry_type === Entry::TYPE_BANK_TRANSFER
                ? ($entry->to_account_id ?? $entry->account_id)
                : ($entry->account_id ?? $entry->to_account_id);
            $isIncome = in_array($entry->entry_type, Entry::INCOME_TYPES, true);
            $isExpense = in_array($entry->entry_type, Entry::EXPENSE_TYPES, true);
            $isMaturity = in_array($entry->entry_type, Entry::MATURITY_TYPES, true);

            $debit = null;
            $credit = null;

            if ($entry->entry_type === Entry::TYPE_BANK_TRANSFER) {
                if ($entry->account_id !== null) {
                    $running[$entry->account_id] = round($running[$entry->account_id] - $entry->amount, 2);
                    $debit = $entry->amount;
                }
                if ($entry->to_account_id !== null) {
                    $running[$entry->to_account_id] = round($running[$entry->to_account_id] + $entry->amount, 2);
                    $credit = $entry->amount;
                }
            } elseif ($isIncome) {
                $running[$entry->account_id] = round(($running[$entry->account_id] ?? 0) + $entry->amount, 2);
                $credit = $entry->amount;
            } elseif ($isExpense) {
                $running[$entry->account_id] = round(($running[$entry->account_id] ?? 0) - $entry->amount, 2);
                $debit = $entry->amount;
            }

            if ($isMaturity) {
                $running[$entry->account_id] = round(
                    $running[$entry->account_id] + (float) $entry->interest_amount - (float) $entry->bank_charges_amount,
                    2,
                );
            }

            $rows[] = [
                'entry' => $entry,
                'primary_account' => $entry->account ?? $entry->toAccount,
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $running[$primary] ?? null,
            ];
        }

        return [
            'statementAccounts' => $accounts,
            'statementOpening' => $opening,
            'statementClosing' => $running,
            'statementRows' => $rows,
        ];
    }

    private function accountData(?string $to): array
    {
        $accountDetails = Account::withCount('entries')
            ->orderBy('type')
            ->orderBy('name')
            ->get()
            ->map(fn (Account $a) => [
                'account' => $a,
                'balance' => $a->balanceAsOf($to),
            ]);

        return ['accountDetails' => $accountDetails];
    }

    private function yearEndData($query): array
    {
        $entries = (clone $query)
            ->whereMonth('entry_date', 3)
            ->whereDay('entry_date', 31)
            ->with('account')
            ->orderBy('entry_date')
            ->get();

        return ['entries' => $entries];
    }

    private function maturityData($query, string $prefix): array
    {
        $type = Entry::MATURITY_TYPE_MAP[$prefix] ?? null;
        $entries = (clone $query)
            ->where('entry_type', $type)
            ->with('account')
            ->orderBy('entry_date')
            ->get();

        return [
            'maturityType' => $prefix,
            'entries' => $entries,
        ];
    }

    private function snapshotBreakdownData($query, array $types): array
    {
        $entries = (clone $query)
            ->whereIn('entry_type', $types)
            ->with('account')
            ->orderBy('entry_date')
            ->get();

        $byType = $entries->groupBy('entry_type')
            ->map(fn (Collection $group) => (float) $group->sum('amount'));

        return [
            'entries' => $entries,
            'byType' => $byType,
            'total' => (float) $entries->sum('amount'),
        ];
    }

    private function donationExpenseData($query): array
    {
        $entries = (clone $query)
            ->where('entry_type', Entry::TYPE_DONATION_EXPENSE)
            ->with('account')
            ->orderBy('entry_date')
            ->get();

        return ['entries' => $entries, 'total' => (float) $entries->sum('amount')];
    }

    private function distributionData($query): array
    {
        $entries = (clone $query)
            ->where('entry_type', Entry::TYPE_DONATION_EXPENSE)
            ->with('account')
            ->orderBy('entry_date')
            ->get();

        $byRecipient = $entries
            ->groupBy(fn (Entry $e) => $e->particulars ?: '—')
            ->map(fn (Collection $group) => [
                'count' => $group->count(),
                'total' => (float) $group->sum('amount'),
            ])
            ->sortByDesc('total');

        return [
            'entries' => $entries,
            'byRecipient' => $byRecipient,
            'total' => (float) $entries->sum('amount'),
        ];
    }

    // ----------------------------------------------------------------- utils

    private function cleanDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function sumByTypes($query, array $types): array
    {
        return (clone $query)
            ->whereIn('entry_type', $types)
            ->selectRaw('entry_type, SUM(amount) as total')
            ->groupBy('entry_type')
            ->pluck('total', 'entry_type')
            ->map(fn ($v) => (float) $v)
            ->toArray();
    }
}
