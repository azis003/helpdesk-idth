@php
    $previewFields = $serviceType->activeFieldDefinitions
        ->filter(fn ($field) => in_array($field->visibility, ['requester', 'both'], true))
        ->values();
    $internalFieldCount = $serviceType->activeFieldDefinitions
        ->filter(fn ($field) => $field->visibility === 'internal')
        ->count();
    $sharedFieldCount = $serviceType->activeFieldDefinitions
        ->filter(fn ($field) => $field->visibility === 'both')
        ->count();
@endphp

<aside class="rounded-2xl border border-[#dfe8ec] bg-[#f6fafb] lg:sticky lg:top-20 lg:self-start" aria-labelledby="form-preview-heading-{{ $serviceType->id }}">
    <div class="border-b border-[#dfe8ec] px-4 py-4 sm:px-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-[0.65rem] font-extrabold uppercase tracking-[0.14em] text-[#7a929a]">Pratinjau versi aktif</p>
                <h3 id="form-preview-heading-{{ $serviceType->id }}" class="mt-1 text-sm font-extrabold text-[#17313c]">Formulir pemohon</h3>
            </div>
            <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full border border-[#b9e8e1] bg-[#ecfbf8] px-2.5 py-1 text-[0.65rem] font-extrabold text-[#0f6862]">
                <span class="h-1.5 w-1.5 rounded-full bg-[#2bb8aa]" aria-hidden="true"></span>
                Versi aktif
            </span>
        </div>
        <p class="mt-2 text-xs leading-5 text-[#78909a]">Gambaran field yang akan dilihat pemohon saat membuat tiket baru.</p>
    </div>

    <div class="space-y-4 p-4 sm:p-5">
        <div class="rounded-xl border border-[#d9e8ec] bg-white p-3.5">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-[0.65rem] font-extrabold uppercase tracking-[0.12em] text-[#8aa0a8]">Layanan</p>
                    <p class="mt-1 truncate text-sm font-extrabold text-[#263a43]">{{ $serviceType->name }}</p>
                </div>
                <span class="shrink-0 rounded-lg bg-[#eef8fc] px-2 py-1 text-[0.65rem] font-extrabold text-[#26677b]">{{ $serviceType->code }}</span>
            </div>
            <div class="mt-3 flex flex-wrap gap-1.5 text-[0.65rem] font-bold text-[#607681]">
                <span class="rounded-full border border-[#dfe8ec] bg-[#f7fafb] px-2 py-1">Kelas {{ $serviceType->ticket_class ?? 'belum diatur' }}</span>
            </div>
        </div>

        @if ($previewFields->isEmpty())
            <div class="rounded-xl border border-dashed border-[#bfced4] bg-white px-4 py-8 text-center">
                <svg class="mx-auto h-8 w-8 text-[#aebfc5]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7 3.75h7.5L19 8.25v12H7a2 2 0 0 1-2-2v-12a2 2 0 0 1 2-2Z" /><path stroke-linecap="round" d="M14 3.75v4.5h4.5M8.5 12h7M8.5 15.5h5" /></svg>
                <p class="mt-3 text-xs font-extrabold text-[#526f79]">Belum ada field untuk pemohon</p>
                <p class="mt-1 text-xs leading-5 text-[#78909a]">Gunakan Edit layanan untuk menambah field dan menyusun formulirnya.</p>
            </div>
        @else
            <div class="rounded-xl border border-[#dfe8ec] bg-white p-4">
                <div class="flex items-center justify-between gap-3 border-b border-[#edf2f4] pb-3">
                    <p class="text-xs font-extrabold text-[#35505b]">Informasi layanan</p>
                    <span class="text-[0.65rem] font-bold text-[#8aa0a8]">{{ $previewFields->count() }} field</span>
                </div>

                <ol class="mt-4 space-y-4">
                    @foreach ($previewFields as $field)
                        @php($fieldTypeLabel = $fieldTypes[$field->field_type] ?? $field->field_type)
                        <li>
                            <div class="flex items-start justify-between gap-3">
                                <p class="text-xs font-bold leading-5 text-[#35505b]">
                                    {{ $field->label }}
                                    @if ($field->is_required)
                                        <span class="text-rose-600" aria-hidden="true">*</span><span class="sr-only">, wajib diisi</span>
                                    @endif
                                </p>
                                <span class="shrink-0 text-[0.6rem] font-bold text-[#9aabb0]">{{ $fieldTypeLabel }}</span>
                            </div>

                            @switch($field->field_type)
                                @case('textarea')
                                    <div class="mt-1.5 h-16 rounded-lg border border-[#dfe8ec] bg-[#fbfdfd] px-3 py-2 text-[0.68rem] text-[#a0b0b5]">Tulis jawaban di sini...</div>
                                    @break
                                @case('select')
                                    <div class="mt-1.5 flex min-h-10 items-center justify-between gap-2 rounded-lg border border-[#dfe8ec] bg-[#fbfdfd] px-3 py-2 text-[0.68rem] text-[#a0b0b5]"><span>Pilih {{ strtolower($field->label) }}</span><span class="text-[#78909a]" aria-hidden="true">⌄</span></div>
                                    @if ($field->options->isNotEmpty())
                                        <div class="mt-1.5 flex flex-wrap gap-1">
                                            @foreach ($field->options->take(3) as $option)
                                                <span class="rounded-full bg-[#f3f7f8] px-2 py-1 text-[0.6rem] font-bold text-[#78909a]">{{ $option->label }}</span>
                                            @endforeach
                                            @if ($field->options->count() > 3)
                                                <span class="rounded-full bg-[#f3f7f8] px-2 py-1 text-[0.6rem] font-bold text-[#78909a]">+{{ $field->options->count() - 3 }} lainnya</span>
                                            @endif
                                        </div>
                                    @endif
                                    @break
                                @case('multiselect')
                                    <div class="mt-1.5 flex min-h-10 flex-wrap items-center gap-1 rounded-lg border border-[#dfe8ec] bg-[#fbfdfd] px-2.5 py-2">
                                        @forelse ($field->options->take(2) as $option)
                                            <span class="rounded-md bg-[#e8f7fb] px-2 py-1 text-[0.6rem] font-bold text-[#26677b]">{{ $option->label }}</span>
                                        @empty
                                            <span class="text-[0.68rem] text-[#a0b0b5]">Pilih satu atau beberapa opsi...</span>
                                        @endforelse
                                    </div>
                                    @break
                                @case('boolean')
                                    <div class="mt-1.5 flex min-h-10 items-center gap-2 rounded-lg border border-[#dfe8ec] bg-[#fbfdfd] px-3 py-2 text-[0.68rem] text-[#78909a]"><span class="h-4 w-4 rounded border border-[#b9cbd1] bg-white" aria-hidden="true"></span>Ya / Tidak</div>
                                    @break
                                @case('date')
                                @case('datetime')
                                    <div class="mt-1.5 flex min-h-10 items-center justify-between gap-2 rounded-lg border border-[#dfe8ec] bg-[#fbfdfd] px-3 py-2 text-[0.68rem] text-[#a0b0b5]"><span>{{ $field->field_type === 'datetime' ? 'Pilih tanggal dan waktu...' : 'Pilih tanggal...' }}</span><span class="text-[#78909a]" aria-hidden="true">▣</span></div>
                                    @break
                                @default
                                    <div class="mt-1.5 flex min-h-10 items-center rounded-lg border border-[#dfe8ec] bg-[#fbfdfd] px-3 py-2 text-[0.68rem] text-[#a0b0b5]">{{ $field->field_type === 'number' ? 'Masukkan angka...' : 'Masukkan jawaban...' }}</div>
                            @endswitch

                            @if ($field->help_text)
                                <p class="mt-1 text-[0.65rem] leading-4 text-[#8aa0a8]">{{ $field->help_text }}</p>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </div>
        @endif

        <div class="rounded-xl border border-[#d9e8ec] bg-[#f8fbfc] px-3.5 py-3">
            <p class="text-[0.68rem] font-extrabold text-[#526f79]">Catatan visibilitas</p>
            <p class="mt-1 text-[0.68rem] leading-5 text-[#78909a]">
                @if ($internalFieldCount > 0 && $sharedFieldCount > 0)
                    {{ $internalFieldCount }} field hanya untuk Tim TI; {{ $sharedFieldCount }} field dapat dilihat pemohon dan Tim TI.
                @elseif ($internalFieldCount > 0)
                    {{ $internalFieldCount }} field hanya untuk Tim TI dan tidak tampil pada formulir pemohon.
                @elseif ($sharedFieldCount > 0)
                    {{ $sharedFieldCount }} field dapat dilihat oleh pemohon dan Tim TI.
                @else
                    Semua field aktif pada layanan ini dapat diisi oleh pemohon.
                @endif
            </p>
        </div>
    </div>
</aside>
