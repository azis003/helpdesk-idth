@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi halaman" class="ui-pagination">
        <p class="ui-pagination-summary">
            Menampilkan {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ $paginator->total() }} data
        </p>
        <div class="ui-pagination-controls">
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" class="ui-pagination-control is-disabled">Sebelumnya</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="ui-pagination-control">Sebelumnya</a>
            @endif

            <span class="ui-pagination-status">Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}</span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="ui-pagination-control">Berikutnya</a>
            @else
                <span aria-disabled="true" class="ui-pagination-control is-disabled">Berikutnya</span>
            @endif
        </div>
    </nav>
@endif
