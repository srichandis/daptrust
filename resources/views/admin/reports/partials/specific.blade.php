<div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
        <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
            <th class="pb-2 pr-4">Date</th>
            <th class="pb-2 pr-4">Type</th>
            <th class="pb-2 pr-4">Account</th>
            <th class="pb-2 pr-4 text-right">Amount</th>
            <th class="pb-2">Particulars</th>
        </tr>
        </thead>
        <tbody>
        @forelse ($entries as $entry)
            <tr class="border-t border-slate-100">
                <td class="whitespace-nowrap py-2 pr-4">{{ $entry->entry_date?->format('d-m-Y') }}</td>
                <td class="py-2 pr-4">
                    <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $entry->is_income ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                        {{ $entry->label }}
                    </span>
                </td>
                <td class="py-2 pr-4">
                    {{ $entry->account?->name ?: '—' }}
                    @if ($entry->toAccount)
                        <span class="text-slate-400">→ {{ $entry->toAccount->name }}</span>
                    @endif
                </td>
                <td class="whitespace-nowrap py-2 pr-4 text-right font-medium {{ $entry->is_income ? 'text-emerald-600' : 'text-rose-600' }}">
                    ₹{{ number_format($entry->amount, 2) }}
                </td>
                <td class="max-w-[16rem] py-2 text-slate-500">{{ $entry->particulars ?: '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="py-3 text-slate-500">No entries in the selected period.</td></tr>
        @endforelse
        </tbody>
        <tfoot>
        <tr class="border-t-2 border-slate-200 font-semibold text-slate-900">
            <td class="py-2 pr-4" colspan="3">Total ({{ $entries->count() }} entries)</td>
            <td class="py-2 pr-4 text-right">₹{{ number_format($entries->sum('amount'), 2) }}</td>
            <td></td>
        </tr>
        </tfoot>
    </table>
</div>
