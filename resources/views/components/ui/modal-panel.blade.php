@props([
    'id',
    'labelledby',
    'autoOpen' => false,
    'maxWidth' => 'max-w-2xl',
])

<div
    id="{{ $id }}"
    data-ui-modal
    data-auto-open="{{ $autoOpen ? 'true' : 'false' }}"
    class="fixed inset-0 z-50 hidden"
    aria-hidden="true"
>
    <button type="button" class="absolute inset-0 cursor-default bg-slate-950/50 backdrop-blur-[3px]" data-ui-modal-close tabindex="-1" aria-label="Tutup dialog"></button>

    <div class="relative flex min-h-full items-center justify-center p-4 sm:p-6">
        <div role="dialog" aria-modal="true" aria-labelledby="{{ $labelledby }}" class="relative max-h-[calc(100dvh-2rem)] w-full {{ $maxWidth }} overflow-y-auto overscroll-contain rounded-[var(--tm-r-xl)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface)] shadow-[var(--tm-sh-xl)] sm:max-h-[calc(100dvh-4rem)]">
            <button type="button" data-ui-modal-close class="absolute right-3 top-3 z-20 inline-flex h-9 w-9 items-center justify-center rounded-[var(--tm-r-sm)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface)] text-[color:var(--tm-text-muted)] shadow-[var(--tm-sh-xs)] transition hover:bg-[color:var(--tm-n-50)] hover:text-[color:var(--tm-text)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[color:var(--tm-ring-color)]" aria-label="Tutup dialog">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" d="m7 7 10 10M17 7 7 17" />
                </svg>
            </button>

            {{ $slot }}
        </div>
    </div>
</div>
