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
                        <h4 class="text-sm font-bold {{ $isCurrent ? 'text-[color:var(--tm-brand-700)]' : 'text-[color:var(--tm-text)]' }}">{{ $event['title'] }}</h4>
                        @if ($event['occurredAt'])
                            <time class="text-sm tabular-nums text-[color:var(--tm-text-muted)]" datetime="{{ $event['occurredAt']->toIso8601String() }}">{{ $event['occurredAt']->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i') }} WIB</time>
                        @endif
                    </div>
                    <p class="mt-1.5 text-sm leading-6 text-[color:var(--tm-text-secondary)]">{{ $event['description'] }}</p>
                </div>
            </li>
        @endforeach
    </ol>
@else

<ol class="p-5 sm:p-6">
    @foreach ($events as $event)
        @php
            $dot = match ($event['tone']) {
                'success' => 'bg-[color:var(--tm-success-600)]',
                'attention' => 'bg-[color:var(--tm-warning-600)]',
                'danger' => 'bg-[color:var(--tm-danger-600)]',
                'progress' => 'bg-[color:var(--tm-brand-400)]',
                default => 'bg-[color:var(--tm-n-400)]',
            };
        @endphp

        <li class="relative flex gap-4 pb-5 last:pb-0">
            @unless ($loop->last)
                <span class="absolute left-[0.3rem] top-4 h-full w-px bg-[color:var(--tm-border)]" aria-hidden="true"></span>
            @endunless

            <span class="relative mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full ring-4 ring-[color:var(--tm-surface)] {{ $dot }}" aria-hidden="true"></span>

            <div class="min-w-0 flex-1">
                <p class="text-sm font-extrabold text-[color:var(--tm-text)]">{{ $event['title'] }}</p>
                <p class="mt-1 text-xs leading-5 text-[color:var(--tm-text-muted)]">{{ $event['description'] }}</p>
                @if ($event['occurredAt'])
                    <time class="mt-1 block text-xs tabular-nums text-[color:var(--tm-text-faint)]" datetime="{{ $event['occurredAt']->toIso8601String() }}">{{ $event['occurredAt']->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i') }}</time>
                @endif
            </div>
        </li>
    @endforeach
</ol>
@endif
