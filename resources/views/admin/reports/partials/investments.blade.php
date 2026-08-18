<div class="grid gap-4 md:grid-cols-3">
    <div class="rounded-xl border border-slate-200 p-4">
        <p class="text-xs uppercase tracking-wide text-slate-500">Total {{ $investmentType }} invested</p>
        <p class="mt-1 text-2xl font-bold text-slate-900">₹{{ number_format($investmentTotal, 2) }}</p>
        <p class="text-xs text-slate-400">{{ $investments->count() }} deposit{{ $investments->count() === 1 ? '' : 's' }}</p>
    </div>
    <div class="rounded-xl border border-slate-200 p-4">
        <p class="text-xs uppercase tracking-wide text-slate-500">Total matured</p>
        <p class="mt-1 text-2xl font-bold text-slate-900">₹{{ number_format($maturityTotal, 2) }}</p>
        <p class="text-xs text-slate-400">{{ $maturities->count() }} maturity event{{ $maturities->count() === 1 ? '' : 's' }}</p>
    </div>
    <div class="rounded-xl border border-slate-200 p-4">
        <p class="text-xs uppercase tracking-wide text-slate-500">Interest earned</p>
        <p class="mt-1 text-2xl font-bold text-emerald-600">₹{{ number_format($interestTotal, 2) }}</p>
        <p class="text-xs text-slate-400">Periodic + at maturity</p>
    </div>
</div>

<div class="mt-6">
    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">{{ $investmentType }} investments</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
            <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                <th class="pb-2 pr-4">Bank</th>
                <th class="pb-2 pr-4">Invested on</th>
                <th class="pb-2 pr-4 text-right">Amount</th>
                <th class="pb-2 pr-4">{{ $investmentType }} number</th>
                <th class="pb-2 pr-4">Interest rate</th>
                <th class="pb-2">Maturity date</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($investments as $entry)
                <tr class="border-t border-slate-100">
                    <td class="py-2 pr-4">{{ $entry->account?->name ?: '—' }}</td>
                    <td class="whitespace-nowrap py-2 pr-4">{{ $entry->entry_date?->format('d-m-Y') }}</td>
                    <td class="py-2 pr-4 text-right font-medium">₹{{ number_format($entry->amount, 2) }}</td>
                    <td class="py-2 pr-4 text-slate-400">—</td>
                    <td class="py-2 pr-4 text-slate-400">—</td>
                    <td class="py-2 text-slate-400">—</td>
                </tr>
            @empty
                <tr><td colspan="6" class="py-3 text-slate-500">No {{ $investmentType }} investments in the selected period.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6">
    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">{{ $investmentType }} maturities</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
            <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                <th class="pb-2 pr-4">Maturity date</th>
                <th class="pb-2 pr-4">Bank</th>
                <th class="pb-2 pr-4 text-right">Principal</th>
                <th class="pb-2 pr-4 text-right">Interest earned</th>
                <th class="pb-2">Particulars</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($maturities as $entry)
                <tr class="border-t border-slate-100">
                    <td class="whitespace-nowrap py-2 pr-4">{{ $entry->entry_date?->format('d-m-Y') }}</td>
                    <td class="py-2 pr-4">{{ $entry->account?->name ?: '—' }}</td>
                    <td class="py-2 pr-4 text-right">₹{{ number_format($entry->amount, 2) }}</td>
                    <td class="py-2 pr-4 text-right text-emerald-600">₹{{ number_format($entry->interest_amount ?? 0, 2) }}</td>
                    <td class="py-2">{{ $entry->particulars ?: '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="py-3 text-slate-500">No {{ $investmentType }} maturities in the selected period.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<p class="mt-6 text-xs text-slate-400">
    Note: certificate numbers, interest rates and maturity dates for still-active deposits are not part of the imported ledger and can be added to individual entries.
</p>
