@extends('admin.layout')

@section('title', 'DAP Trust Donation Amount Particulars')
@section('heading', 'DAP Trust Donation Amount Particulars')
@section('subheading', 'Donations · donation income by year & mode of receipt')

@section('content')
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        @include('transparency.partials.stat-card', ['label' => 'Donations Received', 'value' => $money($donationIncome), 'hint' => 'All donation income', 'color' => 'text-emerald-600'])
        @include('transparency.partials.stat-card', ['label' => 'Receipts Issued', 'value' => number_format($donationCount), 'hint' => 'Recorded donations'])
        @include('transparency.partials.stat-card', ['label' => 'Financial Years', 'value' => number_format($byYear->count()), 'hint' => 'Of donation records'])
    </div>

    <div class="mt-6 rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-slate-900">Donations by Financial Year</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-xs uppercase tracking-wide text-slate-400">
                        <th class="px-5 py-3 font-medium">Financial Year</th>
                        <th class="px-5 py-3 text-right font-medium">Donations</th>
                        <th class="px-5 py-3 text-right font-medium">Amount Received</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($byYear as $row)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-medium text-slate-800">{{ $row->financial_year }}</td>
                            <td class="px-5 py-3 text-right text-slate-600">{{ number_format($row->count) }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-emerald-600">{{ $money($row->total) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-5 py-6 text-center text-slate-500">No donation particulars recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($byMode->isNotEmpty())
        <div class="mt-6 rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-slate-900">Donations by Mode of Receipt</h2>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach ($byMode as $row)
                    <div class="flex items-center justify-between px-5 py-3 text-sm">
                        <span class="text-slate-600">{{ $row->mode_of_receipt }}</span>
                        <span class="font-semibold text-emerald-600">{{ $money($row->total) }} <span class="text-xs font-normal text-slate-400">({{ number_format($row->count) }})</span></span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endsection
