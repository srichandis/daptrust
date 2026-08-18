@extends('admin.layout')

@section('title', 'DAP Trust IT Return Details')
@section('heading', 'DAP Trust IT Return Details')
@section('subheading', 'Investments & compliance · live register')

@section('content')
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        @include('transparency.partials.stat-card', ['label' => 'Refunds Received', 'value' => $money($refunds), 'hint' => 'IT return refunds', 'color' => 'text-emerald-600'])
        @include('transparency.partials.stat-card', ['label' => 'Tax Paid', 'value' => $money($paid), 'hint' => 'IT paid entries', 'color' => 'text-red-600'])
    </div>

    <div class="mt-6 rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <h2 class="text-lg font-semibold text-slate-900">IT Return Register</h2>
            <span class="text-xs font-medium text-emerald-600">● Live</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-xs uppercase tracking-wide text-slate-400">
                        <th class="px-5 py-3 font-medium">Date</th>
                        <th class="px-5 py-3 font-medium">Type</th>
                        <th class="px-5 py-3 font-medium">Account</th>
                        <th class="px-5 py-3 font-medium">Particulars</th>
                        <th class="px-5 py-3 text-right font-medium">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($entries as $entry)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 text-slate-500">{{ $entry->entry_date?->format('d M Y') }}</td>
                            <td class="px-5 py-3">
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $entry->entry_type === \App\Models\Entry::TYPE_IT_RETURN ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $entry->label }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-slate-600">{{ $entry->account?->name ?? '—' }}</td>
                            <td class="max-w-xs truncate px-5 py-3 text-slate-500">{{ $entry->particulars ?: '—' }}</td>
                            <td class="px-5 py-3 text-right font-semibold {{ $entry->entry_type === \App\Models\Entry::TYPE_IT_RETURN ? 'text-emerald-600' : 'text-red-600' }}">
                                {{ ($entry->entry_type === \App\Models\Entry::TYPE_IT_RETURN ? '+' : '−') . $money($entry->amount) }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-6 text-center text-slate-500">No IT return entries recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
