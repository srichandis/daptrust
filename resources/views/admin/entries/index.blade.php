@extends('admin.layout')

@section('title', 'Entries')
@section('heading', 'Entries')
@section('subheading', 'The complete trust ledger')

@section('content')
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-500">{{ $entries->total() }} entr{{ $entries->total() === 1 ? 'y' : 'ies' }} · Filtered total ₹{{ indian_number_format($totalAmount, 2) }}</p>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.entries.create') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-emerald-600/20 transition hover:bg-emerald-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Entry
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="mb-5 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.entries.index') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-7">
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Entry type</label>
                <select name="entry_type" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                    <option value="">All types</option>
                    @foreach (\App\Models\Entry::GROUPS as $group => $types)
                        <optgroup label="{{ $group }}">
                            @foreach ($types as $type)
                                <option value="{{ $type }}" @selected(($filters['entry_type'] ?? '') === $type)>{{ \App\Models\Entry::labelFor($type) }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Account</label>
                <select name="account_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                    <option value="">All accounts</option>
                    @foreach ($accounts as $account)
                        <option value="{{ $account->id }}" @selected(($filters['account_id'] ?? '') == $account->id)>{{ $account->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Financial year</label>
                <select name="financial_year" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                    <option value="">All years</option>
                    @foreach ($financialYears as $fy)
                        <option value="{{ $fy }}" @selected(($filters['financial_year'] ?? '') === $fy)>{{ $fy }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">From</label>
                <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">To</label>
                <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Search</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Donor, receipt, notes…"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-900">Filter</button>
                <a href="{{ route('admin.entries.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Reset</a>
                <a href="{{ route('admin.entries.export', request()->only(['entry_type', 'account_id', 'financial_year', 'from', 'to'])) }}"
                   class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700 hover:bg-emerald-100">CSV</a>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <th class="px-4 py-3 font-semibold">Date</th>
                        <th class="px-4 py-3 font-semibold">Type</th>
                        <th class="px-4 py-3 font-semibold">Account</th>
                        <th class="px-4 py-3 font-semibold">Particulars / Donor</th>
                        <th class="px-4 py-3 text-right font-semibold">Amount</th>
                        <th class="px-4 py-3 text-right font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($entries as $entry)
                        <tr class="hover:bg-slate-50">
                            <td class="whitespace-nowrap px-4 py-3 text-slate-500">{{ $entry->entry_date?->format('d M Y') }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-medium
                                    {{ $entry->is_income ? 'bg-emerald-100 text-emerald-700' : ($entry->is_expense ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-600') }}">
                                    {{ $entry->label }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-600">
                                {{ $entry->account?->name ?? '—' }}
                                @if ($entry->toAccount)
                                    <span class="text-slate-400">→ {{ $entry->toAccount->name }}</span>
                                @endif
                            </td>
                            <td class="max-w-xs px-4 py-3">
                                @if ($entry->donor_name)
                                    <p class="font-medium text-slate-800">{{ $entry->donor_name }}</p>
                                    <p class="truncate text-xs text-slate-400">{{ $entry->particulars ?: '' }}</p>
                                @else
                                    <p class="truncate text-slate-500">{{ $entry->particulars ?: '—' }}</p>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right font-semibold {{ $entry->is_income ? 'text-emerald-600' : ($entry->is_expense ? 'text-red-600' : 'text-slate-700') }}">
                                ₹{{ indian_number_format($entry->amount, 2) }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('admin.entries.edit', $entry) }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-700">Edit</a>
                                    <form method="POST" action="{{ route('admin.entries.destroy', $entry) }}"
                                          onsubmit="return confirm('Delete this entry? This is the data-correction facility — deletion is permanent.');">
                                        @csrf @method('DELETE')
                                        <button class="text-sm font-medium text-red-500 hover:text-red-600">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-slate-500">No entries match your filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-4 py-3">
            {{ $entries->links() }}
        </div>
    </div>
@endsection
