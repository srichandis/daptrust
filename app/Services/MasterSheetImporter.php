<?php

namespace App\Services;

use Illuminate\Support\Collection;

class MasterSheetImporter
{
    /**
     * Map the Excel Master-sheet column letters to entry_type + DB account key.
     * The MASTER sheet is the consolidated trust ledger (FY02 -> FY26).
     */
    public function columnMap(): array
    {
        return [
            // Cash
            'cash_donor'  => ['column' => 'D', 'entry_type' => 'donation_income'],
            'bank_transfer' => ['column' => 'E', 'entry_type' => 'bank_transfer'],
            'stationary'  => ['column' => 'F', 'entry_type' => 'stationary_charges'],
            'cash_donation' => ['column' => 'G', 'entry_type' => 'donation_expense'],
            'cash_balance' => ['column' => 'H', 'is_balance' => true],

            // Andhra Bank
            'ab_donor'  => ['column' => 'I', 'entry_type' => 'donation_income'],
            'ab_donation'  => ['column' => 'J', 'entry_type' => 'donation_expense'],
            'ab_bank_charges' => ['column' => 'K', 'entry_type' => 'bank_charges'],
            'ab_bank_interest' => ['column' => 'L', 'entry_type' => 'bank_interest'],
            'ab_fd_interest'  => ['column' => 'M', 'entry_type' => 'fd_interest'],
            'ab_rd_interest'  => ['column' => 'N', 'entry_type' => 'rd_interest'],
            'ab_mf_interest'  => ['column' => 'O', 'entry_type' => 'mf_interest'],
            'ab_professional' => ['column' => 'P', 'entry_type' => 'professional_charges'],
            'ab_stationary'  => ['column' => 'Q', 'entry_type' => 'stationary_charges'],
            'ab_bank_transfer' => ['column' => 'R', 'entry_type' => 'bank_transfer'],
            'ab_it_return'  => ['column' => 'S', 'entry_type' => 'it_return'],
            'ab_fd_maturity' => ['column' => 'T', 'entry_type' => 'fd_maturity'],
            'ab_rd_maturity' => ['column' => 'U', 'entry_type' => 'rd_maturity'],
            'ab_mf_maturity' => ['column' => 'V', 'entry_type' => 'mf_maturity'],
            'ab_fd_investment' => ['column' => 'W', 'entry_type' => 'fd_investment'],
            'ab_rd_investment' => ['column' => 'X', 'entry_type' => 'rd_investment'],
            'ab_mf_investment' => ['column' => 'Y', 'entry_type' => 'mf_investment'],
            'ab_balance' => ['column' => 'Z', 'is_balance' => true],

            // SBI
            'sbi_donor'  => ['column' => 'AA', 'entry_type' => 'donation_income'],
            'sbi_donation'  => ['column' => 'AB', 'entry_type' => 'donation_expense'],
            'sbi_bank_charges' => ['column' => 'AC', 'entry_type' => 'bank_charges'],
            'sbi_bank_interest' => ['column' => 'AD', 'entry_type' => 'bank_interest'],
            'sbi_fd_interest'  => ['column' => 'AE', 'entry_type' => 'fd_interest'],
            'sbi_rd_interest'  => ['column' => 'AF', 'entry_type' => 'rd_interest'],
            'sbi_mf_interest'  => ['column' => 'AG', 'entry_type' => 'mf_interest'],
            'sbi_professional' => ['column' => 'AH', 'entry_type' => 'professional_charges'],
            'sbi_stationary'  => ['column' => 'AI', 'entry_type' => 'stationary_charges'],
            'sbi_bank_transfer' => ['column' => 'AJ', 'entry_type' => 'bank_transfer'],
            'sbi_it_return'  => ['column' => 'AK', 'entry_type' => 'it_return'],
            'sbi_fd_maturity' => ['column' => 'AL', 'entry_type' => 'fd_maturity'],
            'sbi_rd_maturity' => ['column' => 'AM', 'entry_type' => 'rd_maturity'],
            'sbi_mf_maturity' => ['column' => 'AN', 'entry_type' => 'mf_maturity'],
            'sbi_fd_investment' => ['column' => 'AO', 'entry_type' => 'fd_investment'],
            'sbi_rd_investment' => ['column' => 'AP', 'entry_type' => 'rd_investment'],
            'sbi_mf_investment' => ['column' => 'AQ', 'entry_type' => 'mf_investment'],

            // HDFC
            'hdfc_donor'  => ['column' => 'AS', 'entry_type' => 'donation_income'],
            'hdfc_donation' => ['column' => 'AT', 'entry_type' => 'donation_expense'],
            'hdfc_bank_charges' => ['column' => 'AU', 'entry_type' => 'bank_charges'],
            'hdfc_bank_interest' => ['column' => 'AV', 'entry_type' => 'bank_interest'],
            'hdfc_fd_interest'  => ['column' => 'AW', 'entry_type' => 'fd_interest'],
            'hdfc_rd_interest'  => ['column' => 'AX', 'entry_type' => 'rd_interest'],
            'hdfc_mf_interest'  => ['column' => 'AY', 'entry_type' => 'mf_interest'],
            'hdfc_professional' => ['column' => 'AZ', 'entry_type' => 'professional_charges'],
            'hdfc_stationary'  => ['column' => 'BA', 'entry_type' => 'stationary_charges'],
            'hdfc_bank_transfer' => ['column' => 'BB', 'entry_type' => 'bank_transfer'],
            'hdfc_it_return'  => ['column' => 'BC', 'entry_type' => 'it_return'],
            'hdfc_fd_maturity' => ['column' => 'BD', 'entry_type' => 'fd_maturity'],
            'hdfc_rd_maturity' => ['column' => 'BE', 'entry_type' => 'rd_maturity'],
            'hdfc_mf_maturity' => ['column' => 'BF', 'entry_type' => 'mf_maturity'],
            'hdfc_fd_investment' => ['column' => 'BG', 'entry_type' => 'fd_investment'],
            'hdfc_rd_investment' => ['column' => 'BH', 'entry_type' => 'rd_investment'],
            'hdfc_mf_investment' => ['column' => 'BI', 'entry_type' => 'mf_investment'],

            // Donation to OPS
            'donation_to_ops' => ['column' => 'BL', 'entry_type' => 'donation_to_ops'],
        ];
    }

    /**
     * Returns the master-sheet column letters that carry nonzero transaction values.
     */
    public function transactionColumns(): array
    {
        $cols = [];
        foreach ($this->columnMap() as $key => $map) {
            if (isset($map['is_balance'])) {
                continue;
            }
            $cols[] = $map['column'];
        }

        return $cols;
    }

    /**
     * Normalizes the master-sheet rows into ledger entries.
     *
     * One Excel row is not imported as one database row. The ledger is normalised,
     * so a transaction in several bank columns becomes one entry per column, each
     * carrying the same date, particulars, and account-specific amount.
     */
    public function collectEntries(array $rows): Collection
    {
        $entries = new Collection();
        $cols = $this->transactionColumns();

        foreach ($rows as $row) {
            $description = $row['C'] ?? null;

            // The MASTER sheet repeats its summary totals below the ledger
            // (starting with DONOR / RECEIPTS). Stop there so those totals are
            // never imported as transactions.
            if (is_string($description) && strtoupper(trim($description)) === 'DONOR / RECEIPTS') {
                break;
            }

            // Ignore C/F balance rows and any row without a valid date.
            $date = $this->normalizeDate($row['B'] ?? null);
            if ($date === null) {
                continue;
            }

            // Skip C/F carry-forward rows; those are balances, not transactions.
            if (is_string($description) && strtoupper(trim($description)) === 'C/F') {
                continue;
            }

            foreach ($cols as $col) {
                if (! isset($row[$col])) {
                    continue;
                }

                $value = $row[$col];
                if (! is_numeric($value)) {
                    continue;
                }

                $value = (float) $value;
                if ($value === 0.0) {
                    continue;
                }

                $entryType = $this->entryTypeFor($col);
                if (!$entryType) {
                    continue;
                }

                $entries->push([
                    'column'      => $col,
                    'account'     => $this->accountKeyFor($col),
                    'entry_type'  => $entryType,
                    'amount'      => $value,
                    'particulars' => $description,
                    'date'        => $date,
                ]);
            }
        }

        return $entries->sortBy(fn (array $a) => $a['column']);
    }

    private function entryTypeFor(string $column): ?string
    {
        $map = $this->columnMap();
        foreach ($map as $key => $field) {
            if ($field['column'] === $column) {
                return $field['entry_type'] ?? null;
            }
        }

        return null;
    }

    /**
     * Maps a MASTER-sheet column to the account that owns it, using the same
     * blocks as the sheet: Cash (D-H), Andhra Bank (I-Z), SBI (AA-AR) and
     * HDFC (AS-BJ). The DONATION TO OPS column sits outside those blocks and
     * has no account.
     */
    private function accountKeyFor(string $column): ?string
    {
        $index = $this->columnIndex($column);

        return match (true) {
            $index >= 4 && $index <= 8 => 'Cash Account',
            $index >= 9 && $index <= 26 => 'Andhra Bank',
            $index >= 27 && $index <= 44 => 'State Bank of India',
            $index >= 45 && $index <= 62 => 'HDFC Bank',
            default => null,
        };
    }

    private function columnIndex(string $column): int
    {
        $index = 0;
        foreach (str_split(strtoupper($column)) as $character) {
            $index = $index * 26 + (ord($character) - 64);
        }

        return $index;
    }

    /**
     * The MASTER sheet stores dates as text (d.m.y or d.m.Y), and some cells
     * arrive as Excel datetimes; normalise both to a MySQL date string.
     */
    private function normalizeDate(mixed $value): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $value = str_replace(',', '.', trim($value));

        // The MASTER sheet contains a truncated four-digit year (31.07.202);
        // restore the missing final digit (2020) so the row is not lost.
        if (preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{3})$/', $value, $matches)) {
            $value = $matches[1].'.'.$matches[2].'.'.$matches[3].'0';
        }

        foreach (['d.m.Y', 'd.m.y', 'd/m/Y', 'd/m/y', 'Y-m-d', 'd-m-Y'] as $format) {
            $date = \DateTimeImmutable::createFromFormat('!'.$format, $value);
            if ($date !== false && $date->format($format) === $value) {
                return $date->format('Y-m-d');
            }
        }

        try {
            return (new \DateTimeImmutable($value))->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }
}
