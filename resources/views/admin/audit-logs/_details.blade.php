@php
    $hasChanges = $auditLog->before !== null || $auditLog->after !== null;
    $hasContext = $auditLog->context !== null;

    // Presentasional saja - tidak mengubah data maupun logika audit.
    $auditDetailsCard = 'rounded-[var(--tm-r-md)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-sunken)] p-3';
    $auditDetailsSummary = 'ui-disclosure-summary flex items-center justify-between gap-3 text-xs font-extrabold text-[color:var(--tm-text-secondary)]';
    $auditDetailsHint = 'font-normal text-[color:var(--tm-text-faint)]';
    $auditDetailsPane = 'rounded-[var(--tm-r-sm)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface)] p-3';
    $auditDetailsPaneLabel = 'text-[0.7rem] font-extrabold uppercase tracking-[0.1em] text-[color:var(--tm-text-faint)]';
    $auditDetailsPre = 'mt-2 max-h-64 overflow-auto whitespace-pre-wrap break-words font-mono text-[0.7rem] leading-5 text-[color:var(--tm-text-secondary)]';
@endphp

@if ($hasChanges || $hasContext)
    <div class="mt-4 flex flex-col gap-2">
        @if ($hasChanges)
            <details class="{{ $auditDetailsCard }}">
                <summary class="{{ $auditDetailsSummary }}">
                    <span>Lihat perubahan data</span>
                    <span class="{{ $auditDetailsHint }}">Sebelum · sesudah</span>
                </summary>
                <div class="mt-3 grid gap-3 text-xs lg:grid-cols-2">
                    <div class="{{ $auditDetailsPane }}">
                        <p class="{{ $auditDetailsPaneLabel }}">Sebelum</p>
                        <pre class="{{ $auditDetailsPre }}">{{ $auditLog->before !== null ? $formatJson($auditLog->before) : 'Tidak ada nilai sebelumnya.' }}</pre>
                    </div>
                    <div class="{{ $auditDetailsPane }}">
                        <p class="{{ $auditDetailsPaneLabel }}">Sesudah</p>
                        <pre class="{{ $auditDetailsPre }}">{{ $auditLog->after !== null ? $formatJson($auditLog->after) : 'Tidak ada nilai sesudahnya.' }}</pre>
                    </div>
                </div>
            </details>
        @endif

        @if ($hasContext)
            <details class="{{ $auditDetailsCard }}">
                <summary class="{{ $auditDetailsSummary }}">
                    <span>Lihat konteks teknis</span>
                    <span class="{{ $auditDetailsHint }}">Metadata permintaan</span>
                </summary>
                <p class="mt-3 text-xs leading-5 text-[color:var(--tm-text-muted)]">Detail ini membantu penelusuran teknis dan tidak mengubah catatan audit.</p>
                <pre class="mt-3 max-h-64 overflow-auto whitespace-pre-wrap break-words rounded-[var(--tm-r-sm)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface)] p-3 font-mono text-[0.7rem] leading-5 text-[color:var(--tm-text-secondary)]">{{ $formatJson($auditLog->context) }}</pre>
            </details>
        @endif
    </div>
@endif
