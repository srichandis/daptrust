<div class="mb-4 flex flex-wrap gap-3">
    @foreach ($statementAccounts as $account)
        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm">
            <span class="font-medium text-slate-700">{{ $account->name }}</span>
            <span class="ml-2 text-slate-500">Opening ₹{{ indian_number_format($statementOpening[$account->id] ?? 0, 2) }}</span>
        </div>
    @endforeach
</div>

@if (empty($statementRows))
    <p class="text-sm text-slate-500">No entries in the selected period.</p>
@else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
            <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                <th class="pb-2 pr-4">Date</th>
                <th class="pb-2 pr-4">Particulars</th>
                <th class="pb-2 pr-4">Type</th>
                <th class="pb-2 pr-4">Account</th>
                <th class="pb-2 pr-4 text-right">Debit (₹)</th>
                <th class="pb-2 pr-4 text-right">Credit (₹)</th>
                <th class="pb-2 text-right">Balance (₹)</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($statementRows as $row)
                @php $entry = $row['entry']; @endphp
                <tr class="border-t border-slate-100">
                    <td class="whitespace-nowrap py-2 pr-4">{{ $entry->entry_date?->format('d-m-Y') }}</td>
                    <td class="max-w-[16rem] py-2 pr-4 text-slate-600">{{ $entry->particulars ?: ($entry->donor_name ?: '—') }}</td>
                    <td class="py-2 pr-4">
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-medium
                            {{ $entry->is_income ? 'bg-emerald-100 text-emerald-700' : ($entry->is_expense ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-600') }}">
                            {{ $entry->label }}
                        </span>
                    </td>
                    <td class="whitespace-nowrap py-2 pr-4">
                        {{ $entry->account?->name ?: '—' }}
                        @if ($entry->toAccount)
                            <span class="text-slate-400">→ {{ $entry->toAccount->name }}</span>
                        @endif
                    </td>
                    <td class="whitespace-nowrap py-2 pr-4 text-right text-rose-600">{{ $row['debit'] !== null ? '₹'.indian_number_format($row['debit'], 2) : '' }}</td>
                    <td class="whitespace-nowrap py-2 pr-4 text-right text-emerald-600">{{ $row['credit'] !== null ? '₹'.indian_number_format($row['credit'], 2) : '' }}</td>
                    <td class="whitespace-nowrap py-2 text-right font-medium text-slate-700">
                        {{ $row['balance'] !== null ? '₹'.indian_number_format($row['balance'], 2) : '' }}
                    </td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
            @foreach ($statementAccounts as $account)
                <tr class="border-t-2 border-slate-200">
                    <td class="py-2 pr-4 font-semibold text-slate-900" colspan="6">Closing — {{ $account->name }}</td>
                    <td class="py-2 text-right font-semibold text-slate-900">₹{{ indian_number_format($statementClosing[$account->id] ?? 0, 2) }}</td>
                </tr>
            @endforeach
            </tfoot>
        </table>
    </div>
@endif
