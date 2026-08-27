<div class="grid gap-4 md:grid-cols-3">
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-5">
        <p class="text-xs uppercase tracking-wide text-emerald-700">Total income</p>
        <p class="mt-2 text-3xl font-bold text-emerald-900">₹{{ indian_number_format($incomeTotal, 2) }}</p>
    </div>
    <div class="rounded-xl border border-rose-200 bg-rose-50 p-5">
        <p class="text-xs uppercase tracking-wide text-rose-700">Total expenses</p>
        <p class="mt-2 text-3xl font-bold text-rose-900">₹{{ indian_number_format($expenseTotal, 2) }}</p>
    </div>
    <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-5">
        <p class="text-xs uppercase tracking-wide text-indigo-700">Total savings</p>
        <p class="mt-2 text-3xl font-bold text-indigo-900">₹{{ indian_number_format($savingsTotal, 2) }}</p>
    </div>
</div>
