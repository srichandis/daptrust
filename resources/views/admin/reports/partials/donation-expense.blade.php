<div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
        <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
            <th class="pb-2 pr-4">Date</th>
            <th class="pb-2 pr-4">Recipient</th>
            <th class="pb-2 pr-4">Account</th>
            <th class="pb-2 pr-4 text-right">Amount</th>
            <th class="pb-2">Notes</th>
        </tr>
        </thead>
        <tbody>
        @forelse ($entries as $entry)
            <tr class="border-t border-slate-100">
                <td class="whitespace-nowrap py-2 pr-4">{{ $entry->entry_date?->format('d-m-Y') }}</td>
                <td class="py-2 pr-4 font-medium text-slate-800">{{ $entry->particulars ?: '—' }}</td>
                <td class="py-2 pr-4">{{ $entry->account?->name ?: '—' }}</td>
                <td class="whitespace-nowrap py-2 pr-4 text-right font-medium text-rose-600">₹{{ indian_number_format($entry->amount, 2) }}</td>
                <td class="py-2">{{ $entry->notes ?: '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="py-3 text-slate-500">No donation expenses in the selected period.</td></tr>
        @endforelse
        </tbody>
        <tfoot>
        <tr class="border-t-2 border-slate-200 font-semibold text-slate-900">
            <td class="py-2 pr-4" colspan="3">Total distributed ({{ $entries->count() }} payments)</td>
            <td class="py-2 pr-4 text-right">₹{{ indian_number_format($total, 2) }}</td>
            <td></td>
        </tr>
        </tfoot>
    </table>
</div>
