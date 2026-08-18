<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('admin.trust_name') }} — Transparency · Service · Accountability</title>
    <meta name="description" content="{{ config('admin.trust_name') }} is committed to transparency in every transaction and accountability in every action. Explore our financials, governance and reports through real-time updates.">

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white font-sans text-slate-700 antialiased">

{{-- ============================================================ Navbar --}}
<header x-data="{ open: false }" class="sticky top-0 z-50 border-b border-slate-200/70 bg-white/95 backdrop-blur">
    <nav class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6">
        <a href="#top" class="flex items-center gap-2.5">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-600 text-lg font-bold text-white shadow-lg shadow-emerald-600/25">D</div>
            <div class="leading-tight">
                <p class="text-sm font-bold tracking-wide text-slate-900">DAP TRUST</p>
                <p class="text-[10px] uppercase tracking-widest text-slate-400">Transparency · Service · Accountability</p>
            </div>
        </a>

        <div class="hidden items-center gap-7 text-sm font-medium text-slate-600 md:flex">
            <a href="#about" class="transition hover:text-emerald-600">About</a>
            <a href="{{ route('admin.transparency.endowment') }}" class="transition hover:text-emerald-600">Endowment</a>
            <a href="{{ route('admin.transparency.donors') }}" class="transition hover:text-emerald-600">Donors</a>
            <a href="{{ route('admin.transparency.notice-board') }}" class="transition hover:text-emerald-600">Notice Board</a>
            <a href="{{ route('admin.transparency.financial-report') }}" class="transition hover:text-emerald-600">Transparency</a>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.login') }}" class="hidden text-sm font-medium text-slate-500 transition hover:text-slate-800 sm:block">Admin</a>
            <a href="{{ route('admin.transparency.donors') }}" class="hidden rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-emerald-600/25 transition hover:bg-emerald-700 active:scale-[0.98] sm:inline-block">
                Donate
            </a>
            <button @click="open = !open" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-600 hover:bg-slate-100 md:hidden" aria-label="Menu">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>
    </nav>

    <div x-show="open" @click.outside="open = false" x-transition class="border-t border-slate-100 bg-white px-4 py-3 md:hidden">
        <div class="flex flex-col gap-1 text-sm font-medium text-slate-700">
            <a href="#about" @click="open = false" class="rounded-lg px-3 py-2 hover:bg-slate-50">About</a>
            <a href="{{ route('admin.transparency.endowment') }}" @click="open = false" class="rounded-lg px-3 py-2 hover:bg-slate-50">Endowment</a>
            <a href="{{ route('admin.transparency.donors') }}" @click="open = false" class="rounded-lg px-3 py-2 hover:bg-slate-50">Donors</a>
            <a href="{{ route('admin.transparency.notice-board') }}" @click="open = false" class="rounded-lg px-3 py-2 hover:bg-slate-50">Notice Board</a>
            <a href="{{ route('admin.transparency.financial-report') }}" @click="open = false" class="rounded-lg px-3 py-2 hover:bg-slate-50">Transparency</a>
            <div class="my-1 border-t border-slate-100"></div>
            <a href="{{ route('admin.login') }}" class="rounded-lg px-3 py-2 text-slate-500 hover:bg-slate-50">Admin sign in</a>
            <a href="{{ route('admin.transparency.donors') }}" @click="open = false" class="mt-1 rounded-lg bg-emerald-600 px-3 py-2 text-center font-semibold text-white hover:bg-emerald-700">Donate</a>
        </div>
    </div>
</header>

{{-- ============================================================ Hero --}}
<section id="top" class="relative overflow-hidden bg-[#0b2447] text-white">
    <div class="pointer-events-none absolute -top-24 -right-24 h-96 w-96 rounded-full bg-emerald-500/15 blur-3xl"></div>
    <div class="pointer-events-none absolute bottom-0 left-0 h-72 w-72 rounded-full bg-sky-500/10 blur-3xl"></div>

    <div class="relative mx-auto grid max-w-6xl items-center gap-12 px-4 py-16 sm:px-6 lg:grid-cols-2 lg:py-24">
        <div>
            <p class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3 py-1 text-xs font-semibold uppercase tracking-widest text-emerald-300">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                {{ config('admin.trust_name') }}
            </p>

            <h1 class="mt-5 text-4xl font-bold leading-tight tracking-tight sm:text-5xl">
                Building a Better<br>Tomorrow, <span class="text-emerald-400">Together</span>
            </h1>

            <p class="mt-5 max-w-xl text-base leading-relaxed text-slate-300 sm:text-lg">
                DAP Trust is committed to transparency in every transaction and accountability in every action.
                Explore our financials, governance and reports through real-time updates.
            </p>

            <div class="mt-8 flex flex-wrap items-center gap-4">
                <a href="{{ route('admin.transparency.index') }}" class="rounded-lg bg-emerald-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-600/30 transition hover:bg-emerald-500 active:scale-[0.98]">
                    Explore DAP Trust
                </a>
                <a href="#about" class="text-sm font-semibold text-slate-300 transition hover:text-white">
                    Learn more <span aria-hidden="true">→</span>
                </a>
            </div>

            {{-- Live stats --}}
            <dl class="mt-10 grid grid-cols-2 gap-x-6 gap-y-6 border-t border-white/10 pt-8 sm:grid-cols-4">
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wider text-slate-400">Net Position</dt>
                    <dd class="mt-1 text-xl font-bold sm:text-2xl">{{ $money($incomeTotal - $expenseTotal) }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wider text-slate-400">Total Income</dt>
                    <dd class="mt-1 text-xl font-bold text-emerald-400 sm:text-2xl">{{ $money($incomeTotal) }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wider text-slate-400">Total Expenses</dt>
                    <dd class="mt-1 text-xl font-bold text-rose-400 sm:text-2xl">{{ $money($expenseTotal) }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wider text-slate-400">Donations</dt>
                    <dd class="mt-1 text-xl font-bold sm:text-2xl">{{ $money($donationIncome) }}</dd>
                    <dd class="text-xs text-slate-400">{{ number_format($donationCount) }} donations received</dd>
                </div>
            </dl>
        </div>

        {{-- Product mockup --}}
        <div class="relative mx-auto w-64 sm:w-72 lg:w-80">
            <div class="overflow-hidden rounded-[2.2rem] border-4 border-slate-700 bg-slate-900 shadow-2xl shadow-black/40">
                <img src="{{ asset('dashboard.png') }}" alt="{{ config('admin.trust_name') }} — Financial & Transparency Dashboard" class="h-auto w-full">
            </div>

            @if ($latestEntry)
                <div class="absolute -left-16 bottom-10 hidden w-52 rounded-xl border border-white/10 bg-white/95 p-3.5 text-slate-800 shadow-xl backdrop-blur sm:block">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-emerald-600">Latest Update</p>
                    <p class="mt-1 line-clamp-2 text-xs font-medium text-slate-900">{{ $latestEntry->particulars ?: $latestEntry->donor_name ?: $latestEntry->label }}</p>
                    <p class="mt-1 text-[11px] text-slate-500">{{ $latestEntry->entry_date?->format('d M Y') }} · {{ $money($latestEntry->amount) }}</p>
                </div>
            @endif

            <div class="absolute -right-10 top-8 hidden rounded-xl border border-white/10 bg-white/95 px-3.5 py-2.5 text-slate-800 shadow-xl backdrop-blur sm:block">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-emerald-600">FY 2025-26</p>
                <p class="text-sm font-bold">Financial Overview</p>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================ Cards --}}
<section id="notice-board" class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:py-20">
    <div class="grid gap-6 md:grid-cols-3">
        {{-- Endowment --}}
        <div id="endowment" class="group rounded-2xl border border-slate-200 bg-white p-7 shadow-sm transition hover:-translate-y-1 hover:border-emerald-200 hover:shadow-lg">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 transition group-hover:bg-emerald-600 group-hover:text-white">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h3 class="mt-5 text-lg font-semibold text-slate-900">Endowment</h3>
            <p class="mt-2 text-sm leading-relaxed text-slate-500">
                Our corpus is invested prudently across FDs, RDs and mutual funds so the trust's charitable work can continue for generations.
            </p>
            <a href="{{ route('admin.transparency.endowment') }}" class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-emerald-600 transition hover:text-emerald-700">Learn more <span aria-hidden="true">→</span></a>
        </div>

        {{-- Donors --}}
        <div id="donors" class="group rounded-2xl border border-slate-200 bg-white p-7 shadow-sm transition hover:-translate-y-1 hover:border-emerald-200 hover:shadow-lg">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 transition group-hover:bg-emerald-600 group-hover:text-white">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
            </div>
            <h3 class="mt-5 text-lg font-semibold text-slate-900">Donors</h3>
            <p class="mt-2 text-sm leading-relaxed text-slate-500">
                Every donation is recorded and published — <span class="font-semibold text-slate-700">{{ $money($donationIncome) }}</span> received from {{ number_format($donationCount) }} donations so far.
            </p>
            <a href="{{ route('admin.transparency.donors') }}" class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-emerald-600 transition hover:text-emerald-700">See updates <span aria-hidden="true">→</span></a>
        </div>

        {{-- Notice Board --}}
        <div class="group rounded-2xl border border-slate-200 bg-white p-7 shadow-sm transition hover:-translate-y-1 hover:border-emerald-200 hover:shadow-lg">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 transition group-hover:bg-emerald-600 group-hover:text-white">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6M9 8h6M7.5 3h9A2.5 2.5 0 0119 5.5v13a2.5 2.5 0 01-2.5 2.5h-9A2.5 2.5 0 015 18.5v-13A2.5 2.5 0 017.5 3z"/></svg>
            </div>
            <h3 class="mt-5 text-lg font-semibold text-slate-900">Notice Board</h3>
            <p class="mt-2 text-sm leading-relaxed text-slate-500">
                Important notices, documents and financial reports — published here in real time for everyone to see.
            </p>
            <a href="{{ route('admin.transparency.notice-board') }}" class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-emerald-600 transition hover:text-emerald-700">Explore <span aria-hidden="true">→</span></a>
        </div>
    </div>
</section>

{{-- ============================================================ Updates & About --}}
<section id="updates" class="border-y border-slate-100 bg-slate-50/70">
    <div class="mx-auto grid max-w-6xl gap-12 px-4 py-16 sm:px-6 lg:grid-cols-2 lg:py-20">
        {{-- Latest updates --}}
        <div>
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900">Latest Updates</h2>
                <a href="{{ route('admin.transparency.financial-report') }}" class="text-sm font-semibold text-emerald-600 transition hover:text-emerald-700">View all <span aria-hidden="true">→</span></a>
            </div>

            <div class="mt-6 space-y-3">
                @forelse ($latestEntries as $entry)
                    <div class="flex items-center gap-4 rounded-xl border border-slate-200 bg-white px-4 py-3.5 shadow-sm">
                        <div class="w-12 shrink-0 text-center">
                            <p class="text-lg font-bold leading-none text-slate-900">{{ $entry->entry_date?->format('d') }}</p>
                            <p class="mt-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-400">{{ $entry->entry_date?->format('M Y') }}</p>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-slate-800">{{ $entry->particulars ?: $entry->donor_name ?: $entry->label }}</p>
                            <p class="mt-0.5 text-xs text-slate-500">{{ $entry->label }}@if ($entry->account) · {{ $entry->account->name }}@endif</p>
                        </div>
                        <p class="shrink-0 text-sm font-semibold {{ $entry->is_income ? 'text-emerald-600' : 'text-red-600' }}">
                            {{ ($entry->is_income ? '+' : '−') . $money($entry->amount) }}
                        </p>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-300 bg-white px-5 py-8 text-center text-sm text-slate-500">
                        Updates will appear here as entries are recorded.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- About --}}
        <div id="about">
            <h2 class="text-2xl font-bold tracking-tight text-slate-900">About DAP Trust</h2>
            <p class="mt-5 text-base leading-relaxed text-slate-600">
                DAP Trust is a public charitable trust established with the objective of serving society through
                education, healthcare, community development and other charitable acts. We believe in complete
                transparency and accountability — this portal provides real-time information about our finances,
                donations, investments and compliance.
            </p>

            <ul class="mt-6 space-y-4">
                <li class="flex items-start gap-3">
                    <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </span>
                    <p class="text-sm text-slate-600"><span class="font-semibold text-slate-800">Education</span> — scholarships and learning initiatives for those who need them most.</p>
                </li>
                <li class="flex items-start gap-3">
                    <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </span>
                    <p class="text-sm text-slate-600"><span class="font-semibold text-slate-800">Healthcare</span> — supporting medical care and health camps for underserved communities.</p>
                </li>
                <li class="flex items-start gap-3">
                    <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </span>
                    <p class="text-sm text-slate-600"><span class="font-semibold text-slate-800">Community Development</span> — empowering communities through sustained, transparent programmes.</p>
                </li>
            </ul>

            <div class="mt-8 rounded-xl border border-emerald-100 bg-emerald-50 p-5">
                <p class="text-sm font-semibold text-emerald-800">Transparency + Service + Accountability</p>
                <p class="mt-1 text-sm text-emerald-700/80">Every rupee accounted for, every action published.</p>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================ Footer --}}
<footer id="transparency" class="bg-[#002a55] text-slate-300">
    <div class="mx-auto max-w-6xl px-4 py-14 sm:px-6">
        <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-4">
            {{-- Contact --}}
            <div>
                <div class="flex items-center gap-2.5">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-500 text-lg font-bold text-white">D</div>
                    <div class="leading-tight">
                        <p class="text-sm font-bold tracking-wide text-white">DAP TRUST</p>
                        <p class="text-[10px] uppercase tracking-widest text-slate-400">Transparency · Service · Accountability</p>
                    </div>
                </div>
                <p class="mt-4 text-sm leading-relaxed text-slate-400">
                    Building a better tomorrow, together — with complete transparency in every transaction.
                </p>
                <ul class="mt-5 space-y-2.5 text-sm">
                    <li class="flex items-center gap-2.5">
                        <svg class="h-4 w-4 shrink-0 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                        info@daptrust.org
                    </li>
                    <li class="flex items-center gap-2.5">
                        <svg class="h-4 w-4 shrink-0 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/></svg>
                        www.daptrust.org
                    </li>
                </ul>
            </div>

            {{-- Useful links --}}
            <div>
                <h4 class="text-sm font-semibold uppercase tracking-wider text-white">Useful Links</h4>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li><a href="{{ route('admin.transparency.introduction') }}" class="transition hover:text-emerald-300">About Us</a></li>
                    <li><a href="{{ route('admin.transparency.endowment') }}" class="transition hover:text-emerald-300">Endowment</a></li>
                    <li><a href="{{ route('admin.transparency.donors') }}" class="transition hover:text-emerald-300">Donors</a></li>
                    <li><a href="{{ route('admin.transparency.notice-board') }}" class="transition hover:text-emerald-300">Notice Board</a></li>
                    <li><a href="{{ route('admin.transparency.financial-report') }}" class="transition hover:text-emerald-300">Financial Report</a></li>
                    <li><a href="{{ route('admin.login') }}" class="transition hover:text-emerald-300">Trustee Sign In</a></li>
                </ul>
            </div>

            {{-- Transparency --}}
            <div>
                <h4 class="text-sm font-semibold uppercase tracking-wider text-white">Transparency</h4>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li class="flex items-center justify-between gap-2">
                        <span class="text-slate-400">Trust Registration</span>
                        <span class="font-medium text-white">127998</span>
                    </li>
                    <li class="flex items-center justify-between gap-2">
                        <span class="text-slate-400">80G Certificate</span>
                        <span class="inline-flex items-center gap-1.5 font-medium text-emerald-300"><span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>Available</span>
                    </li>
                    <li class="flex items-center justify-between gap-2">
                        <span class="text-slate-400">12A Certificate</span>
                        <span class="inline-flex items-center gap-1.5 font-medium text-emerald-300"><span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>Available</span>
                    </li>
                    <li class="flex items-center justify-between gap-2">
                        <span class="text-slate-400">NIT Aayog ID</span>
                        <span class="inline-flex items-center gap-1.5 font-medium text-emerald-300"><span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>Available</span>
                    </li>
                </ul>
            </div>

            {{-- Follow us --}}
            <div>
                <h4 class="text-sm font-semibold uppercase tracking-wider text-white">Follow Us</h4>
                <div class="mt-4 flex gap-3">
                    <a href="#" aria-label="Facebook" class="flex h-9 w-9 items-center justify-center rounded-lg border border-white/10 text-slate-300 transition hover:border-emerald-400 hover:text-emerald-300">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M13.5 21v-7h2.4l.4-3h-2.8V9.1c0-.9.3-1.5 1.6-1.5h1.3V4.9c-.3 0-1.1-.1-2-.1-2 0-3.4 1.2-3.4 3.5V11H8.5v3h2.5v7h2.5z"/></svg>
                    </a>
                    <a href="#" aria-label="X" class="flex h-9 w-9 items-center justify-center rounded-lg border border-white/10 text-slate-300 transition hover:border-emerald-400 hover:text-emerald-300">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.9 2H22l-6.8 7.8L23.2 22h-6.3l-4.9-6.4L6.4 22H3.3l7.3-8.3L2.8 2h6.4l4.4 5.9L18.9 2zm-1.1 18h1.7L7.1 3.9H5.3L17.8 20z"/></svg>
                    </a>
                    <a href="#" aria-label="Instagram" class="flex h-9 w-9 items-center justify-center rounded-lg border border-white/10 text-slate-300 transition hover:border-emerald-400 hover:text-emerald-300">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.2" cy="6.8" r="0.8" fill="currentColor" stroke="none"/></svg>
                    </a>
                    <a href="#" aria-label="YouTube" class="flex h-9 w-9 items-center justify-center rounded-lg border border-white/10 text-slate-300 transition hover:border-emerald-400 hover:text-emerald-300">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23 12s0-3.8-.5-5.6c-.3-1-1-1.7-2-2C18.7 4 12 4 12 4s-6.7 0-8.5.4c-1 .3-1.7 1-2 2C1 8.2 1 12 1 12s0 3.8.5 5.6c.3 1 1 1.7 2 2C5.3 20 12 20 12 20s6.7 0 8.5-.4c1-.3 1.7-1 2-2 .5-1.8.5-5.6.5-5.6zM10 15.5v-7l6 3.5-6 3.5z"/></svg>
                    </a>
                </div>
                <p class="mt-5 text-sm text-slate-400">Have questions? Write to us — we're happy to help.</p>
            </div>
        </div>
    </div>

    <div class="border-t border-white/10">
        <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-2 px-4 py-5 text-xs text-slate-400 sm:flex-row sm:px-6">
            <p>© {{ date('Y') }} {{ config('admin.trust_name') }}. All rights reserved.</p>
            <p class="font-medium uppercase tracking-widest">Transparency · Service · Accountability</p>
        </div>
    </div>
</footer>

</body>
</html>
