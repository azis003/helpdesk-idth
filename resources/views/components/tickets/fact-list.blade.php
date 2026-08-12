@props(['facts' => []])

<dl class="divide-y divide-[color:var(--tm-border-subtle)]">
    @foreach ($facts as $fact)
        <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 px-5 py-3 transition-colors hover:bg-[color:var(--tm-n-25)] sm:px-6">
            <dt class="text-[0.68rem] font-extrabold uppercase tracking-[0.09em] text-[color:var(--tm-text-muted)]">{{ $fact['label'] }}</dt>
            <dd class="text-right text-sm font-bold tabular-nums text-[color:var(--tm-text)]">{{ $fact['value'] }}</dd>
        </div>
    @endforeach
</dl>
