@extends('admin.layout')

@section('title', 'DAP Trust Introduction')
@section('heading', 'DAP Trust Introduction')
@section('subheading', 'About & Governance')

@section('content')
    <div class="overflow-hidden rounded-2xl bg-[#0b2447] text-white shadow-sm">
        <div class="relative px-6 py-10 sm:px-10">
            <div class="pointer-events-none absolute -top-16 -right-16 h-64 w-64 rounded-full bg-emerald-500/15 blur-3xl"></div>
            <p class="text-xs font-semibold uppercase tracking-widest text-emerald-300">Established {{ \App\Http\Controllers\TransparencyController::ESTABLISHED }}</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight">{{ \App\Http\Controllers\TransparencyController::TRUST_NAME }}</h2>
            <p class="mt-4 max-w-2xl text-sm leading-relaxed text-slate-300">
                DAP Educational Trust is a public charitable trust established on
                {{ \App\Http\Controllers\TransparencyController::ESTABLISHED }} with the objective of serving society through education,
                healthcare, community development and other charitable acts. We believe in complete transparency and
                accountability — this portal provides real-time information about our finances, donations, investments
                and compliance since FY{{ $since }}.
            </p>
            <div class="mt-8 grid grid-cols-2 gap-4 sm:grid-cols-3">
                @include('transparency.partials.stat-card', [
                    'label' => 'Donations Received',
                    'value' => $money($donationIncome),
                    'hint' => 'Since inception',
                    'color' => 'text-emerald-400',
                ])
                @include('transparency.partials.stat-card', [
                    'label' => 'Donations Count',
                    'value' => number_format($donationCount),
                    'hint' => 'Recorded receipts',
                    'color' => 'text-emerald-400',
                ])
                @include('transparency.partials.stat-card', [
                    'label' => 'Live Records Since',
                    'value' => $since,
                    'hint' => 'FY' . substr((string) $since, 2) . ' — ' . date('Y'),
                    'color' => 'text-emerald-400',
                ])
            </div>
        </div>
    </div>

    <div class="mt-6 grid gap-6 md:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-slate-900">Education</h3>
            <p class="mt-2 text-sm leading-relaxed text-slate-500">Scholarships and learning initiatives for students who need them most.</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-slate-900">Healthcare</h3>
            <p class="mt-2 text-sm leading-relaxed text-slate-500">Supporting medical care and health camps for underserved communities.</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-slate-900">Community Development</h3>
            <p class="mt-2 text-sm leading-relaxed text-slate-500">Empowering communities through sustained, transparent programmes.</p>
        </div>
    </div>

    <div class="mt-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-lg font-semibold text-slate-900">A note on transparency</h3>
        <p class="mt-2 text-sm leading-relaxed text-slate-500">
            Every rupee received and spent is recorded and published on this portal. Use the menu to explore our
            financial reports, donations, investments, compliance certificates and year-by-year trends — all updated
            live as entries are recorded.
        </p>
    </div>
@endsection
