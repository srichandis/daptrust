@extends('admin.layout')

@section('title', 'DAP Trust Endowment Details')
@section('heading', 'DAP Trust Endowment Details')
@section('subheading', 'Financial Transparency · corpus & investments')

@section('content')
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @include('transparency.partials.stat-card', ['label' => 'Corpus Balance', 'value' => $money($totalBalance), 'hint' => 'Across all accounts'])
        @include('transparency.partials.stat-card', ['label' => 'Fixed Deposits', 'value' => $money($byKind[\App\Models\Entry::TYPE_FD_INVESTMENT] ?? 0), 'hint' => 'Total FD invested', 'color' => 'text-emerald-600'])
        @include('transparency.partials.stat-card', ['label' => 'Recurring Deposits', 'value' => $money($byKind[\App\Models\Entry::TYPE_RD_INVESTMENT] ?? 0), 'hint' => 'Total RD invested', 'color' => 'text-emerald-600'])
        @include('transparency.partials.stat-card', ['label' => 'Mutual Funds', 'value' => $money($byKind[\App\Models\Entry::TYPE_MF_INVESTMENT] ?? 0), 'hint' => 'Total MF invested', 'color' => 'text-emerald-600'])
    </div>

    <div class="mt-6 rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-slate-900">Investment Register</h2>
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
                    @forelse ($investments as $entry)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 text-slate-500">{{ $entry->entry_date?->format('d M Y') }}</td>
                            <td class="px-5 py-3">
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium bg-indigo-100 text-indigo-700">{{ strtoupper(\App\Models\Entry::INVEST_TYPES[$entry->entry_type] ?? $entry->label) }}</span>
                            </td>
                            <td class="px-5 py-3 text-slate-600">{{ $entry->account?->name ?? '—' }}</td>
                            <td class="max-w-xs truncate px-5 py-3 text-slate-500">{{ $entry->particulars ?: '—' }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-slate-800">{{ $money($entry->amount) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-6 text-center text-slate-500">No investments recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
