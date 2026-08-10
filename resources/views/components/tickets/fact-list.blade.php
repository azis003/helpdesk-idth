@props(['facts' => []])

<dl class="divide-y divide-[#eef3f5]">
    @foreach ($facts as $fact)
        <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 px-5 py-3 sm:px-6">
            <dt class="text-xs font-extrabold uppercase tracking-[0.08em] text-[#78909a]">{{ $fact['label'] }}</dt>
            <dd class="text-right text-sm font-bold text-[#35505b]">{{ $fact['value'] }}</dd>
        </div>
    @endforeach
</dl>
