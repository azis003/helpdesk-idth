@props(['events' => []])

<ol class="p-5 sm:p-6">
    @foreach ($events as $event)
        @php
            $dot = match ($event['tone']) {
                'success' => 'bg-[#2bb8aa]',
                'attention' => 'bg-[#e4a72c]',
                'danger' => 'bg-[#e11d48]',
                'progress' => 'bg-[#75d5f3]',
                default => 'bg-[#a9bbc2]',
            };
        @endphp

        <li class="relative flex gap-4 pb-5 last:pb-0">
            @unless ($loop->last)
                <span class="absolute left-[0.3rem] top-4 h-full w-px bg-[#e3ecef]" aria-hidden="true"></span>
            @endunless

            <span class="relative mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full ring-4 ring-white {{ $dot }}" aria-hidden="true"></span>

            <div class="min-w-0 flex-1">
                <p class="text-sm font-extrabold text-[#35505b]">{{ $event['title'] }}</p>
                <p class="mt-1 text-xs leading-5 text-[#78909a]">{{ $event['description'] }}</p>
                @if ($event['occurredAt'])
                    <time class="mt-1 block text-xs text-[#9aaeb6]" datetime="{{ $event['occurredAt']->toIso8601String() }}">{{ $event['occurredAt']->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i') }}</time>
                @endif
            </div>
        </li>
    @endforeach
</ol>
