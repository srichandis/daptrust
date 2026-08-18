@extends('admin.layout')

@section('title', 'DAP Trust Donors Details')
@section('heading', 'DAP Trust Donors Details')
@section('subheading', 'Financial Transparency · every donor, recorded & published')

@section('content')
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        @include('transparency.partials.stat-card', ['label' => 'Total Donations', 'value' => $money($donationIncome), 'hint' => 'All donation income', 'color' => 'text-emerald-600'])
        @include('transparency.partials.stat-card', ['label' => 'Donations Count', 'value' => number_format($donationCount), 'hint' => 'Recorded receipts'])
        @include('transparency.partials.stat-card', ['label' => 'Named Donors', 'value' => number_format($topDonors->count()), 'hint' => 'Top 20 shown below'])
    </div>

    <div class="mt-6 rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-slate-900">Top Donors</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-xs uppercase tracking-wide text-slate-400">
                        <th class="px-5 py-3 font-medium">#</th>
                        <th class="px-5 py-3 font-medium">Donor</th>
                        <th class="px-5 py-3 text-right font-medium">Donations</th>
                        <th class="px-5 py-3 text-right font-medium">Total Given</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($topDonors as $i => $donor)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 text-slate-400">{{ $i + 1 }}</td>
                            <td class="px-5 py-3 font-medium text-slate-800">{{ $donor->donor_name }}</td>
                            <td class="px-5 py-3 text-right text-slate-600">{{ number_format($donor->donations) }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-emerald-600">{{ $money($donor->total) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-6 text-center text-slate-500">No donor records yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6 rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-slate-900">Recent Donations</h2>
            <span class="text-xs font-medium text-emerald-600">● Live</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-xs uppercase tracking-wide text-slate-400">
                        <th class="px-5 py-3 font-medium">Date</th>
                        <th class="px-5 py-3 font-medium">Donor</th>
                        <th class="px-5 py-3 font-medium">Particulars</th>
                        <th class="px-5 py-3 text-right font-medium">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($recent as $entry)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 text-slate-500">{{ $entry->entry_date?->format('d M Y') }}</td>
                            <td class="px-5 py-3 font-medium text-slate-800">{{ $entry->donor_name ?: '—' }}</td>
                            <td class="max-w-xs truncate px-5 py-3 text-slate-500">{{ $entry->particulars ?: '—' }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-emerald-600">{{ $money($entry->amount) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-6 text-center text-slate-500">No donations yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
