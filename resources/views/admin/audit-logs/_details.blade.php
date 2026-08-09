@php
    $hasChanges = $auditLog->before !== null || $auditLog->after !== null;
    $hasContext = $auditLog->context !== null;
@endphp

@if ($hasChanges || $hasContext)
    <div class="mt-4 flex flex-col gap-2">
        @if ($hasChanges)
            <details class="rounded-xl border border-[#dcebef] bg-[#f7fbfc] p-3">
                <summary class="ui-disclosure-summary flex items-center justify-between gap-3 text-xs font-extrabold text-[#45606a]">
                    <span>Lihat perubahan data</span>
                    <span class="font-normal text-[#8aa0a8]">Sebelum · sesudah</span>
                </summary>
                <div class="mt-3 grid gap-3 text-xs lg:grid-cols-2">
                    <div class="rounded-lg border border-[#e1eaed] bg-white p-3">
                        <p class="font-extrabold uppercase tracking-[0.1em] text-[#8aa0a8]">Sebelum</p>
                        <pre class="mt-2 max-h-64 overflow-auto whitespace-pre-wrap break-words font-mono text-[0.7rem] leading-5 text-[#526b75]">{{ $auditLog->before !== null ? $formatJson($auditLog->before) : 'Tidak ada nilai sebelumnya.' }}</pre>
                    </div>
                    <div class="rounded-lg border border-[#e1eaed] bg-white p-3">
                        <p class="font-extrabold uppercase tracking-[0.1em] text-[#8aa0a8]">Sesudah</p>
                        <pre class="mt-2 max-h-64 overflow-auto whitespace-pre-wrap break-words font-mono text-[0.7rem] leading-5 text-[#526b75]">{{ $auditLog->after !== null ? $formatJson($auditLog->after) : 'Tidak ada nilai sesudahnya.' }}</pre>
                    </div>
                </div>
            </details>
        @endif

        @if ($hasContext)
            <details class="rounded-xl border border-[#dcebef] bg-[#f7fbfc] p-3">
                <summary class="ui-disclosure-summary flex items-center justify-between gap-3 text-xs font-extrabold text-[#45606a]">
                    <span>Lihat konteks teknis</span>
                    <span class="font-normal text-[#8aa0a8]">Metadata permintaan</span>
                </summary>
                <p class="mt-3 text-xs leading-5 text-[#78909a]">Detail ini membantu penelusuran teknis dan tidak mengubah catatan audit.</p>
                <pre class="mt-3 max-h-64 overflow-auto whitespace-pre-wrap break-words rounded-lg border border-[#e1eaed] bg-white p-3 font-mono text-[0.7rem] leading-5 text-[#526b75]">{{ $formatJson($auditLog->context) }}</pre>
            </details>
        @endif
    </div>
@endif
