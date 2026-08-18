@extends('admin.layout')

@section('title', 'DAP Trust Financial Report')
@section('heading', 'DAP Trust Financial Report')
@section('subheading', 'Live update · every entry recorded on this portal')

@section('content')
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @include('transparency.partials.stat-card', ['label' => 'Total Balance', 'value' => $money($totalBalance), 'hint' => 'Across all accounts'])
        @include('transparency.partials.stat-card', ['label' => 'Total Income', 'value' => $money($incomeTotal), 'hint' => 'All income-type entries', 'color' => 'text-emerald-600'])
        @include('transparency.partials.stat-card', ['label' => 'Total Expenses', 'value' => $money($expenseTotal), 'hint' => 'All expense-type entries', 'color' => 'text-red-600'])
        @include('transparency.partials.stat-card', ['label' => 'Net Position', 'value' => $money($incomeTotal - $expenseTotal), 'hint' => 'Income − expenses', 'color' => ($incomeTotal - $expenseTotal) >= 0 ? 'text-slate-900' : 'text-red-600'])
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-slate-900">Income by Type</h2>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach (\App\Models\Entry::GROUPS['Income'] as $type)
                    <div class="flex items-center justify-between px-5 py-3 text-sm">
                        <span class="text-slate-600">{{ \App\Models\Entry::labelFor($type) }}</span>
                        <span class="font-semibold text-emerald-600">{{ $money($incomeByType[$type] ?? 0) }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-slate-900">Expenses by Type</h2>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach (\App\Models\Entry::GROUPS['Expenses'] as $type)
                    <div class="flex items-center justify-between px-5 py-3 text-sm">
                        <span class="text-slate-600">{{ \App\Models\Entry::labelFor($type) }}</span>
                        <span class="font-semibold text-red-600">{{ $money($expenseByType[$type] ?? 0) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="mt-6 rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-slate-900">Latest Entries</h2>
            <span class="text-xs font-medium text-emerald-600">● Live</span>
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
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $entry->is_income ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $entry->label }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-slate-600">{{ $entry->account?->name ?? '—' }}</td>
                            <td class="max-w-xs truncate px-5 py-3 text-slate-500">{{ $entry->particulars ?: $entry->donor_name ?: '—' }}</td>
                            <td class="px-5 py-3 text-right font-semibold {{ $entry->is_income ? 'text-emerald-600' : 'text-red-600' }}">
                                {{ ($entry->is_income ? '+' : '−') . $money($entry->amount) }}
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
