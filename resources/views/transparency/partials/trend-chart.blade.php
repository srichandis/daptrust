{{-- Expects $series (label, fy, value), $max, $money --}}
<div class="relative h-52">
    <div class="flex h-full items-end gap-[3px]">
        @foreach ($series as $s)
            <div class="group relative flex h-full flex-1 items-end" title="{{ $s['fy'] }} · {{ $money($s['value']) }}">
                <div class="w-full rounded-t bg-emerald-500/75 transition group-hover:bg-emerald-500"
                     style="height: {{ max(round($s['value'] / $max * 100, 2), 1) }}%"></div>
            </div>
        @endforeach
    </div>
    <div class="pointer-events-none absolute inset-x-0 top-0 flex justify-between text-[10px] text-slate-300">
        <span>{{ $money($max) }}</span>
        <span>0</span>
    </div>
</div>
<div class="mt-2 flex gap-[3px]">
    @foreach ($series as $s)
        <div class="flex-1 text-center text-[9px] {{ $loop->index % 5 === 0 ? 'text-slate-500' : 'text-transparent' }}">{{ $s['label'] }}</div>
    @endforeach
</div>
