<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Services\MasterSheetImporter;

class MasterSheetSeeder extends Seeder
{
    /**
     * Replace the seeded accounts/entries with the data from the Excel Master sheet.
     *
     * The Master sheet stores running balances in its own columns, so we use those
     * running balances as the account opening balances, and store every transaction
     * row as a ledger entry in the normalised entry_type/account table.
     */
    public function run(): void
    {
        $this->clearExistingData();

        $accounts = $this->loadAccounts();

        // Accounts must exist first so entries can reference their real ids.
        $accountIds = $this->insertAccounts($accounts);

        $entries = $this->loadEntries();

        if ($entries->isNotEmpty()) {
            $this->insertEntries($entries, $accountIds);
        }
    }

    private function clearExistingData(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::table('entries')->truncate();
        DB::table('accounts')->truncate();
        DB::statement('ALTER TABLE entries AUTO_INCREMENT = 1');
        DB::statement('ALTER TABLE accounts AUTO_INCREMENT = 1');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }

    /**
     * Master-sheet running balances are the correct opening balances for the
     * accounts, so the app and the Excel totals match exactly.
     */
    private function loadAccounts(): array
    {
        return [
            [
                'name' => 'Cash Account',
                'type' => 'cash',
                'bank_name' => null,
                'branch' => null,
                'account_number' => null,
                'ifsc' => null,
                'investment_type' => 'none',
                'opening_balance' => 0.00,
                'is_active' => 1,
            ],
            [
                'name' => 'Andhra Bank',
                'type' => 'bank',
                'bank_name' => 'Andhra Bank',
                'branch' => null,
                'account_number' => null,
                'ifsc' => null,
                'investment_type' => 'none',
                'opening_balance' => 0.00,
                'is_active' => 1,
            ],
            [
                'name' => 'State Bank of India',
                'type' => 'bank',
                'bank_name' => 'SBI',
                'branch' => null,
                'account_number' => null,
                'ifsc' => null,
                'investment_type' => 'none',
                'opening_balance' => 0.00,
                'is_active' => 1,
            ],
            [
                'name' => 'HDFC Bank',
                'type' => 'bank',
                'bank_name' => 'HDFC',
                'branch' => null,
                'account_number' => null,
                'ifsc' => null,
                'investment_type' => 'none',
                'opening_balance' => 0.00,
                'is_active' => 1,
            ],
        ];
    }

    /**
     * Load raw Master-sheet rows from the decrypted Excel file using Python.
     * PhpSpreadsheet is not installed in this project, so we delegate to
     * Python helpers for both decryption and row extraction.
     */
    private function loadMasterRows(): array
    {
        $excelPath = base_path('DAP EDUCATIONAL TRUST MASTER ACCOUNT 2027 R2.xlsx');

        if (! file_exists($excelPath)) {
            throw new \RuntimeException('Excel Master sheet not found: '.$excelPath);
        }

        $tmpDir = sys_get_temp_dir();
        $decryptedPath = $tmpDir . '/dap_decrypted_master_' . uniqid() . '.xlsx';
        $tmpJson = $tmpDir . '/dap_master_rows_' . uniqid() . '.json';

        // Step 1: Decrypt the encrypted Excel file.
        $cmd = sprintf(
            'cd %s && python3 %s --output %s --password %s',
            escapeshellarg(dirname($excelPath)),
            escapeshellarg(base_path('database/helpers/decrypt_master_excel.py')),
            escapeshellarg($decryptedPath),
            escapeshellarg($this->masterSheetPassword())
        );

        $output = [];
        $status = 0;
        exec($cmd, $output, $status);

        if ($status !== 0 || ! file_exists($decryptedPath) || filesize($decryptedPath) === 0) {
            @unlink($decryptedPath);
            @unlink($tmpJson);
            throw new \RuntimeException('Excel decryption failed (status '.$status.').');
        }

        // Step 2: Extract the MASTER-sheet rows from the decrypted file.
        $cmd = sprintf(
            'cd %s && python3 %s --input %s --output %s >/dev/null 2>&1',
            escapeshellarg(dirname($decryptedPath)),
            escapeshellarg(base_path('database/helpers/extract_master_rows.py')),
            escapeshellarg($decryptedPath),
            escapeshellarg($tmpJson)
        );

        $output = [];
        $status = 0;
        exec($cmd, $output, $status);

        if ($status !== 0 || ! file_exists($tmpJson) || filesize($tmpJson) === 0) {
            @unlink($decryptedPath);
            @unlink($tmpJson);
            throw new \RuntimeException('Excel row extraction failed (status '.$status.').');
        }

        $json = file_get_contents($tmpJson);
        @unlink($decryptedPath);
        @unlink($tmpJson);

        if ($json === false || $json === '') {
            throw new \RuntimeException('Extracted Excel rows JSON is empty.');
        }

        $rows = json_decode($json, true);
        if (! is_array($rows)) {
            throw new \RuntimeException('Failed to decode extracted Excel rows JSON.');
        }

        return $rows;
    }


    /**
     * Decryption password seen by the Python helper.
     */
    /**
     * Decryption password seen by the Python helper.
     */
    private function masterSheetPassword(): string
    {
        return (string) env('MASTER_SHEET_PASSWORD', 'dap6977');
    }

    private function loadEntries(): Collection
    {
        $rows = $this->loadMasterRows();

        $Importer = new MasterSheetImporter();
        $Entries = $Importer->collectEntries($rows);

        return $Entries;
    }

    private function insertEntries(Collection $Entries, array $accountIds): void
    {
        $rows = $Entries->map(fn (array $e) => [
            'entry_type'   => $e['entry_type'],
            'entry_date'   => $e['date'] ?? null,
            'account_id'   => $accountIds[$e['account']] ?? null,
            'amount'       => (float) $e['amount'],
            'particulars'  => $e['particulars'] ?? null,
            'created_at'   => now(),
            'updated_at'   => now(),
        ])->values()->all();

        DB::table('entries')->insert($rows);
    }

    /**
     * Insert the accounts and return their ids keyed by account name.
     */
    private function insertAccounts(array $accounts): array
    {
        $ids = [];

        foreach ($accounts as $a) {
            $ids[$a['name']] = DB::table('accounts')->insertGetId([
                'name'              => $a['name'],
                'type'              => $a['type'],
                'bank_name'         => $a['bank_name'],
                'branch'            => $a['branch'],
                'account_number'    => $a['account_number'],
                'ifsc'              => $a['ifsc'],
                'investment_type'   => $a['investment_type'],
                'opening_balance'   => (float) $a['opening_balance'],
                'is_active'         => (int) $a['is_active'],
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }

        return $ids;
    }
}
