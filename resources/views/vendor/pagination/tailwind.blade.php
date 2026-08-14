@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi halaman" class="flex flex-col gap-3 text-sm sm:flex-row sm:items-center sm:justify-between">
        <p class="text-[color:var(--tm-text-muted)]">
            Menampilkan {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ $paginator->total() }} data
        </p>
        <div class="flex items-center gap-2">
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" class="rounded-lg border border-[color:var(--tm-border-subtle)] px-3 py-2 text-[color:var(--tm-text-muted)] bg-[color:var(--tm-sunken)]/50 select-none">Sebelumnya</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="rounded-lg border border-[color:var(--tm-border-strong)] px-3 py-2 font-semibold text-[color:var(--tm-text-secondary)] transition hover:border-[color:var(--tm-text)] hover:text-[color:var(--tm-text)] focus:outline-none focus:ring-2 focus:ring-[color:var(--tm-brand-500)] focus:ring-offset-2">Sebelumnya</a>
            @endif

            <span class="px-2 text-[color:var(--tm-text-muted)]">Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}</span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="rounded-lg border border-[color:var(--tm-border-strong)] px-3 py-2 font-semibold text-[color:var(--tm-text-secondary)] transition hover:border-[color:var(--tm-text)] hover:text-[color:var(--tm-text)] focus:outline-none focus:ring-2 focus:ring-[color:var(--tm-brand-500)] focus:ring-offset-2">Berikutnya</a>
            @else
                <span aria-disabled="true" class="rounded-lg border border-[color:var(--tm-border-subtle)] px-3 py-2 text-[color:var(--tm-text-muted)] bg-[color:var(--tm-sunken)]/50 select-none">Berikutnya</span>
            @endif
        </div>
    </nav>
@endif
