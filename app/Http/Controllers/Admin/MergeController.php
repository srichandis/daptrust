<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Entry;
use App\Services\MergeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MergeController extends Controller
{
    public function __construct(
        private MergeService $mergeService,
    ) {}

    public function index(Request $request): View
    {
        $entryType = $request->input('entry_type', 'donation_income');
        $accountId = $request->input('account_id') ? (int) $request->input('account_id') : null;
        $threshold = (float) $request->input('threshold', 0.75);

        $groups = $this->mergeService->findSimilarGroups(
            entryType: $entryType ?: null,
            accountId: $accountId,
            threshold: $threshold,
            minGroupSize: 2,
        );

        $accounts = Account::orderBy('name')->get();
        $entryTypes = Entry::LABELS;

        return view('admin.merge.index', [
            'groups' => $groups,
            'accounts' => $accounts,
            'entryTypes' => $entryTypes,
            'filters' => $request->only(['entry_type', 'account_id', 'threshold']),
        ]);
    }

    public function merge(Request $request): RedirectResponse
    {
        $request->validate([
            'entry_ids' => ['required', 'array'],
            'entry_ids.*' => ['required', 'exists:entries,id'],
            'canonical_name' => ['required', 'string', 'max:255'],
        ]);

        $count = $this->mergeService->mergeGroup(
            $request->input('entry_ids'),
            $request->input('canonical_name'),
        );

        return redirect()
            ->route('admin.merge.index', $request->only(['entry_type', 'account_id', 'threshold']))
            ->with('success', "Merged {$count} entries under \"{$request->input('canonical_name')}\".");
    }
}
