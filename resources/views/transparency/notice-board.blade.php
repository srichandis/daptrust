@extends('admin.layout')

@section('title', 'DAP Trust Notice Board')
@section('heading', 'DAP Trust Notice Board')
@section('subheading', 'About & Governance · important notices & documents')

@section('content')
    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-slate-900">Notices</h2>
                <span class="text-xs font-medium text-emerald-600">● Live</span>
            </div>
            <div class="divide-y divide-slate-100">
                <div class="flex items-start gap-4 px-5 py-4">
                    <div class="w-12 shrink-0 text-center">
                        <p class="text-lg font-bold leading-none text-slate-900">{{ now()->format('d') }}</p>
                        <p class="mt-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-400">{{ now()->format('M Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-800">Financial Report FY{{ substr((string) $latestReport, 2) }} published</p>
                        <p class="mt-0.5 text-xs text-slate-500">The latest financial report is available in Important Documents.</p>
                    </div>
                </div>
                @if ($latestEntry)
                    <div class="flex items-start gap-4 px-5 py-4">
                        <div class="w-12 shrink-0 text-center">
                            <p class="text-lg font-bold leading-none text-slate-900">{{ $latestEntry->entry_date?->format('d') }}</p>
                            <p class="mt-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-400">{{ $latestEntry->entry_date?->format('M Y') }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-800">Latest entry recorded — {{ $latestEntry->label }}</p>
                            <p class="mt-0.5 text-xs text-slate-500">{{ $latestEntry->particulars ?: $latestEntry->donor_name ?: '—' }} · {{ $money($latestEntry->amount) }}</p>
                        </div>
                    </div>
                @endif
                <div class="flex items-start gap-4 px-5 py-4">
                    <div class="w-12 shrink-0 text-center">
                        <p class="text-lg font-bold leading-none text-slate-900">{{ now()->format('d') }}</p>
                        <p class="mt-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-400">{{ now()->format('M Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-800">This portal now shows live financial data</p>
                        <p class="mt-0.5 text-xs text-slate-500">Income, expenses, donations, investments and trends update automatically as entries are recorded.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-slate-900">Important Documents</h2>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach (['Financial Report FY' . substr((string) $latestReport, 2), 'Audit Report', 'Income & Expenditure Statement', 'Balance Sheet'] as $doc)
                    <div class="flex items-center justify-between px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-800">{{ $doc }}</p>
                                <p class="text-xs text-slate-400">Published {{ now()->format('d M Y') }}</p>
                            </div>
                        </div>
                        <span class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-500">View / Download</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
