@extends('admin.layout')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')
@section('subheading', 'Trust accounts at a glance')

@section('content')
    {{-- Summary cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">INCOME</p>
            <p class="mt-1 text-2xl font-bold text-emerald-600">₹{{ indian_number_format($incomeTotal, 2) }}</p>
            <p class="mt-1 text-xs text-slate-400">Donations + Interest + IT Return</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">EXPENSE</p>
            <p class="mt-1 text-2xl font-bold text-red-600">₹{{ indian_number_format($expenseTotal, 2) }}</p>
            <p class="mt-1 text-xs text-slate-400">Donations given + Charges</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">SAVINGS</p>
            <p class="mt-1 text-2xl font-bold {{ $savings >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                ₹{{ indian_number_format($savings, 2) }}
            </p>
            <p class="mt-1 text-xs text-slate-400">Income − Expenses</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">EXP + SAV</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">₹{{ indian_number_format($expPlusSav, 2) }}</p>
            <p class="mt-1 text-xs text-slate-400">Should equal INCOME</p>
        </div>
    </div>

    {{-- Account balances --}}
    <div class="mt-6 rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-slate-900">Account Balances</h2>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse ($accounts as $account)
                <div class="flex items-center justify-between px-5 py-3.5">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg text-sm font-bold
                            {{ $account->type === 'cash' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }}">
                            {{ $account->type === 'cash' ? '₹' : substr($account->name, 0, 1) }}
                        </div>
                        <div>
                            <p class="font-medium text-slate-900">{{ $account->name }}</p>
                            <p class="text-xs text-slate-500">
                                {{ ucfirst($account->type) }}
                                @if ($account->type === 'bank')
                                    · {{ $account->bank_name ?: '—' }} · {{ $account->account_number ?: '' }}
                                @endif
                                @if ($account->investment_type !== 'none')
                                    · <span class="text-indigo-600">{{ strtoupper($account->investment_type) }} investments</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <p class="font-semibold {{ $account->balance() < 0 ? 'text-red-600' : 'text-slate-900' }}">
                        ₹{{ indian_number_format($account->balance(), 2) }}
                    </p>
                </div>
            @empty
                <p class="px-5 py-6 text-sm text-slate-500">No accounts yet.</p>
            @endforelse
            @if ($accounts->isNotEmpty())
                <div class="flex items-center justify-between border-t-2 border-indigo-200 bg-indigo-50 px-5 py-3.5">
                    <p class="font-semibold text-indigo-700">BANK BALANCE</p>
                    <p class="text-lg font-bold text-indigo-700">
                        ₹{{ indian_number_format($totalBalance, 2) }}
                    </p>
                </div>
            @endif
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Income by type --}}
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-slate-900">Income by Type</h2>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach (\App\Models\Entry::GROUPS['Income'] as $type)
                    @continue (in_array($type, [\App\Models\Entry::TYPE_RD_INTEREST, \App\Models\Entry::TYPE_MF_INTEREST], true))
                    @php
                        $total = $incomeByType[$type] ?? 0;
                        $label = $type === \App\Models\Entry::TYPE_FD_INTEREST ? 'FD/RD/MF INTEREST' : \App\Models\Entry::labelFor($type);
                    @endphp
                    <div class="flex items-center justify-between px-5 py-3 text-sm">
                        <span class="text-slate-600">{{ $label }}</span>
                        <span class="font-semibold text-emerald-600">₹{{ indian_number_format($total, 2) }}</span>
                    </div>
                @endforeach

                {{-- Net FD/RD/MF still invested: Excel FD + MF + RD (D2555) --}}
                <div class="flex items-center justify-between px-5 py-3 text-sm">
                    <span class="text-slate-600">FD + MF + RD</span>
                    <span class="font-semibold text-emerald-600">₹{{ indian_number_format($trustSummary['FD + MF + RD'], 2) }}</span>
                </div>

                {{-- Donations routed to OPS: Excel DON TO OPS (BL2537) --}}
                <div class="flex items-center justify-between px-5 py-3 text-sm">
                    <span class="text-slate-600">DON TO OPS</span>
                    <span class="font-semibold text-emerald-600">₹{{ indian_number_format($trustSummary['DON TO OPS'], 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Expenses by type --}}
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-slate-900">Expenses by Type</h2>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach (\App\Models\Entry::GROUPS['Expenses'] as $type)
                    @php $total = $expenseByType[$type] ?? 0; @endphp
                    <div class="flex items-center justify-between px-5 py-3 text-sm">
                        <span class="text-slate-600">{{ \App\Models\Entry::labelFor($type) }}</span>
                        <span class="font-semibold text-red-600">₹{{ indian_number_format($total, 2) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Trust Summary --}}
    <div class="mt-6 rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-slate-900">Trust Summary</h2>
            <p class="mt-1 text-xs text-slate-500">All-time totals across all accounts and financial years</p>
        </div>
        <div class="divide-y divide-slate-100">
            @foreach ($trustSummary as $label => $value)
                @php
                    $isIncome = in_array($label, ['DONOR / RECEIPTS', 'BANK INTEREST', 'FD/RD/MF INTEREST', 'IT RETURN']);
                    $isExpense = in_array($label, ['DONATION', 'BANK CHARGES', 'PROFESSIONAL CHARGES', 'DON TO OPS']);
                @endphp
                <div class="flex items-center justify-between px-5 py-3 text-sm">
                    <span class="text-slate-600">{{ $label }}</span>
                    <span class="font-semibold
                        {{ $isIncome ? 'text-emerald-600' : ($isExpense ? 'text-red-600' : 'text-slate-700') }}">
                        ₹{{ indian_number_format($value, 2) }}
                    </span>
                </div>
            @endforeach
            <div class="flex items-center justify-between px-5 py-3.5 text-sm font-semibold">
                <span class="text-slate-800">Net Income − Expenses</span>
                <span class="{{ $summaryTotals['income'] - $summaryTotals['expense'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                    ₹{{ indian_number_format($summaryTotals['income'] - $summaryTotals['expense'], 2) }}
                </span>
            </div>
        </div>
    </div>

    {{-- Latest entries --}}
    <div class="mt-6 rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-slate-900">Latest Entries</h2>
            <a href="{{ route('admin.entries.index') }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-700">View all →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-xs uppercase tracking-wide text-slate-400">
                        <th class="px-5 py-3 font-medium">Date</th>
                        <th class="px-5 py-3 font-medium">Type</th>
                        <th class="px-5 py-3 font-medium">Account</th>
                        <th class="px-5 py-3 font-medium">Particulars</th>
                        <th class="px-5 py-3 text-right font-medium">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($latestEntries as $entry)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 text-slate-500">{{ $entry->entry_date?->format('d M Y') }}</td>
                            <td class="px-5 py-3">
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium
                                    {{ $entry->is_income ? 'bg-emerald-100 text-emerald-700' : ($entry->is_expense ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-600') }}">
                                    {{ $entry->label }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-slate-600">
                                {{ $entry->account?->name ?? '—' }}
                                @if ($entry->toAccount) → {{ $entry->toAccount->name }} @endif
                            </td>
                            <td class="max-w-xs truncate px-5 py-3 text-slate-500">{{ $entry->particulars ?: $entry->donor_name ?: '—' }}</td>
                            <td class="px-5 py-3 text-right font-semibold {{ $entry->is_income ? 'text-emerald-600' : ($entry->is_expense ? 'text-red-600' : 'text-slate-700') }}">
                                ₹{{ indian_number_format($entry->amount, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-6 text-center text-slate-500">No entries yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
