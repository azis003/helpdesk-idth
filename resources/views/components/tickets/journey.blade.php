@props(['steps' => []])

<ol class="grid gap-4 sm:grid-cols-4" aria-label="Progres penanganan tiket">
    @foreach ($steps as $step)
        @php
            $state = $step['state'];
            $badge = match ($state) {
                'done' => 'border-[#2bb8aa] bg-[#2bb8aa] text-white',
                'current' => 'border-[#75d5f3] bg-white text-[#147a79] shadow-[0_0_0_4px_#d9f6ff]',
                default => 'border-[#dce7eb] bg-white text-[#a9bbc2]',
            };
            $rail = match ($state) {
                'done' => 'bg-[#2bb8aa]',
                'current' => 'bg-[#75d5f3]',
                default => 'bg-[#e3ecef]',
            };
            $stateLabel = match ($state) {
                'done' => 'Selesai',
                'current' => 'Tahap saat ini',
                default => 'Belum dimulai',
            };
        @endphp

        <li @if ($state === 'current') aria-current="step" @endif>
            <div class="flex items-center gap-2">
                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full border-2 text-[0.7rem] font-extrabold {{ $badge }}" aria-hidden="true">
                    @if ($state === 'done')
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m5 13 4 4 10-10" /></svg>
                    @else
                        {{ $loop->iteration }}
                    @endif
                </span>
                <span class="h-1 grow rounded-full {{ $rail }}" aria-hidden="true"></span>
            </div>

            <p class="mt-3 text-sm font-extrabold {{ $state === 'upcoming' ? 'text-[#9aaeb6]' : 'text-[#263a43]' }}">{{ $step['label'] }}</p>
            <p class="mt-1 text-xs leading-5 {{ $state === 'upcoming' ? 'text-[#a9bbc2]' : 'text-[#78909a]' }}">{{ $step['caption'] }}</p>
            <span class="sr-only">{{ $stateLabel }}</span>
        </li>
    @endforeach
</ol>
