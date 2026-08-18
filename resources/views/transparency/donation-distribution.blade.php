@extends('admin.layout')

@section('title', 'DAP Trust Donation Distribution Details')
@section('heading', 'DAP Trust Donation Distribution Details')
@section('subheading', "Donations · where the trust's donations go")

@section('content')
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        @include('transparency.partials.stat-card', ['label' => 'Total Distributed', 'value' => $money($donationExpense), 'hint' => 'All donation expenses', 'color' => 'text-red-600'])
        @include('transparency.partials.stat-card', ['label' => 'OPS Donations', 'value' => $money($opsTotal), 'hint' => 'To OPS Saiva Peravai centres'])
        @include('transparency.partials.stat-card', ['label' => 'Distribution Heads', 'value' => number_format($byParticulars->count()), 'hint' => 'Distinct purposes recorded'])
    </div>

    <div class="mt-6 rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-slate-900">Distribution by Particulars</h2>
            <span class="text-xs font-medium text-emerald-600">● Live</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-xs uppercase tracking-wide text-slate-400">
                        <th class="px-5 py-3 font-medium">Particulars</th>
                        <th class="px-5 py-3 text-right font-medium">Transactions</th>
                        <th class="px-5 py-3 text-right font-medium">Total Distributed</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($byParticulars as $row)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-medium text-slate-800">{{ $row->particulars }}</td>
                            <td class="px-5 py-3 text-right text-slate-600">{{ number_format($row->count) }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-red-600">{{ $money($row->total) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-5 py-6 text-center text-slate-500">No donation distribution recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
