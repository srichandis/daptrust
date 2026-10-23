@extends('admin.layout')

@section('title', 'Merge Similar Names')
@section('heading', 'Merge Similar Names')
@section('subheading', 'Find and merge entries with similar donor/particulars names')

@section('content')
    {{-- Filters --}}
    <div class="mb-5 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.merge.index') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Entry type</label>
                <select name="entry_type" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                    <option value="">All types</option>
                    @foreach ($entryTypes as $key => $label)
                        <option value="{{ $key }}" @selected(($filters['entry_type'] ?? '') === $key)>{{ $label }}</option>
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
                <label class="mb-1 block text-xs font-medium text-slate-500">Similarity threshold</label>
                <select name="threshold" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none">
                    <option value="0.6" @selected(($filters['threshold'] ?? '0.75') == '0.6')>Loose (60%)</option>
                    <option value="0.7" @selected(($filters['threshold'] ?? '0.75') == '0.7')>Medium (70%)</option>
                    <option value="0.75" @selected(($filters['threshold'] ?? '0.75') == '0.75')>Default (75%)</option>
                    <option value="0.85" @selected(($filters['threshold'] ?? '0.75') == '0.85')>Strict (85%)</option>
                    <option value="0.95" @selected(($filters['threshold'] ?? '0.75') == '0.95')>Exact (95%)</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-900">Find Groups</button>
                <a href="{{ route('admin.merge.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Reset</a>
            </div>
        </form>
    </div>

    {{-- Summary --}}
    <div class="mb-5 flex items-center gap-4">
        <div class="rounded-lg border border-slate-200 bg-white px-4 py-2 shadow-sm">
            <span class="text-sm text-slate-500">Groups found:</span>
            <span class="ml-1 font-semibold text-slate-800">{{ $groups->count() }}</span>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white px-4 py-2 shadow-sm">
            <span class="text-sm text-slate-500">Total entries to merge:</span>
            <span class="ml-1 font-semibold text-slate-800">{{ $groups->sum('count') }}</span>
        </div>
    </div>

    {{-- Groups --}}
    @forelse ($groups as $groupIdx => $group)
        <div class="mb-4 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm" x-data="{ open: false, merged: false }">
            {{-- Group Header --}}
            <button type="button" @click="open = !open"
                    class="flex w-full items-center justify-between px-5 py-4 text-left transition hover:bg-slate-50">
                <div class="flex items-center gap-4">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-sm font-bold text-emerald-700">
                        {{ $group['count'] }}
                    </span>
                    <div>
                        <p class="font-semibold text-slate-800">{{ $group['canonical'] }}</p>
                        <p class="text-sm text-slate-500">
                            {{ $group['count'] }} entries · Total ₹{{ indian_number_format($group['total'], 2) }}
                            @if(isset($group['unique_names']) && $group['unique_names']->count() > 1)
                                <span class="ml-2 text-xs text-amber-600">
                                    ({{ $group['unique_names']->count() }} variations: {{ $group['unique_names']->take(4)->implode(', ') }}{{ $group['unique_names']->count() > 4 ? '…' : '' }})
                                </span>
                            @endif
                        </p>
                    </div>
                </div>
                <svg class="h-5 w-5 shrink-0 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            {{-- Group Detail --}}
            <div x-show="open" x-cloak style="display: none;">
                <div class="border-t border-slate-100 px-5 py-4">
                    {{-- Merge Form --}}
                    <form method="POST" action="{{ route('admin.merge.submit') }}" class="mb-4"
                          x-on:submit="if(!confirm('Merge {{ $group['count'] }} entries under this name?')) return false;">
                        @csrf
                        <div class="flex flex-wrap items-end gap-3">
                            <div class="flex-1">
                                <label class="mb-1 block text-xs font-medium text-slate-500">Canonical name (all entries will be renamed to this)</label>
                                <input type="text" name="canonical_name" value="{{ $group['canonical'] }}"
                                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none"
                                       required>
                            </div>
                            @foreach ($group['entries'] as $entry)
                                <input type="hidden" name="entry_ids[]" value="{{ $entry->id }}">
                            @endforeach
                            <button type="submit"
                                    class="rounded-lg bg-emerald-600 px-5 py-2 text-sm font-semibold text-white shadow-lg shadow-emerald-600/20 transition hover:bg-emerald-700">
                                Merge {{ $group['count'] }} entries
                            </button>
                        </div>
                    </form>

                    {{-- Entries Table --}}
                    <div class="overflow-x-auto rounded-lg border border-slate-100">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                                    <th class="px-4 py-2 font-semibold">Date</th>
                                    <th class="px-4 py-2 font-semibold">Type</th>
                                    <th class="px-4 py-2 font-semibold">Account</th>
                                    <th class="px-4 py-2 font-semibold">Particulars</th>
                                    <th class="px-4 py-2 font-semibold">Donor Name</th>
                                    <th class="px-4 py-2 text-right font-semibold">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach ($group['entries'] as $entry)
                                    <tr class="hover:bg-slate-50">
                                        <td class="whitespace-nowrap px-4 py-2 text-slate-500">{{ $entry->entry_date?->format('d M Y') }}</td>
                                        <td class="px-4 py-2">
                                            <span class="rounded-full px-2 py-0.5 text-xs font-medium
                                                {{ $entry->is_income ? 'bg-emerald-100 text-emerald-700' : ($entry->is_expense ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-600') }}">
                                                {{ $entry->label }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-2 text-slate-600">{{ $entry->account?->name ?? '—' }}</td>
                                        <td class="max-w-xs truncate px-4 py-2 text-slate-500">{{ $entry->particulars ?: '—' }}</td>
                                        <td class="max-w-xs truncate px-4 py-2 font-medium text-slate-800">{{ $entry->donor_name ?: '—' }}</td>
                                        <td class="whitespace-nowrap px-4 py-2 text-right font-semibold {{ $entry->is_income ? 'text-emerald-600' : 'text-red-600' }}">
                                            ₹{{ indian_number_format($entry->amount, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="rounded-xl border border-slate-200 bg-white px-6 py-12 text-center shadow-sm">
            <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="mt-3 text-slate-500">No similar name groups found with the current filters.</p>
            <p class="mt-1 text-sm text-slate-400">Try lowering the similarity threshold or removing filters.</p>
        </div>
    @endforelse
@endsection
