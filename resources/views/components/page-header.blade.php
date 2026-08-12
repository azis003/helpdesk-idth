@props([
    'eyebrow',
    'title',
    'description' => null,
    'backUrl' => null,
    'backLabel' => 'Kembali',
    'dotClass' => '',
])

<header {{ $attributes->merge(['class' => 'ui-page-header']) }}>
    <div class="min-w-0 flex-1">
        @if ($backUrl)
            <a href="{{ $backUrl }}" class="ui-action-link mb-4 inline-flex items-center gap-1.5 rounded-[var(--tm-r-full)] border border-[color:var(--tm-border)] bg-[color:var(--tm-surface)] px-3 py-1.5 text-xs font-semibold shadow-[var(--tm-sh-xs)] transition hover:-translate-x-0.5 hover:border-[color:var(--tm-brand-300)]">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19 8 12l7-7" />
                </svg>
                {{ $backLabel }}
            </a>
        @endif

        <p class="ui-eyebrow">
            <span class="ui-eyebrow-dot {{ $dotClass }}" aria-hidden="true"></span>
            {{ $eyebrow }}
        </p>
        <h1 class="ui-page-title">{{ $title }}</h1>

        @if ($description)
            <p class="ui-page-description">{{ $description }}</p>
        @endif
    </div>

    @if (trim((string) $slot) !== '')
        <div class="flex shrink-0 flex-wrap items-center gap-2">
            {{ $slot }}
        </div>
    @endif
</header>
