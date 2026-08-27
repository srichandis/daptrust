@extends('admin.layout')

@section('title', $title)
@section('heading', $title)
@section('subheading', $subtitle)

@section('content')
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        @include('transparency.partials.stat-card', ['label' => 'Total (' . $series[0]['fy'] . ' — ' . end($series)['fy'] . ')', 'value' => $money($total), 'hint' => 'Sum across all financial years', 'color' => 'text-emerald-600'])
        @include('transparency.partials.stat-card', ['label' => 'Best Year', 'value' => $money($max), 'hint' => 'Highest single financial year'])
    </div>

    <div class="mt-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-lg font-semibold text-slate-900">Year-by-Year ({{ $series[0]['fy'] }} — {{ end($series)['fy'] }})</h2>
            <span class="text-xs font-medium text-emerald-600">● Live update</span>
        </div>
        @include('transparency.partials.trend-chart')
        <p class="mt-4 text-xs text-slate-400">
            Hover over a bar to see the exact amount for that financial year. Figures update automatically as entries are recorded.
        </p>
    </div>

    <div class="mt-6 rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-slate-900">Figures by Financial Year</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-xs uppercase tracking-wide text-slate-400">
                        <th class="px-5 py-3 font-medium">Financial Year</th>
                        <th class="px-5 py-3 text-right font-medium">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach (array_reverse($series) as $row)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-medium text-slate-800">{{ $row['fy'] }}</td>
                            <td class="px-5 py-3 text-right font-semibold {{ $row['value'] > 0 ? 'text-emerald-600' : 'text-slate-400' }}">{{ $money($row['value']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
