@if ($itReturns->isEmpty())
    <p class="text-sm text-slate-500">No IT return entries in the selected period.</p>
@else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
            <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                <th class="pb-2 pr-4">Date</th>
                <th class="pb-2 pr-4">Account</th>
                <th class="pb-2 pr-4 text-right">Amount</th>
                <th class="pb-2">Particulars</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($itReturns as $entry)
                <tr class="border-t border-slate-100">
                    <td class="whitespace-nowrap py-2 pr-4">{{ $entry->entry_date?->format('d-m-Y') }}</td>
                    <td class="py-2 pr-4">{{ $entry->account?->name ?: '—' }}</td>
                    <td class="py-2 pr-4 text-right font-medium text-emerald-600">₹{{ number_format($entry->amount, 2) }}</td>
                    <td class="py-2">{{ $entry->particulars ?: '—' }}</td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
            <tr class="border-t-2 border-slate-200 font-semibold text-slate-900">
                <td class="py-2 pr-4" colspan="2">Total</td>
                <td class="py-2 pr-4 text-right">₹{{ number_format($itReturns->sum('amount'), 2) }}</td>
                <td></td>
            </tr>
            </tfoot>
        </table>
    </div>
@endif
