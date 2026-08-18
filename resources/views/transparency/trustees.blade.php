@extends('admin.layout')

@section('title', 'DAP Trustees Details')
@section('heading', 'DAP Trustees Details')
@section('subheading', 'About & Governance')

@section('content')
    <p class="mb-5 max-w-2xl text-sm leading-relaxed text-slate-600">
        The affairs of the trust are managed by its board of trustees. Trustee details are published here as per the
        trust's commitment to open governance.
    </p>

    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @foreach (['Trustee 1', 'Trustee 2', 'Trustee 3', 'Trustee 4', 'Trustee 5'] as $i => $name)
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-lg font-bold text-emerald-700">
                    {{ strtoupper(substr(explode(' ', $name)[1], 0, 1)) }}
                </div>
                <h3 class="mt-4 font-semibold text-slate-900">{{ $name }}</h3>
                <p class="mt-1 text-xs text-slate-400">Trustee · name to be confirmed</p>
                <p class="mt-3 text-sm leading-relaxed text-slate-500">
                    Please replace this placeholder with the trustee's name and designation.
                </p>
            </div>
        @endforeach
    </div>

    <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-800">
        <p class="font-semibold">Note</p>
        <p class="mt-1">Trustee names are placeholders — replace them with the actual board of trustees before publishing.</p>
    </div>
@endsection
