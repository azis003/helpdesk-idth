@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi halaman" class="flex flex-col gap-3 text-sm sm:flex-row sm:items-center sm:justify-between">
        <p class="text-slate-500">
            Menampilkan {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ $paginator->total() }} data
        </p>
        <div class="flex items-center gap-2">
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" class="rounded-lg border border-slate-200 px-3 py-2 text-slate-400">Sebelumnya</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="rounded-lg border border-slate-300 px-3 py-2 font-semibold text-slate-700 transition hover:border-slate-500 hover:text-slate-950 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:ring-offset-2">Sebelumnya</a>
            @endif

            <span class="px-2 text-slate-500">Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}</span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="rounded-lg border border-slate-300 px-3 py-2 font-semibold text-slate-700 transition hover:border-slate-500 hover:text-slate-950 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:ring-offset-2">Berikutnya</a>
            @else
                <span aria-disabled="true" class="rounded-lg border border-slate-200 px-3 py-2 text-slate-400">Berikutnya</span>
            @endif
        </div>
    </nav>
@endif
