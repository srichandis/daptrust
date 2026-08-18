<p class="mb-4 text-sm text-slate-500">Entries dated 31 March within the selected period.</p>
@if ($entries->isEmpty())
    <p class="text-sm text-slate-500">No 31 March entries in the selected period.</p>
@else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
            <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                <th class="pb-2 pr-4">Date</th>
                <th class="pb-2 pr-4">Type</th>
                <th class="pb-2 pr-4">Account</th>
                <th class="pb-2 pr-4 text-right">Amount</th>
                <th class="pb-2">Notes</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($entries as $entry)
                <tr class="border-t border-slate-100">
                    <td class="whitespace-nowrap py-2 pr-4">{{ $entry->entry_date?->format('d-m-Y') }}</td>
                    <td class="py-2 pr-4">{{ $entry->label }}</td>
                    <td class="py-2 pr-4">{{ $entry->account?->name ?: '—' }}</td>
                    <td class="whitespace-nowrap py-2 pr-4 text-right font-medium {{ $entry->is_income ? 'text-emerald-600' : 'text-rose-600' }}">
                        ₹{{ number_format($entry->amount, 2) }}
                    </td>
                    <td class="py-2">{{ $entry->notes ?: '—' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endif
