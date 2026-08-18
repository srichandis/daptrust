@extends('admin.layout')

@section('title', 'DAP Trust Audit Report')
@section('heading', 'DAP Trust Audit Report')
@section('subheading', 'Investments & compliance · audited financials by year')

@section('content')
    <p class="mb-5 max-w-2xl text-sm leading-relaxed text-slate-600">
        Audited financial statements of the trust, year by year, available for public download.
    </p>

    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-slate-900">Audit Reports by Financial Year</h2>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse ($years as $year)
                <div class="flex items-center justify-between px-5 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-800">FY{{ $year }} — FY{{ $year + 1 }} Audit Report</p>
                            <p class="text-xs text-slate-400">Audited accounts for the financial year</p>
                        </div>
                    </div>
                    <span class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-500">View / Download</span>
                </div>
            @empty
                <p class="px-5 py-6 text-sm text-slate-500">No audit reports available yet.</p>
            @endforelse
        </div>
    </div>

    <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-800">
        <p class="font-semibold">Note</p>
        <p class="mt-1">Audit report files are placeholders — upload the actual PDFs when available.</p>
    </div>
@endsection
