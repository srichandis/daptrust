<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'bank_name',
        'branch',
        'account_number',
        'ifsc',
        'investment_type',
        'opening_balance',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'float',
            'is_active' => 'boolean',
        ];
    }

    public function entries(): HasMany
    {
        return $this->hasMany(Entry::class, 'account_id');
    }

    public function outgoingTransfers(): HasMany
    {
        return $this->hasMany(Entry::class, 'account_id')->where('entry_type', 'bank_transfer');
    }

    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(Entry::class, 'to_account_id')->where('entry_type', 'bank_transfer');
    }

    /**
     * Computed balance = opening balance + income - expenses - investments, plus
     * transfers: paired transfers move money between accounts, while imported
     * one-sided transfers are signed net movements added to the account.
     */
    public function balance(): float
    {
        return $this->balanceAsOf(null);
    }

    /**
     * Balance as of a date (inclusive), or the all-time balance when null.
     */
    public function balanceAsOf(?string $date = null): float
    {
        $sum = function (string $column, array $types, string $accountColumn) use ($date): float {
            $query = Entry::where($accountColumn, $this->id)
                ->whereIn('entry_type', $types);
            if ($date !== null) {
                $query->where('entry_date', '<=', $date);
            }

            return (float) $query->sum($column);
        };

        $income = $sum('amount', Entry::INCOME_TYPES, 'account_id');

        // Donation-to-OPS is recorded against an account for reporting, but the
        // Excel balances never deduct it, so it must not move the balance.
        $expenseTypes = array_values(array_diff(Entry::EXPENSE_TYPES, [Entry::TYPE_DONATION_TO_OPS]));
        $expense = $sum('amount', $expenseTypes, 'account_id');

        // Maturity interest is credited to the account; charges are debited.
        $maturityInterest = $sum('interest_amount', Entry::MATURITY_TYPES, 'account_id');
        $maturityCharges = $sum('bank_charges_amount', Entry::MATURITY_TYPES, 'account_id');

        // Imported MASTER transfers are one-sided and carry their sign (the
        // Excel BANK TRANSFER columns are net movements), so they are added
        // as-is. Manually recorded transfers carry a destination account and a
        // positive amount, so they move money out of this account instead.
        $transfersIn = $sum('amount', ['bank_transfer'], 'to_account_id');
        $singleSidedTransfers = (float) Entry::where('account_id', $this->id)
            ->where('entry_type', Entry::TYPE_BANK_TRANSFER)
            ->whereNull('to_account_id')
            ->when($date !== null, fn ($query) => $query->where('entry_date', '<=', $date))
            ->sum('amount');
        $pairedTransfersOut = (float) Entry::where('account_id', $this->id)
            ->where('entry_type', Entry::TYPE_BANK_TRANSFER)
            ->whereNotNull('to_account_id')
            ->when($date !== null, fn ($query) => $query->where('entry_date', '<=', $date))
            ->sum('amount');

        return round(
            $this->opening_balance + $income - $expense
            + $maturityInterest - $maturityCharges
            + $singleSidedTransfers + $transfersIn - $pairedTransfersOut,
            2,
        );
    }
}
