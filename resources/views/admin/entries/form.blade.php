@extends('admin.layout')

@section('title', $entry->exists ? 'Edit Entry' : 'New Entry')
@section('heading', $entry->exists ? 'Edit Entry' : 'New Entry')
@section('subheading', $entry->exists ? 'Data correction facility — edit or correct this entry' : 'Record a new entry in the trust ledger')

@section('content')
    <div x-data="entryForm()" class="max-w-4xl">
        <form method="POST" action="{{ $entry->exists ? route('admin.entries.update', $entry) : route('admin.entries.store') }}" class="space-y-5">
            @csrf
            @if ($entry->exists) @method('PUT') @endif

            {{-- Step 1: type + basics --}}
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-lg font-semibold text-slate-900">1 · Entry details</h3>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Entry type *</label>
                        <select name="entry_type" id="entry_type" x-model="type" @change="syncType($el)" required
                                class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                            @foreach (\App\Models\Entry::GROUPS as $group => $types)
                                <optgroup label="{{ $group }}">
                                    @foreach ($types as $type)
                                        <option value="{{ $type }}" @selected(old('entry_type', $entry->entry_type) === $type)>{{ \App\Models\Entry::labelFor($type) }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Date *</label>
                        <input type="date" name="entry_date" value="{{ old('entry_date', $entry->entry_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required
                               class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Amount (₹) *</label>
                        <input type="number" step="0.01" min="0" name="amount" x-model="amount" value="{{ old('amount', $entry->amount) }}" required
                               class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    </div>
                </div>

                {{-- Account (hidden for transfers) --}}
                <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div x-show="type !== 'bank_transfer'">
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Account *</label>
                        <select name="account_id" class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                            <option value="">— Select account —</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}" @selected(old('account_id', $entry->account_id) == $account->id)>{{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div x-show="type === 'bank_transfer'" class="grid grid-cols-2 gap-5">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">From account *</label>
                            <select name="account_id" class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                <option value="">— Select —</option>
                                @foreach ($accounts as $account)
                                    <option value="{{ $account->id }}" @selected(old('account_id', $entry->account_id) == $account->id)>{{ $account->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">To account *</label>
                            <select name="to_account_id" class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                                <option value="">— Select —</option>
                                @foreach ($accounts as $account)
                                    <option value="{{ $account->id }}" @selected(old('to_account_id', $entry->to_account_id) == $account->id)>{{ $account->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Particulars / description</label>
                        <input type="text" name="particulars" value="{{ old('particulars', $entry->particulars) }}"
                               class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    </div>
                </div>

                <div x-show="type === 'fd_investment' || type === 'rd_investment' || type === 'mf_investment' || type === 'fd_maturity' || type === 'rd_maturity' || type === 'mf_maturity'" class="mt-5">
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Scheme / investment name</label>
                    <input type="text" name="scheme_name" value="{{ old('scheme_name', $entry->scheme_name) }}"
                           placeholder="e.g. SBI RD — 5 year, HDFC MF — ELSS"
                           class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                </div>
            </div>

            {{-- Step 2: donor details (donation income only) --}}
            <div x-show="type === 'donation_income'" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-lg font-semibold text-slate-900">2 · Donor details</h3>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Donor name</label>
                        <input type="text" name="donor_name" value="{{ old('donor_name', $entry->donor_name) }}"
                               class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">PAN number</label>
                        <input type="text" name="donor_pan" value="{{ old('donor_pan', $entry->donor_pan) }}" placeholder="e.g. ABCDE1234F"
                               class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Donor address</label>
                        <textarea name="donor_address" rows="2" class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">{{ old('donor_address', $entry->donor_address) }}</textarea>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Nature of donation</label>
                        <input type="text" name="nature_of_donation" value="{{ old('nature_of_donation', $entry->nature_of_donation) }}" placeholder="e.g. General / Corpus / Scholarship"
                               class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Mode of receipt</label>
                        <select name="mode_of_receipt" class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                            <option value="">— Select —</option>
                            @foreach (['Cash', 'Cheque', 'Bank transfer', 'UPI', 'Demand draft', 'Online'] as $mode)
                                <option value="{{ $mode }}" @selected(old('mode_of_receipt', $entry->mode_of_receipt) === $mode)>{{ $mode }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Section code (e.g. 80G)</label>
                        <input type="text" name="section_code" value="{{ old('section_code', $entry->section_code) }}" placeholder="e.g. 12A / 80G"
                               class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">DAP Trust receipt number</label>
                        <input type="text" name="receipt_number" value="{{ old('receipt_number', $entry->receipt_number) }}" placeholder="e.g. DAP/2026/0001"
                               class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Donor bank details</label>
                        <input type="text" name="donor_bank_details" value="{{ old('donor_bank_details', $entry->donor_bank_details) }}"
                               placeholder="Bank, branch, account no. & IFSC (if provided)"
                               class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    </div>
                </div>
            </div>

            {{-- Step 2: donation expense breakdown --}}
            <div x-show="type === 'donation_expense'" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-lg font-semibold text-slate-900">2 · Expense allocation</h3>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">To Ops (₹)</label>
                        <input type="number" step="0.01" min="0" name="ops_amount" value="{{ old('ops_amount', $entry->ops_amount) }}"
                               class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">To TSP (₹)</label>
                        <input type="number" step="0.01" min="0" name="tsp_amount" value="{{ old('tsp_amount', $entry->tsp_amount) }}"
                               class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Others (₹)</label>
                        <input type="number" step="0.01" min="0" name="others_amount" value="{{ old('others_amount', $entry->others_amount) }}"
                               class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    </div>
                </div>
            </div>

            {{-- Step 2: maturity details --}}
            <div x-show="type === 'fd_maturity' || type === 'rd_maturity' || type === 'mf_maturity'" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-lg font-semibold text-slate-900">2 · Maturity details</h3>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Interest received (₹)</label>
                        <input type="number" step="0.01" min="0" name="interest_amount" value="{{ old('interest_amount', $entry->interest_amount) }}"
                               class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Bank charges (₹)</label>
                        <input type="number" step="0.01" min="0" name="bank_charges_amount" value="{{ old('bank_charges_amount', $entry->bank_charges_amount) }}"
                               class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                    </div>
                </div>
                <p class="mt-3 text-xs text-slate-400">Enter the maturity principal in the Amount field above; interest and charges here are recorded separately for reporting.</p>
            </div>

            {{-- Notes --}}
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Notes</label>
                <textarea name="notes" rows="2" class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">{{ old('notes', $entry->notes) }}</textarea>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="rounded-lg bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-600/20 transition hover:bg-emerald-700">
                    {{ $entry->exists ? 'Save corrections' : 'Record entry' }}
                </button>
                <a href="{{ route('admin.entries.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">Cancel</a>
            </div>
        </form>
    </div>

    <script>
        function entryForm() {
            return {
                type: @json(old('entry_type', $entry->entry_type ?? 'donation_income')),
                amount: @json(old('amount', $entry->amount ?? '')),
                syncType(el) {
                    // keep x-model in sync (select options may be set by server-side old())
                    this.type = el.value;
                },
            };
        }
    </script>
@endsection
