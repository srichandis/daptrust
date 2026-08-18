{{-- Usage: @include('transparency.partials.stat-card', ['label' => ..., 'value' => ..., 'hint' => ..., 'color' => ...]) --}}
<div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
    <p class="text-sm font-medium text-slate-500">{{ $label }}</p>
    <p class="mt-1 text-2xl font-bold {{ $color ?? 'text-slate-900' }}">{{ $value }}</p>
    @isset($hint)
        <p class="mt-1 text-xs text-slate-400">{{ $hint }}</p>
    @endisset
</div>
