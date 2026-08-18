@extends('admin.layout')

@section('title', 'DAP Trust OPS Donation')
@section('heading', 'DAP Trust OPS Donation')
@section('subheading', 'Investments · donations to OPS Saiva Peravai')

@section('content')
    <p class="mb-5 max-w-2xl text-sm leading-relaxed text-slate-600">
        Donations made by the trust to OPS Saiva Peravai and its centres — recorded and published live.
    </p>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        @include('transparency.partials.stat-card', ['label' => 'Total OPS Donations', 'value' => $money($total), 'hint' => 'All OPS donation entries', 'color' => 'text-emerald-600'])
        @include('transparency.partials.stat-card', ['label' => 'OPS Centres', 'value' => number_format($byPlace->count()), 'hint' => 'Distinct centres supported'])
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-slate-900">By Centre</h2>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($byPlace->sortDesc() as $place => $totalPlace)
                    <div class="flex items-center justify-between px-5 py-3 text-sm">
                        <span class="text-slate-600">{{ $place }}</span>
                        <span class="font-semibold text-emerald-600">{{ $money($totalPlace) }}</span>
                    </div>
                @empty
                    <p class="px-5 py-6 text-sm text-slate-500">No OPS donations recorded yet.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-slate-900">Recent OPS Donations</h2>
                <span class="text-xs font-medium text-emerald-600">● Live</span>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($entries->take(12) as $entry)
                    <div class="flex items-center justify-between px-5 py-3 text-sm">
                        <div>
                            <p class="text-slate-600">{{ $entry->entry_date?->format('d M Y') }} · {{ $entry->particulars }}</p>
                            <p class="text-xs text-slate-400">{{ $entry->account?->name ?? '—' }}</p>
                        </div>
                        <span class="font-semibold text-emerald-600">{{ $money($entry->amount) }}</span>
                    </div>
                @empty
                    <p class="px-5 py-6 text-sm text-slate-500">No OPS donations recorded yet.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
