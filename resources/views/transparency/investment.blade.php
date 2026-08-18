@extends('admin.layout')

@section('title', 'DAP Trust ' . $label . ' Details')
@section('heading', 'DAP Trust ' . $label . ' Details')
@section('subheading', 'Investments · live register')

@section('content')
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        @include('transparency.partials.stat-card', ['label' => 'Invested', 'value' => $money($invested), 'hint' => 'Total ' . $label . ' invested', 'color' => 'text-emerald-600'])
        @include('transparency.partials.stat-card', ['label' => 'Interest Earned', 'value' => $money($interestTotal), 'hint' => 'All ' . $label . ' interest', 'color' => 'text-emerald-600'])
        @include('transparency.partials.stat-card', ['label' => 'Matured', 'value' => $money($matured), 'hint' => 'Principal returned on maturity'])
    </div>

    <div class="mt-6 rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-slate-900">Investments</h2>
            <span class="text-xs font-medium text-emerald-600">● Live</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-xs uppercase tracking-wide text-slate-400">
                        <th class="px-5 py-3 font-medium">Date</th>
                        <th class="px-5 py-3 font-medium">Account</th>
                        <th class="px-5 py-3 font-medium">Particulars</th>
                        <th class="px-5 py-3 text-right font-medium">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($investments as $entry)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 text-slate-500">{{ $entry->entry_date?->format('d M Y') }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $entry->account?->name ?? '—' }}</td>
                            <td class="max-w-xs truncate px-5 py-3 text-slate-500">{{ $entry->particulars ?: '—' }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-emerald-600">{{ $money($entry->amount) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-6 text-center text-slate-500">No {{ strtolower($label) }} investments recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-slate-900">Interest</h2>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($interests as $entry)
                    <div class="flex items-center justify-between px-5 py-3 text-sm">
                        <span class="text-slate-600">{{ $entry->entry_date?->format('d M Y') }} · {{ $entry->account?->name ?? '—' }}</span>
                        <span class="font-semibold text-emerald-600">{{ $money($entry->amount) }}</span>
                    </div>
                @empty
                    <p class="px-5 py-6 text-sm text-slate-500">No interest entries recorded yet.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-lg font-semibold text-slate-900">Maturities</h2>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($maturities as $entry)
                    <div class="flex items-center justify-between px-5 py-3 text-sm">
                        <div>
                            <p class="text-slate-600">{{ $entry->entry_date?->format('d M Y') }} · {{ $entry->account?->name ?? '—' }}</p>
                            @if ($entry->interest_amount)
                                <p class="text-xs text-slate-400">Interest {{ $money($entry->interest_amount) }}</p>
                            @endif
                        </div>
                        <span class="font-semibold text-slate-800">{{ $money($entry->amount) }}</span>
                    </div>
                @empty
                    <p class="px-5 py-6 text-sm text-slate-500">No maturities recorded yet.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
