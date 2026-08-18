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
     * Computed balance = opening balance + income - expenses - investments - transfers out + transfers in.
     * Transfer entries are excluded from the plain income/expense sums and handled via to/from columns.
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
        $expense = $sum('amount', Entry::EXPENSE_TYPES, 'account_id');

        // Maturity interest is credited to the account; charges are debited.
        $maturityInterest = $sum('interest_amount', Entry::MATURITY_TYPES, 'account_id');
        $maturityCharges = $sum('bank_charges_amount', Entry::MATURITY_TYPES, 'account_id');

        $transfersIn = $sum('amount', ['bank_transfer'], 'to_account_id');
        $transfersOut = $sum('amount', ['bank_transfer'], 'account_id');

        return round(
            $this->opening_balance + $income - $expense
            + $maturityInterest - $maturityCharges
            + $transfersIn - $transfersOut,
            2,
        );
    }
}
