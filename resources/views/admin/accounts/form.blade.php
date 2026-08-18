@extends('admin.layout')

@section('title', $account->exists ? 'Edit Account' : 'New Account')
@section('heading', $account->exists ? 'Edit Account' : 'New Account')
@section('subheading', $account->exists ? 'Update account details' : 'Add a bank or cash account')

@section('content')
    <div class="max-w-2xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ $account->exists ? route('admin.accounts.update', $account) : route('admin.accounts.store') }}" class="space-y-5">
            @csrf
            @if ($account->exists) @method('PUT') @endif

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Account name *</label>
                    <input type="text" name="name" value="{{ old('name', $account->name) }}" required
                           placeholder="e.g. HDFC Bank — Main Account"
                           class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Type *</label>
                    <select name="type" class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                        <option value="bank" @selected(old('type', $account->type) === 'bank')>Bank</option>
                        <option value="cash" @selected(old('type', $account->type) === 'cash')>Cash</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Investment type *</label>
                    <select name="investment_type" class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                        <option value="none" @selected(old('investment_type', $account->investment_type) === 'none')>None</option>
                        <option value="rd" @selected(old('investment_type', $account->investment_type) === 'rd')>RD</option>
                        <option value="mf" @selected(old('investment_type', $account->investment_type) === 'mf')>MF</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Bank name</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name', $account->bank_name) }}" placeholder="e.g. State Bank of India"
                           class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Branch</label>
                    <input type="text" name="branch" value="{{ old('branch', $account->branch) }}" placeholder="e.g. Jayanagar"
                           class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Account number</label>
                    <input type="text" name="account_number" value="{{ old('account_number', $account->account_number) }}"
                           class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">IFSC</label>
                    <input type="text" name="ifsc" value="{{ old('ifsc', $account->ifsc) }}" placeholder="e.g. SBIN0001234"
                           class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Opening balance (₹)</label>
                    <input type="number" step="0.01" min="0" name="opening_balance" value="{{ old('opening_balance', $account->opening_balance ?? 0) }}"
                           class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                </div>

                <div class="flex items-end">
                    <label class="inline-flex cursor-pointer items-center gap-2.5 text-sm font-medium text-slate-700">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $account->is_active ?? true))
                               class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        Active account
                    </label>
                </div>
            </div>

            <div class="flex items-center gap-3 border-t border-slate-100 pt-5">
                <button type="submit" class="rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-600/20 transition hover:bg-emerald-700">
                    {{ $account->exists ? 'Save changes' : 'Create account' }}
                </button>
                <a href="{{ route('admin.accounts.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">Cancel</a>
            </div>
        </form>
    </div>
@endsection
