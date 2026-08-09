@props([
    'eyebrow',
    'title',
    'description' => null,
    'backUrl' => null,
    'backLabel' => 'Kembali',
    'dotClass' => '',
])

<header {{ $attributes->merge(['class' => 'ui-page-header']) }}>
    <div class="min-w-0">
        @if ($backUrl)
            <a href="{{ $backUrl }}" class="ui-action-link mb-4 inline-flex items-center gap-1.5">
                <span aria-hidden="true">←</span>
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
        <div class="flex shrink-0 flex-wrap gap-2">
            {{ $slot }}
        </div>
    @endif
</header>
