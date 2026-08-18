<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(): View
    {
        $accounts = Account::withCount('entries')->orderBy('type')->orderBy('name')->get();

        return view('admin.accounts.index', compact('accounts'));
    }

    public function create(): View
    {
        return view('admin.accounts.form', ['account' => new Account]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        Account::create($data);

        return redirect()
            ->route('admin.accounts.index')
            ->with('success', 'Account created.');
    }

    public function edit(Account $account): View
    {
        return view('admin.accounts.form', compact('account'));
    }

    public function update(Request $request, Account $account): RedirectResponse
    {
        $data = $this->validated($request);

        $account->update($data);

        return redirect()
            ->route('admin.accounts.index')
            ->with('success', 'Account updated.');
    }

    public function destroy(Account $account): RedirectResponse
    {
        $account->delete();

        return redirect()
            ->route('admin.accounts.index')
            ->with('success', 'Account deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:bank,cash'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'branch' => ['nullable', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:255'],
            'ifsc' => ['nullable', 'string', 'max:255'],
            'investment_type' => ['required', 'in:rd,mf,none'],
            'opening_balance' => ['required', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }
}
