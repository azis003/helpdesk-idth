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
    <button type="button" class="ui-modal-backdrop" data-ui-modal-close tabindex="-1" aria-label="Tutup dialog"></button>

    <div class="relative flex min-h-full items-center justify-center p-4 sm:p-6">
        <div role="dialog" aria-modal="true" aria-labelledby="{{ $labelledby }}" class="ui-modal-dialog relative max-h-[calc(100dvh-2rem)] w-full {{ $maxWidth }} overflow-y-auto overscroll-contain sm:max-h-[calc(100dvh-4rem)]">
            <button type="button" data-ui-modal-close class="ui-modal-close" aria-label="Tutup dialog">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" d="m7 7 10 10M17 7 7 17" />
                </svg>
            </button>

            {{ $slot }}
        </div>
    </div>
</div>
