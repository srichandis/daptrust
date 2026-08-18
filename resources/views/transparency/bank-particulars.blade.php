@extends('admin.layout')

@section('title', 'DAP Trust Bank Particulars')
@section('heading', 'DAP Trust Bank Particulars')
@section('subheading', 'Investments & compliance · accounts held by the trust')

@section('content')
    <p class="mb-5 max-w-2xl text-sm leading-relaxed text-slate-600">
        Bank accounts and cash holdings of the trust. Account numbers and IFSC codes are masked for security.
    </p>

    <div class="grid gap-5 sm:grid-cols-2">
        @foreach ($accounts as $account)
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl {{ $account->type === 'cash' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }} text-lg font-bold">
                        {{ $account->type === 'cash' ? '₹' : strtoupper(substr($account->name, 0, 1)) }}
                    </div>
                    <span class="rounded-full px-2.5 py-1 text-xs font-medium capitalize {{ $account->type === 'cash' ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700' }}">{{ $account->type }}</span>
                </div>
                <h3 class="mt-4 font-semibold text-slate-900">{{ $account->name }}</h3>
                <p class="text-xs text-slate-400">{{ $account->bank_name ?: '—' }}</p>

                <dl class="mt-4 space-y-2 text-sm">
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-500">Balance</dt>
                        <dd class="font-semibold text-slate-900">{{ $money($account->balance()) }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-500">Branch</dt>
                        <dd class="text-slate-700">{{ $account->branch ?: '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-500">Account No.</dt>
                        <dd class="font-mono text-slate-700">{{ $account->account_number ? '••••'.substr($account->account_number, -4) : '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-slate-500">IFSC</dt>
                        <dd class="font-mono text-slate-700">{{ $account->ifsc ?: '—' }}</dd>
                    </div>
                    @if ($account->investment_type !== 'none')
                        <div class="flex items-center justify-between">
                            <dt class="text-slate-500">Investments</dt>
                            <dd class="font-medium text-indigo-600">{{ strtoupper($account->investment_type) }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        @endforeach
    </div>
@endsection
