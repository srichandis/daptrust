{{-- Trust summary, mirroring the Excel MASTER summary block --}}
<div class="mb-8">
    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-700">Trust Summary</h3>
    <table class="w-full text-sm">
        <tbody>
        @foreach ($trustSummary as $label => $value)
            @php
                $isIncome = in_array($label, ['DONOR / RECEIPTS', 'BANK INTEREST', 'FD/RD/MF INTEREST', 'IT RETURN']);
                $isExpense = in_array($label, ['DONATION', 'BANK CHARGES', 'PROFESSIONAL CHARGES', 'DON TO OPS']);
            @endphp
            <tr class="border-t border-slate-100">
                <td class="py-2 pr-4 text-slate-700">{{ $label }}</td>
                <td class="py-2 text-right font-medium
                    {{ $isIncome ? 'text-emerald-600' : ($isExpense ? 'text-rose-600' : 'text-slate-900') }}">
                    ₹{{ indian_number_format($value, 2) }}
                </td>
            </tr>
        @endforeach
        <tr class="border-t-2 border-slate-200 font-semibold text-slate-900">
            <td class="py-2 pr-4">INCOME</td>
            <td class="py-2 text-right">₹{{ indian_number_format($summaryTotals['income'], 2) }}</td>
        </tr>
        <tr class="border-t border-slate-100 font-semibold text-slate-900">
            <td class="py-2 pr-4">EXPENSE</td>
            <td class="py-2 text-right">₹{{ indian_number_format($summaryTotals['expense'], 2) }}</td>
        </tr>
        <tr class="border-t border-slate-100 font-semibold text-slate-900">
            <td class="py-2 pr-4">SAVINGS</td>
            <td class="py-2 text-right">₹{{ indian_number_format($summaryTotals['savings'], 2) }}</td>
        </tr>
        <tr class="border-t border-slate-100 font-semibold text-slate-900">
            <td class="py-2 pr-4">EXP + SAV</td>
            <td class="py-2 text-right">₹{{ indian_number_format($summaryTotals['exp_plus_sav'], 2) }}</td>
        </tr>
        </tbody>
    </table>
</div>

<div class="grid gap-8 lg:grid-cols-2">
    <div>
        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-emerald-700">Income</h3>
        <table class="w-full text-sm">
            <tbody>
            @foreach ($masterIncome as $label => $value)
                <tr class="border-t border-slate-100">
                    <td class="py-2 pr-4 text-slate-700">{{ $label }}</td>
                    <td class="py-2 text-right font-medium text-emerald-600">₹{{ indian_number_format($value, 2) }}</td>
                </tr>
            @endforeach
            <tr class="border-t-2 border-slate-200 font-semibold text-slate-900">
                <td class="py-2 pr-4">Total Income</td>
                <td class="py-2 text-right">₹{{ indian_number_format(collect($masterIncome)->sum(), 2) }}</td>
            </tr>
            </tbody>
        </table>
    </div>

    <div>
        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-rose-700">Expenses</h3>
        <table class="w-full text-sm">
            <tbody>
            @foreach ($masterExpense as $label => $value)
                <tr class="border-t border-slate-100">
                    <td class="py-2 pr-4 text-slate-700">{{ $label }}</td>
                    <td class="py-2 text-right font-medium text-rose-600">₹{{ indian_number_format($value, 2) }}</td>
                </tr>
            @endforeach
            <tr class="border-t-2 border-slate-200 font-semibold text-slate-900">
                <td class="py-2 pr-4">Total Expenses</td>
                <td class="py-2 text-right">₹{{ indian_number_format(collect($masterExpense)->sum(), 2) }}</td>
            </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="mt-8">
    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-indigo-700">Bank Balance</h3>
    <p class="mb-3 text-xs text-slate-500">Closing balance of each account as of the selected period end.</p>
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        @foreach ($bankBalances as $bank)
            <div class="rounded-xl border border-slate-200 p-4">
                <p class="text-xs uppercase tracking-wide text-slate-500">{{ $bank['name'] }}</p>
                <p class="mt-1 text-xl font-bold text-slate-900">₹{{ indian_number_format($bank['balance'], 2) }}</p>
                <p class="text-xs text-slate-400">{{ ucfirst($bank['type']) }}</p>
            </div>
        @endforeach
        <div class="rounded-xl border-2 border-indigo-300 bg-indigo-50 p-4">
            <p class="text-xs uppercase tracking-wide text-indigo-600 font-semibold">BANK BALANCE</p>
            <p class="mt-1 text-xl font-bold text-indigo-700">₹{{ indian_number_format(collect($bankBalances)->sum('balance'), 2) }}</p>
            <p class="text-xs text-indigo-400">Total</p>
        </div>
    </div>
</div>
