@extends('admin.layout')

@section('title', 'DAP Trust Resolution')
@section('heading', 'DAP Trust Resolution')
@section('subheading', 'Compliance · resolutions passed by the board')

@section('content')
    <p class="mb-5 max-w-2xl text-sm leading-relaxed text-slate-600">
        Resolutions passed by the board of trustees are recorded here for public reference.
    </p>

    <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-slate-900">Board Resolutions</h2>
        </div>
        <div class="divide-y divide-slate-100">
            @foreach (['Constitution of the board of trustees', 'Opening of bank accounts and authorisation of signatories', 'Appointment of auditors for the financial year', 'Investment policy for corpus funds (FD / RD / Mutual Funds)', 'Charitable disbursements and donations policy', 'Annual report and accounts adoption'] as $resolution)
                <div class="flex items-start gap-4 px-5 py-4">
                    <div class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-800">{{ $resolution }}</p>
                        <p class="mt-0.5 text-xs text-slate-400">Placeholder — resolution text to be confirmed</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
