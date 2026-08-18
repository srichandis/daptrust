<div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
        <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
            <th class="pb-2 pr-4">Maturity date</th>
            <th class="pb-2 pr-4">Bank</th>
            <th class="pb-2 pr-4 text-right">Principal</th>
            <th class="pb-2 pr-4 text-right">Interest</th>
            <th class="pb-2">Scheme / particulars</th>
        </tr>
        </thead>
        <tbody>
        @forelse ($entries as $entry)
            <tr class="border-t border-slate-100">
                <td class="whitespace-nowrap py-2 pr-4">{{ $entry->entry_date?->format('d-m-Y') }}</td>
                <td class="py-2 pr-4">{{ $entry->account?->name ?: '—' }}</td>
                <td class="py-2 pr-4 text-right font-medium">₹{{ number_format($entry->amount, 2) }}</td>
                <td class="py-2 pr-4 text-right text-emerald-600">₹{{ number_format($entry->interest_amount ?? 0, 2) }}</td>
                <td class="py-2">{{ $entry->scheme_name ?: ($entry->particulars ?: '—') }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="py-3 text-slate-500">No {{ $maturityType }} maturities in the selected period.</td></tr>
        @endforelse
        </tbody>
        <tfoot>
        <tr class="border-t-2 border-slate-200 font-semibold text-slate-900">
            <td class="py-2 pr-4" colspan="2">Total matured</td>
            <td class="py-2 pr-4 text-right">₹{{ number_format($entries->sum('amount'), 2) }}</td>
            <td class="py-2 pr-4 text-right">₹{{ number_format($entries->sum('interest_amount'), 2) }}</td>
            <td></td>
        </tr>
        </tfoot>
    </table>
</div>
