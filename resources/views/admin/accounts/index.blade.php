@extends('admin.layout')

@section('title', 'Accounts')
@section('heading', 'Accounts')
@section('subheading', 'Bank and cash accounts of the trust')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <p class="text-sm text-slate-500">{{ $accounts->count() }} account(s)</p>
        <a href="{{ route('admin.accounts.create') }}"
           class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-emerald-600/20 transition hover:bg-emerald-700">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Add Account
        </a>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <th class="px-5 py-3 font-semibold">Account</th>
                    <th class="px-5 py-3 font-semibold">Type</th>
                    <th class="px-5 py-3 font-semibold">Bank / Details</th>
                    <th class="px-5 py-3 font-semibold">Investments</th>
                    <th class="px-5 py-3 text-right font-semibold">Opening Balance</th>
                    <th class="px-5 py-3 text-right font-semibold">Entries</th>
                    <th class="px-5 py-3 text-center font-semibold">Status</th>
                    <th class="px-5 py-3 text-right font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($accounts as $account)
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-3.5 font-medium text-slate-900">{{ $account->name }}</td>
                        <td class="px-5 py-3.5">
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $account->type === 'cash' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700' }}">
                                {{ ucfirst($account->type) }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-slate-600">
                            @if ($account->type === 'bank')
                                {{ $account->bank_name ?: '—' }}<br>
                                <span class="text-xs text-slate-400">
                                    A/c {{ $account->account_number ?: '—' }}
                                    @if ($account->ifsc) · {{ $account->ifsc }} @endif
                                </span>
                            @else
                                <span class="text-slate-400">Cash in hand</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            @if ($account->investment_type === 'none')
                                <span class="text-slate-400">—</span>
                            @else
                                <span class="rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-700">
                                    {{ strtoupper($account->investment_type) }}
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-right text-slate-700">₹{{ number_format($account->opening_balance, 2) }}</td>
                        <td class="px-5 py-3.5 text-right text-slate-500">{{ $account->entries_count }}</td>
                        <td class="px-5 py-3.5 text-center">
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $account->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $account->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-3">
                                <a href="{{ route('admin.accounts.edit', $account) }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-700">Edit</a>
                                <form method="POST" action="{{ route('admin.accounts.destroy', $account) }}"
                                      onsubmit="return confirm('Delete account &quot;{{ $account->name }}&quot;? Entries keep a null reference.');">
                                    @csrf @method('DELETE')
                                    <button class="text-sm font-medium text-red-500 hover:text-red-600">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-5 py-8 text-center text-slate-500">No accounts yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
