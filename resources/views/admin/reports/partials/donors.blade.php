@if ($donors->isEmpty())
    <p class="text-sm text-slate-500">No donation receipts in the selected period.</p>
@else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
            <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                <th class="pb-2 pr-4">Date</th>
                <th class="pb-2 pr-4">Donor name</th>
                <th class="pb-2 pr-4">Address</th>
                <th class="pb-2 pr-4">Nature</th>
                <th class="pb-2 pr-4">Mode of receipt</th>
                <th class="pb-2 pr-4 text-right">Amount</th>
                <th class="pb-2 pr-4">Section</th>
                <th class="pb-2 pr-4">PAN</th>
                <th class="pb-2 pr-4">Bank details</th>
                <th class="pb-2">Receipt no.</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($donors as $entry)
                <tr class="border-t border-slate-100 align-top">
                    <td class="whitespace-nowrap py-2 pr-4">{{ $entry->entry_date?->format('d-m-Y') }}</td>
                    <td class="py-2 pr-4 font-medium text-slate-800">{{ $entry->donor_name ?: '—' }}</td>
                    <td class="max-w-[10rem] py-2 pr-4 text-slate-500">{{ $entry->donor_address ?: '—' }}</td>
                    <td class="py-2 pr-4">{{ $entry->nature_of_donation ?: '—' }}</td>
                    <td class="py-2 pr-4">{{ $entry->mode_of_receipt ?: '—' }}</td>
                    <td class="whitespace-nowrap py-2 pr-4 text-right font-medium text-emerald-600">₹{{ number_format($entry->amount, 2) }}</td>
                    <td class="py-2 pr-4">{{ $entry->section_code ?: '—' }}</td>
                    <td class="py-2 pr-4">{{ $entry->donor_pan ?: '—' }}</td>
                    <td class="max-w-[10rem] py-2 pr-4 text-slate-500">{{ $entry->donor_bank_details ?: '—' }}</td>
                    <td class="whitespace-nowrap py-2">{{ $entry->receipt_number ?: '—' }}</td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
            <tr class="border-t-2 border-slate-200 font-semibold text-slate-900">
                <td class="py-2 pr-4" colspan="5">Total receipts ({{ $donors->count() }})</td>
                <td class="py-2 pr-4 text-right">₹{{ number_format($donors->sum('amount'), 2) }}</td>
                <td colspan="4"></td>
            </tr>
            </tfoot>
        </table>
    </div>
@endif
