<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Entry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class EntryController extends Controller
{
    public function index(Request $request): View
    {
        $query = Entry::with(['account', 'toAccount']);

        $filters = $request->only(['entry_type', 'account_id', 'financial_year', 'from', 'to', 'search']);

        if (! empty($filters['entry_type'])) {
            $query->where('entry_type', $filters['entry_type']);
        }

        if (! empty($filters['financial_year'])) {
            $query->where('financial_year', $filters['financial_year']);
        }

        if (! empty($filters['account_id'])) {
            $query->where(fn ($q) => $q->where('account_id', $filters['account_id'])
                ->orWhere('to_account_id', $filters['account_id']));
        }

        if (! empty($filters['from'])) {
            $query->where('entry_date', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->where('entry_date', '<=', $filters['to']);
        }

        if (! empty($filters['search'])) {
            $query->where(fn ($q) => $q->where('particulars', 'like', '%'.$filters['search'].'%')
                ->orWhere('donor_name', 'like', '%'.$filters['search'].'%')
                ->orWhere('receipt_number', 'like', '%'.$filters['search'].'%')
                ->orWhere('notes', 'like', '%'.$filters['search'].'%'));
        }

        $entries = $query->orderByDesc('entry_date')->orderByDesc('id')->paginate(25)->withQueryString();

        $accounts = Account::orderBy('name')->get();
        $financialYears = Entry::query()
            ->whereNotNull('financial_year')
            ->distinct()
            ->orderByDesc('financial_year')
            ->pluck('financial_year');
        $totalAmount = (clone $query)->sum('amount');

        return view('admin.entries.index', compact('entries', 'accounts', 'financialYears', 'filters', 'totalAmount'));
    }

    public function create(): View
    {
        return view('admin.entries.form', [
            'entry' => new Entry,
            'accounts' => Account::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $this->applyTypeDefaults($data);
        $this->applyFinancialYear($data);

        Entry::create($data);

        return redirect()
            ->route('admin.entries.index')
            ->with('success', 'Entry recorded.');
    }

    public function edit(Entry $entry): View
    {
        return view('admin.entries.form', [
            'entry' => $entry,
            'accounts' => Account::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Entry $entry): RedirectResponse
    {
        $data = $this->validated($request);
        $this->applyTypeDefaults($data);
        $this->applyFinancialYear($data);

        $entry->update($data);

        return redirect()
            ->route('admin.entries.index')
            ->with('success', 'Entry updated.');
    }

    public function destroy(Entry $entry): RedirectResponse
    {
        $entry->delete();

        return redirect()
            ->route('admin.entries.index')
            ->with('success', 'Entry deleted.');
    }

    public function export(Request $request): StreamedResponse
    {
        $query = Entry::with(['account', 'toAccount']);

        if (! empty($request->entry_type)) {
            $query->where('entry_type', $request->entry_type);
        }
        if (! empty($request->financial_year)) {
            $query->where('financial_year', $request->financial_year);
        }
        if (! empty($request->account_id)) {
            $query->where(fn ($q) => $q->where('account_id', $request->account_id)
                ->orWhere('to_account_id', $request->account_id));
        }
        if (! empty($request->from)) {
            $query->where('entry_date', '>=', $request->from);
        }
        if (! empty($request->to)) {
            $query->where('entry_date', '<=', $request->to);
        }

        $entries = $query->orderBy('entry_date')->orderBy('id')->get();

        return response()->streamDownload(function () use ($entries) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'Date', 'Type', 'Account', 'Transfer To', 'Amount (₹)',
                'Particulars', 'Donor Name', 'PAN', 'Receipt No',
                'Scheme', 'Interest', 'Bank Charges',
                'To Ops', 'To TSP', 'To Others', 'Notes',
            ]);

            foreach ($entries as $e) {
                fputcsv($out, [
                    $e->entry_date?->format('Y-m-d'),
                    $e->label,
                    $e->account?->name,
                    $e->toAccount?->name,
                    $e->amount,
                    $e->particulars,
                    $e->donor_name,
                    $e->donor_pan,
                    $e->receipt_number,
                    $e->scheme_name,
                    $e->interest_amount,
                    $e->bank_charges_amount,
                    $e->ops_amount,
                    $e->tsp_amount,
                    $e->others_amount,
                    $e->notes,
                ]);
            }
            fclose($out);
        }, 'dap-trust-entries-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv']);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'entry_type' => ['required', 'string', 'in:'.implode(',', array_keys(Entry::LABELS))],
            'entry_date' => ['required', 'date'],
            'account_id' => ['nullable', 'exists:accounts,id', 'required_if:entry_type,bank_transfer'],
            'to_account_id' => ['nullable', 'exists:accounts,id', 'different:account_id', 'required_if:entry_type,bank_transfer'],
            'amount' => ['required', 'numeric', 'min:0'],
            'particulars' => ['nullable', 'string', 'max:1000'],
            'donor_name' => ['nullable', 'string', 'max:255'],
            'donor_address' => ['nullable', 'string', 'max:1000'],
            'nature_of_donation' => ['nullable', 'string', 'max:255'],
            'mode_of_receipt' => ['nullable', 'string', 'max:100'],
            'section_code' => ['nullable', 'string', 'max:50'],
            'donor_pan' => ['nullable', 'string', 'max:20'],
            'donor_bank_details' => ['nullable', 'string', 'max:1000'],
            'receipt_number' => ['nullable', 'string', 'max:100'],
            'ops_amount' => ['nullable', 'numeric', 'min:0'],
            'tsp_amount' => ['nullable', 'numeric', 'min:0'],
            'others_amount' => ['nullable', 'numeric', 'min:0'],
            'scheme_name' => ['nullable', 'string', 'max:255'],
            'interest_amount' => ['nullable', 'numeric', 'min:0'],
            'bank_charges_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    /**
     * Tag the entry with its April–March financial year (e.g. "FY25").
     */
    private function applyFinancialYear(array &$data): void
    {
        $year = \Carbon\Carbon::parse($data['entry_date']);
        $fy = $year->month >= 4 ? $year->year : $year->year - 1;
        $data['financial_year'] = 'FY'.str_pad((string) ($fy % 100), 2, '0', STR_PAD_LEFT);
    }

    private function applyTypeDefaults(array &$data): void
    {
        if ($data['entry_type'] === Entry::TYPE_BANK_TRANSFER) {
            $data['account_id'] = $data['account_id'] ?? null;
        }

        if (in_array($data['entry_type'], [Entry::TYPE_FD_MATURITY, Entry::TYPE_RD_MATURITY, Entry::TYPE_MF_MATURITY], true)) {
            $data['interest_amount'] = $data['interest_amount'] ?? 0;
            $data['bank_charges_amount'] = $data['bank_charges_amount'] ?? 0;
        }

        // Keep interest/charges columns clean for non-maturity entries
        if (! in_array($data['entry_type'], [Entry::TYPE_FD_MATURITY, Entry::TYPE_RD_MATURITY, Entry::TYPE_MF_MATURITY], true)) {
            unset($data['interest_amount'], $data['bank_charges_amount']);
        }

        // Donor fields only make sense for donation income
        if ($data['entry_type'] !== Entry::TYPE_DONATION_INCOME) {
            foreach (['donor_name', 'donor_address', 'nature_of_donation', 'mode_of_receipt', 'section_code', 'donor_pan', 'donor_bank_details', 'receipt_number'] as $field) {
                unset($data[$field]);
            }
        }
    }
}
