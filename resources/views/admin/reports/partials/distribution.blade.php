<p class="mb-4 text-sm text-slate-500">
    Donations distributed by the trust for educational purposes, grouped by recipient.
</p>

<div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead>
        <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
            <th class="pb-2 pr-4">Recipient</th>
            <th class="pb-2 pr-4 text-right">Payments</th>
            <th class="pb-2 text-right">Total distributed</th>
        </tr>
        </thead>
        <tbody>
        @forelse ($byRecipient as $recipient => $info)
            <tr class="border-t border-slate-100">
                <td class="py-2 pr-4 font-medium text-slate-800">{{ $recipient }}</td>
                <td class="py-2 pr-4 text-right text-slate-500">{{ $info['count'] }}</td>
                <td class="py-2 text-right font-medium text-rose-600">₹{{ indian_number_format($info['total'], 2) }}</td>
            </tr>
        @empty
            <tr><td colspan="3" class="py-3 text-slate-500">No donations distributed in the selected period.</td></tr>
        @endforelse
        </tbody>
        <tfoot>
        <tr class="border-t-2 border-slate-200 font-semibold text-slate-900">
            <td class="py-2 pr-4">Total</td>
            <td class="py-2 pr-4 text-right">{{ $entries->count() }}</td>
            <td class="py-2 text-right">₹{{ indian_number_format($total, 2) }}</td>
        </tr>
        </tfoot>
    </table>
</div>

@if ($byRecipient->isNotEmpty())
    <div class="mt-6">
        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Distribution share</h3>
        @foreach ($byRecipient->take(8) as $recipient => $info)
            @php $pct = $total > 0 ? round($info['total'] / $total * 100, 1) : 0; @endphp
            <div class="mb-2 flex items-center gap-3">
                <span class="w-48 truncate text-sm text-slate-600">{{ $recipient }}</span>
                <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full bg-emerald-500" style="width: {{ min($pct, 100) }}%"></div>
                </div>
                <span class="w-24 text-right text-xs text-slate-500">{{ $pct }}%</span>
            </div>
        @endforeach
    </div>
@endif
