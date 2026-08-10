@props(['events' => [], 'variant' => 'default'])

@if ($variant === 'reference')
    <ol class="ticket-reference-timeline mt-6" aria-label="Urutan aktivitas tiket">
        @foreach ($events as $event)
            @php
                $isCurrent = $loop->last;
                $markerTone = match ($event['tone']) {
                    'danger' => 'danger',
                    'attention' => 'attention',
                    'success' => 'success',
                    default => 'progress',
                };
            @endphp

            <li class="ticket-reference-timeline-item" @if ($isCurrent) aria-current="step" @endif>
                <span class="ticket-reference-timeline-marker ticket-reference-timeline-marker--{{ $markerTone }} {{ $isCurrent ? 'ticket-reference-timeline-marker--current' : '' }}" aria-hidden="true">
                    @if (! $isCurrent && $markerTone === 'success')
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m5 13 4 4 10-10" /></svg>
                    @elseif ($isCurrent)
                        <span class="h-2.5 w-2.5 rounded-full bg-current"></span>
                    @else
                        <span class="h-2 w-2 rounded-full bg-current"></span>
                    @endif
                </span>

                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-baseline gap-x-2 gap-y-1">
                        <h3 class="text-sm font-bold {{ $isCurrent ? 'text-[#0037b0]' : 'text-[#0b1c30]' }}">{{ $event['title'] }}</h3>
                        @if ($event['occurredAt'])
                            <time class="text-sm text-[#434655]" datetime="{{ $event['occurredAt']->toIso8601String() }}">{{ $event['occurredAt']->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i') }} WIB</time>
                        @endif
                    </div>
                    <p class="mt-1.5 text-sm leading-6 text-[#434655]">{{ $event['description'] }}</p>
                </div>
            </li>
        @endforeach
    </ol>
@else

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
@endif
