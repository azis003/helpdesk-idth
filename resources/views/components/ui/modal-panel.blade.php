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
    <button type="button" class="absolute inset-0 cursor-default bg-slate-950/45" data-ui-modal-close tabindex="-1" aria-label="Tutup dialog"></button>

    <div class="relative flex min-h-full items-center justify-center p-4 sm:p-6">
        <div role="dialog" aria-modal="true" aria-labelledby="{{ $labelledby }}" class="relative max-h-[calc(100dvh-2rem)] w-full {{ $maxWidth }} overflow-y-auto overscroll-contain rounded-xl bg-white shadow-[0_20px_55px_rgba(38,58,67,0.2)] sm:max-h-[calc(100dvh-4rem)]">
            <button type="button" data-ui-modal-close class="absolute right-3 top-3 z-20 inline-flex h-9 w-9 items-center justify-center rounded-lg bg-white text-[#526f79] shadow-sm transition hover:bg-[#f4f8f9] hover:text-[#263a43] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#147a79]" aria-label="Tutup dialog">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" d="m7 7 10 10M17 7 7 17" />
                </svg>
            </button>

            {{ $slot }}
        </div>
    </div>
</div>
