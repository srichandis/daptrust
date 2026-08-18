@extends('admin.layout')

@section('title', 'DAP Trust Govt Certificate')
@section('heading', 'DAP Trust Govt Certificate')
@section('subheading', 'Compliance · registrations & certificates')

@section('content')
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ([
            ['Trust Registration', '127998', 'Registered under the Indian Trusts Act'],
            ['PAN', 'Available', 'Income Tax Permanent Account Number'],
            ['80G Certificate', 'Available', 'Donations eligible for tax deduction'],
            ['12A Certificate', 'Available', 'Trust income exempt under Section 12A'],
            ['NIT Aayog ID', 'Available', 'NITI Aayog registration'],
        ] as [$title, $value, $hint])
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Verified
                    </span>
                </div>
                <h3 class="mt-4 font-semibold text-slate-900">{{ $title }}</h3>
                <p class="mt-1 text-lg font-bold text-emerald-600">{{ $value }}</p>
                <p class="mt-1 text-xs text-slate-400">{{ $hint }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-800">
        <p class="font-semibold">Note</p>
        <p class="mt-1">Registration number and certificate details are placeholders from the design mockup — replace them with the actual certificates before publishing.</p>
    </div>
@endsection
