@props(['steps' => []])

<ol class="grid gap-4 sm:grid-cols-4" aria-label="Progres penanganan tiket">
    @foreach ($steps as $step)
        @php
            $state = $step['state'];
            $badge = match ($state) {
                'done' => 'border-[color:var(--tm-success-600)] bg-[color:var(--tm-success-600)] text-white',
                'current' => 'border-[color:var(--tm-brand-400)] bg-[color:var(--tm-surface)] text-[color:var(--tm-brand-700)] shadow-[0_0_0_4px_var(--tm-brand-100)]',
                default => 'border-[color:var(--tm-border)] bg-[color:var(--tm-surface)] text-[color:var(--tm-text-faint)]',
            };
            $rail = match ($state) {
                'done' => 'bg-[color:var(--tm-success-600)]',
                'current' => 'bg-[color:var(--tm-brand-400)]',
                default => 'bg-[color:var(--tm-n-200)]',
            };
            $stateLabel = match ($state) {
                'done' => 'Selesai',
                'current' => 'Tahap saat ini',
                default => 'Belum dimulai',
            };
        @endphp

        <li @if ($state === 'current') aria-current="step" @endif>
            <div class="flex items-center gap-2">
                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full border-2 text-[0.7rem] font-extrabold tabular-nums {{ $badge }}" aria-hidden="true">
                    @if ($state === 'done')
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m5 13 4 4 10-10" /></svg>
                    @else
                        {{ $loop->iteration }}
                    @endif
                </span>
                <span class="h-1 grow rounded-full {{ $rail }}" aria-hidden="true"></span>
            </div>

            <p class="mt-3 text-sm font-extrabold {{ $state === 'upcoming' ? 'text-[color:var(--tm-text-faint)]' : 'text-[color:var(--tm-text)]' }}">{{ $step['label'] }}</p>
            <p class="mt-1 text-xs leading-5 {{ $state === 'upcoming' ? 'text-[color:var(--tm-text-faint)]' : 'text-[color:var(--tm-text-muted)]' }}">{{ $step['caption'] }}</p>
            <span class="sr-only">{{ $stateLabel }}</span>
        </li>
    @endforeach
</ol>
