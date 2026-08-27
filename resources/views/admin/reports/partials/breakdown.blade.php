<div class="grid gap-6 lg:grid-cols-3">
    <div class="lg:col-span-1">
        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Totals by type</h3>
        <table class="w-full text-sm">
            <tbody>
            @foreach ($byType as $type => $value)
                <tr class="border-t border-slate-100">
                    <td class="py-2 pr-4 text-slate-700">{{ \App\Models\Entry::labelFor($type) }}</td>
                    <td class="py-2 text-right font-medium {{ $value >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                        ₹{{ indian_number_format($value, 2) }}
                    </td>
                </tr>
            @endforeach
            <tr class="border-t-2 border-slate-200 font-semibold text-slate-900">
                <td class="py-2 pr-4">Total</td>
                <td class="py-2 text-right">₹{{ indian_number_format($total, 2) }}</td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="lg:col-span-2">
        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Details</h3>
        <div class="max-h-96 overflow-auto rounded-lg border border-slate-100">
            <table class="w-full text-sm">
                <thead class="sticky top-0 bg-white">
                <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                    <th class="pb-2 pr-4">Date</th>
                    <th class="pb-2 pr-4">Type</th>
                    <th class="pb-2 pr-4">Account</th>
                    <th class="pb-2 text-right">Amount</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($entries as $entry)
                    <tr class="border-t border-slate-100">
                        <td class="whitespace-nowrap py-2 pr-4">{{ $entry->entry_date?->format('d-m-Y') }}</td>
                        <td class="py-2 pr-4">{{ $entry->label }}</td>
                        <td class="py-2 pr-4">{{ $entry->account?->name ?: '—' }}</td>
                        <td class="whitespace-nowrap py-2 text-right font-medium">₹{{ indian_number_format($entry->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-3 text-slate-500">No entries in the selected period.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
