<div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
    @foreach ($accountDetails as $item)
        @php $account = $item['account']; @endphp
        <div class="rounded-xl border border-slate-200 p-4">
            <div class="flex items-center justify-between">
                <h4 class="font-medium text-slate-900">{{ $account->name }}</h4>
                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">{{ ucfirst($account->type) }}</span>
            </div>
            <dl class="mt-3 space-y-2 text-sm text-slate-600">
                <div class="flex justify-between"><dt>Bank</dt><dd>{{ $account->bank_name ?: '—' }}</dd></div>
                <div class="flex justify-between"><dt>Account no.</dt><dd>{{ $account->account_number ?: '—' }}</dd></div>
                <div class="flex justify-between"><dt>Branch</dt><dd>{{ $account->branch ?: '—' }}</dd></div>
                <div class="flex justify-between"><dt>IFSC</dt><dd>{{ $account->ifsc ?: '—' }}</dd></div>
                <div class="flex justify-between"><dt>Entries</dt><dd>{{ $account->entries_count }}</dd></div>
                <div class="flex justify-between border-t border-slate-100 pt-2 font-semibold text-slate-900">
                    <dt>Balance</dt><dd>₹{{ indian_number_format($item['balance'], 2) }}</dd>
                </div>
            </dl>
        </div>
    @endforeach
</div>
<p class="mt-4 text-xs text-slate-400">Balances are computed as of the selected period end.</p>
