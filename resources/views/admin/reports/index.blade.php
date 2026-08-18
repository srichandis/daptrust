@extends('admin.layout')

@section('title', 'Reports')
@section('heading', 'Reports')
@section('subheading', 'All trust reports for a selected period')

@php
    $today = now();
    $fyStart = $today->month >= 4 ? $today->copy()->startOfYear()->addMonths(3) : $today->copy()->subYear()->startOfYear()->addMonths(3);
    $fyEnd = $fyStart->copy()->addYear()->subDay();
    $lastFyStart = $fyStart->copy()->subYear();
    $lastFyEnd = $fyEnd->copy()->subYear();
@endphp

@section('content')
    <style>
        @media print {
            aside, header, .no-print { display: none !important; }
            main { padding: 0 !important; }
            .print-card { border: 0 !important; box-shadow: none !important; }
        }
    </style>

    {{-- Period + report selector --}}
    <div class="no-print mb-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="grid gap-4 md:grid-cols-12">
            <div class="md:col-span-3">
                <label class="mb-1 block text-xs font-medium text-slate-500">Report</label>
                <select name="report" onchange="this.form.submit()"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                    @foreach ($reports as $key => $label)
                        <option value="{{ $key }}" @selected($report === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="mb-1 block text-xs font-medium text-slate-500">From</label>
                <input type="date" name="from" value="{{ $from }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
            </div>

            <div class="md:col-span-2">
                <label class="mb-1 block text-xs font-medium text-slate-500">To</label>
                <input type="date" name="to" value="{{ $to }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
            </div>

            <div class="flex items-end gap-2 md:col-span-5">
                <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-900">Apply period</button>
                <a href="{{ route('admin.reports.index', ['report' => $report]) }}"
                   class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">All time</a>
                <span class="mx-1 hidden h-8 w-px bg-slate-200 sm:block"></span>
                <a href="{{ route('admin.reports.index', ['report' => $report, 'from' => $fyStart->toDateString(), 'to' => $fyEnd->toDateString()]) }}"
                   class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-700 hover:bg-emerald-100">This FY</a>
                <a href="{{ route('admin.reports.index', ['report' => $report, 'from' => $lastFyStart->toDateString(), 'to' => $lastFyEnd->toDateString()]) }}"
                   class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-700 hover:bg-emerald-100">Last FY</a>
                <button type="button" onclick="window.print()"
                        class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Print</button>
            </div>
        </form>

        <p class="mt-3 text-xs text-slate-400">
            Period: {{ $from ? \Carbon\Carbon::parse($from)->format('d M Y') : 'Beginning' }} —
            {{ $to ? \Carbon\Carbon::parse($to)->format('d M Y') : 'Latest entry' }}
        </p>
    </div>

    {{-- Selected report --}}
    <div class="print-card rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-5 flex items-center justify-between border-b border-slate-100 pb-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">{{ $reports[$report] }}</h2>
                <p class="text-xs text-slate-500">
                    {{ $from ? \Carbon\Carbon::parse($from)->format('d M Y') : 'Beginning' }} to
                    {{ $to ? \Carbon\Carbon::parse($to)->format('d M Y') : 'Latest entry' }}
                </p>
            </div>
        </div>

        @switch($report)
            @case('master')
                @include('admin.reports.partials.master')
                @break
            @case('snapshot')
                @include('admin.reports.partials.snapshot')
                @break
            @case('it-returns')
                @include('admin.reports.partials.it-returns')
                @break
            @case('fd-details')
                @include('admin.reports.partials.investments')
                @break
            @case('rd-details')
                @include('admin.reports.partials.investments')
                @break
            @case('mf-details')
                @include('admin.reports.partials.investments')
                @break
            @case('donors')
                @include('admin.reports.partials.donors')
                @break
            @case('income')
                @include('admin.reports.partials.specific')
                @break
            @case('expense')
                @include('admin.reports.partials.specific')
                @break
            @case('statement')
                @include('admin.reports.partials.statement')
                @break
            @case('accounts')
                @include('admin.reports.partials.accounts')
                @break
            @case('year-end')
                @include('admin.reports.partials.year-end')
                @break
            @case('fd-maturity')
                @include('admin.reports.partials.maturity')
                @break
            @case('rd-maturity')
                @include('admin.reports.partials.maturity')
                @break
            @case('snapshot-income')
                @include('admin.reports.partials.breakdown')
                @break
            @case('snapshot-expense')
                @include('admin.reports.partials.breakdown')
                @break
            @case('donation-expense')
                @include('admin.reports.partials.donation-expense')
                @break
            @case('distribution')
                @include('admin.reports.partials.distribution')
                @break
        @endswitch
    </div>
@endsection
