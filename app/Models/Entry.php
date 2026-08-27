<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Entry extends Model
{
    public const TYPE_DONATION_INCOME = 'donation_income';
    public const TYPE_DONATION_EXPENSE = 'donation_expense';
    public const TYPE_BANK_INTEREST = 'bank_interest';
    public const TYPE_FD_INTEREST = 'fd_interest';
    public const TYPE_MF_INTEREST = 'mf_interest';
    public const TYPE_RD_INTEREST = 'rd_interest';
    public const TYPE_BANK_CHARGES = 'bank_charges';
    public const TYPE_BANK_TRANSFER = 'bank_transfer';
    public const TYPE_FD_INVESTMENT = 'fd_investment';
    public const TYPE_RD_INVESTMENT = 'rd_investment';
    public const TYPE_MF_INVESTMENT = 'mf_investment';
    public const TYPE_FD_MATURITY = 'fd_maturity';
    public const TYPE_RD_MATURITY = 'rd_maturity';
    public const TYPE_MF_MATURITY = 'mf_maturity';
    public const TYPE_PROFESSIONAL_CHARGES = 'professional_charges';
    public const TYPE_STATIONARY_CHARGES = 'stationary_charges';
    public const TYPE_COURIER_CHARGES = 'courier_charges';
    public const TYPE_IT_RETURN = 'it_return';
    public const TYPE_IT_PAID = 'it_paid';
    public const TYPE_BANK_BALANCE = 'bank_balance';
    public const TYPE_DATA_CORRECTION = 'data_correction';
    public const TYPE_DONATION_TO_OPS = 'donation_to_ops';

    /**
     * Display labels for every entry type (ordered as the trust defined them).
     */
    public const LABELS = [
        self::TYPE_DONATION_INCOME => 'Donation Income',
        self::TYPE_DONATION_EXPENSE => 'Donation Expense',
        self::TYPE_BANK_INTEREST => 'Bank Interest',
        self::TYPE_FD_INTEREST => 'FD Interest',
        self::TYPE_MF_INTEREST => 'MF Interest',
        self::TYPE_RD_INTEREST => 'RD Interest',
        self::TYPE_BANK_CHARGES => 'Bank Charges',
        self::TYPE_BANK_TRANSFER => 'Bank Transfer (Internal)',
        self::TYPE_FD_INVESTMENT => 'FD',
        self::TYPE_RD_INVESTMENT => 'RD',
        self::TYPE_MF_INVESTMENT => 'MF',
        self::TYPE_FD_MATURITY => 'FD Maturity',
        self::TYPE_RD_MATURITY => 'RD Maturity',
        self::TYPE_MF_MATURITY => 'MF Maturity',
        self::TYPE_PROFESSIONAL_CHARGES => 'Professional Charges',
        self::TYPE_STATIONARY_CHARGES => 'Stationary Charges',
        self::TYPE_COURIER_CHARGES => 'Courier Charges',
        self::TYPE_IT_RETURN => 'IT Return',
        self::TYPE_IT_PAID => 'IT Paid',
        self::TYPE_BANK_BALANCE => 'Bank Balance',
        self::TYPE_DATA_CORRECTION => 'Data Correction',
        self::TYPE_DONATION_TO_OPS => 'Donation to OPS',
    ];

    /** Groups shown in the sidebar, form, and filters. */
    public const GROUPS = [
        'Income' => [
            self::TYPE_DONATION_INCOME,
            self::TYPE_BANK_INTEREST,
            self::TYPE_FD_INTEREST,
            self::TYPE_MF_INTEREST,
            self::TYPE_RD_INTEREST,
            self::TYPE_IT_RETURN,
        ],
        'Expenses' => [
            self::TYPE_DONATION_EXPENSE,
            self::TYPE_BANK_CHARGES,
            self::TYPE_PROFESSIONAL_CHARGES,
            self::TYPE_STATIONARY_CHARGES,
            self::TYPE_COURIER_CHARGES,
            self::TYPE_IT_PAID,
        ],
        'Investments' => [
            self::TYPE_FD_INVESTMENT,
            self::TYPE_RD_INVESTMENT,
            self::TYPE_MF_INVESTMENT,
            self::TYPE_FD_MATURITY,
            self::TYPE_RD_MATURITY,
            self::TYPE_MF_MATURITY,
        ],
        'Transfers & Balances' => [
            self::TYPE_BANK_TRANSFER,
            self::TYPE_BANK_BALANCE,
        ],
        'Utilities' => [
            self::TYPE_DATA_CORRECTION,
        ],
        'Donation to OPS' => [
            self::TYPE_DONATION_TO_OPS,
        ],
    ];

    /** Maturity types carry principal in `amount` plus interest/charges columns. */
    public const MATURITY_TYPES = [
        self::TYPE_FD_MATURITY,
        self::TYPE_RD_MATURITY,
        self::TYPE_MF_MATURITY,
    ];

    /** Scheme-prefixed maps for FD/RD/MF report grouping. */
    public const INVEST_TYPES = [
        'FD' => self::TYPE_FD_INVESTMENT,
        'RD' => self::TYPE_RD_INVESTMENT,
        'MF' => self::TYPE_MF_INVESTMENT,
    ];

    public const MATURITY_TYPE_MAP = [
        'FD' => self::TYPE_FD_MATURITY,
        'RD' => self::TYPE_RD_MATURITY,
        'MF' => self::TYPE_MF_MATURITY,
    ];

    public const INTEREST_TYPE_MAP = [
        'FD' => self::TYPE_FD_INTEREST,
        'RD' => self::TYPE_RD_INTEREST,
        'MF' => self::TYPE_MF_INTEREST,
    ];

    /** Types that increase an account balance. */
    public const INCOME_TYPES = [
        self::TYPE_DONATION_INCOME,
        self::TYPE_BANK_INTEREST,
        self::TYPE_FD_INTEREST,
        self::TYPE_MF_INTEREST,
        self::TYPE_RD_INTEREST,
        self::TYPE_FD_MATURITY,
        self::TYPE_RD_MATURITY,
        self::TYPE_MF_MATURITY,
        self::TYPE_IT_RETURN,
    ];

    /** Types that decrease an account balance. */
    public const EXPENSE_TYPES = [
        self::TYPE_DONATION_EXPENSE,
        self::TYPE_BANK_CHARGES,
        self::TYPE_PROFESSIONAL_CHARGES,
        self::TYPE_STATIONARY_CHARGES,
        self::TYPE_COURIER_CHARGES,
        self::TYPE_IT_PAID,
        self::TYPE_FD_INVESTMENT,
        self::TYPE_RD_INVESTMENT,
        self::TYPE_MF_INVESTMENT,
        self::TYPE_DONATION_TO_OPS,
    ];

    /** Types that require an account (income/expense ledger). */
    public const ACCOUNT_TYPES = [
        self::TYPE_DONATION_INCOME,
        self::TYPE_DONATION_EXPENSE,
        self::TYPE_BANK_INTEREST,
        self::TYPE_FD_INTEREST,
        self::TYPE_MF_INTEREST,
        self::TYPE_RD_INTEREST,
        self::TYPE_BANK_CHARGES,
        self::TYPE_FD_INVESTMENT,
        self::TYPE_RD_INVESTMENT,
        self::TYPE_MF_INVESTMENT,
        self::TYPE_FD_MATURITY,
        self::TYPE_RD_MATURITY,
        self::TYPE_MF_MATURITY,
        self::TYPE_PROFESSIONAL_CHARGES,
        self::TYPE_STATIONARY_CHARGES,
        self::TYPE_COURIER_CHARGES,
        self::TYPE_IT_RETURN,
        self::TYPE_IT_PAID,
        self::TYPE_BANK_BALANCE,
    ];

    protected $fillable = [
        'entry_type',
        'entry_date',
        'financial_year',
        'account_id',
        'to_account_id',
        'amount',
        'particulars',
        'donor_name',
        'donor_address',
        'nature_of_donation',
        'mode_of_receipt',
        'section_code',
        'donor_pan',
        'donor_bank_details',
        'receipt_number',
        'ops_amount',
        'tsp_amount',
        'others_amount',
        'scheme_name',
        'interest_amount',
        'bank_charges_amount',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'amount' => 'float',
            'ops_amount' => 'float',
            'tsp_amount' => 'float',
            'others_amount' => 'float',
            'interest_amount' => 'float',
            'bank_charges_amount' => 'float',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function toAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'to_account_id');
    }

    public function getLabelAttribute(): string
    {
        return self::LABELS[$this->entry_type] ?? ucfirst(str_replace('_', ' ', $this->entry_type));
    }

    public function getIsIncomeAttribute(): bool
    {
        return in_array($this->entry_type, self::INCOME_TYPES, true);
    }

    public function getIsExpenseAttribute(): bool
    {
        return in_array($this->entry_type, self::EXPENSE_TYPES, true);
    }

    public static function labelFor(string $type): string
    {
        return self::LABELS[$type] ?? ucfirst(str_replace('_', ' ', $type));
    }
}
