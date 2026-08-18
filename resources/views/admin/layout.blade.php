<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') — {{ config('admin.trust_name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans text-slate-800 antialiased">
<div class="min-h-full lg:flex">

    {{-- Sidebar --}}
    <aside class="lg:w-72 lg:shrink-0 bg-slate-900 text-slate-300 lg:flex lg:flex-col lg:min-h-screen">
        <div class="flex h-16 items-center gap-3 border-b border-slate-800 px-5">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-500 font-bold text-white shadow-lg shadow-emerald-500/20">D</div>
            <div class="leading-tight">
                <p class="font-semibold text-white">{{ config('admin.trust_name') }}</p>
                <p class="text-xs text-slate-400">Trust Accounts</p>
            </div>
        </div>

        <nav class="space-y-1 px-3 py-4 text-sm font-medium">
            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center gap-3 rounded-lg px-3 py-2 {{ request()->routeIs('admin.dashboard') ? 'bg-slate-800 text-white' : 'hover:bg-slate-800/60 hover:text-white' }} transition">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M5 10v10h14V10"/></svg>
                Dashboard
            </a>
            <a href="{{ route('admin.entries.index') }}"
               class="flex items-center gap-3 rounded-lg px-3 py-2 {{ request()->routeIs('admin.entries.*') ? 'bg-slate-800 text-white' : 'hover:bg-slate-800/60 hover:text-white' }} transition">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                Entries
            </a>
            <a href="{{ route('admin.entries.create') }}"
               class="flex items-center gap-3 rounded-lg px-3 py-2 {{ request()->routeIs('admin.entries.create') ? 'bg-slate-800 text-white' : 'hover:bg-slate-800/60 hover:text-white' }} transition">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Entry
            </a>
            <a href="{{ route('admin.accounts.index') }}"
               class="flex items-center gap-3 rounded-lg px-3 py-2 {{ request()->routeIs('admin.accounts.*') ? 'bg-slate-800 text-white' : 'hover:bg-slate-800/60 hover:text-white' }} transition">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M3 10h18M3 14h18M3 18h18"/></svg>
                Accounts
            </a>
            <a href="{{ route('admin.reports.index') }}"
               class="flex items-center gap-3 rounded-lg px-3 py-2 {{ request()->routeIs('admin.reports.*') ? 'bg-slate-800 text-white' : 'hover:bg-slate-800/60 hover:text-white' }} transition">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3.5h14a1.5 1.5 0 011.5 1.5V19a1.5 1.5 0 01-1.5 1.5H5A1.5 1.5 0 013.5 19V5A1.5 1.5 0 015 3.5zm2.5 6h9m-9 4h6m-6 4h9"/></svg>
                Reports
            </a>
        </nav>

        {{-- Transparency portal --}}
        @php
            $groups = \App\Http\Controllers\TransparencyController::MENU;
            $groupNames = array_keys($groups);
            $activeGroup = null;
            foreach ($groups as $group => $items) {
                foreach ($items as [$page, $label]) {
                    if (request()->routeIs('admin.transparency.'.$page)) {
                        $activeGroup = $group;
                        break 2;
                    }
                }
            }
            $openState = array_fill(0, count($groupNames), false);
            if ($activeGroup !== null) {
                $openState[array_search($activeGroup, $groupNames)] = true;
            }
        @endphp
        <nav x-data="{ open: {{ json_encode($openState) }} }" class="mt-5 space-y-0.5 border-t border-slate-800 px-3 py-4 text-sm font-medium">
            @foreach ($groups as $group => $items)
                <button type="button"
                        @click="open[{{ $loop->index }}] = ! open[{{ $loop->index }}]"
                        class="flex w-full items-center gap-3 rounded-lg px-3 py-2 transition {{ $openState[$loop->index] ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z"/></svg>
                    <span class="flex-1 text-left">{{ $group }}</span>
                    <svg class="h-4 w-4 shrink-0 transition-transform duration-200" :class="open[{{ $loop->index }}] ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div x-show="open[{{ $loop->index }}]" style="{{ $openState[$loop->index] ? '' : 'display: none' }}">
                    @foreach ($items as [$page, $label])
                        <a href="{{ route('admin.transparency.'.$page) }}"
                           class="block rounded-lg px-3 py-2 text-[13px] font-normal leading-snug transition {{ request()->routeIs('admin.transparency.'.$page) ? 'bg-slate-800 font-medium text-white' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            @endforeach
        </nav>
    </aside>

    {{-- Main --}}
    <div class="min-w-0 flex-1">
        <header class="sticky top-0 z-10 flex h-16 items-center justify-between border-b border-slate-200 bg-white/90 px-4 backdrop-blur sm:px-6">
            <div>
                <h1 class="text-xl font-semibold text-slate-900">@yield('heading', 'Dashboard')</h1>
                <p class="hidden text-sm text-slate-500 sm:block">@yield('subheading', 'Trust accounts at a glance')</p>
            </div>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 hover:text-slate-900">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Sign out
                </button>
            </form>
        </header>

        <main class="p-4 sm:p-6">
            @if (session('success'))
                <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">
                    <p class="font-semibold">Please fix the following:</p>
                    <ul class="mt-1 list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
