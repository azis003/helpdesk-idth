@extends('layouts.app')

@php
    $autoOpenForm = old('_skill_form');

    if (! $autoOpenForm && old('_skill_create')) {
        $autoOpenForm = 'create';
    }

    if (! $autoOpenForm && old('_skill_edit')) {
        $autoOpenForm = 'edit-'.old('_skill_edit');
    }

    $skillEmptyTitle = $search !== '' ? 'Tidak ada keahlian yang cocok' : 'Belum ada keahlian';
    $skillEmptyDescription = $search !== '' ? 'Coba kata kunci lain atau kosongkan kolom pencarian.' : 'Tambahkan bidang keahlian untuk membantu triase dan penugasan tiket.';
@endphp

@section('title', 'Keahlian — '.$branding['application_name'])
@section('header_kicker', 'Manajemen Keahlian')
@section('header_title', 'Keahlian')

@section('content')
    <x-page-header
        eyebrow="Data Master · Kapabilitas"
        title="Manajemen Keahlian"
        description="Kelola bidang keahlian dan pemetaannya untuk membantu triase serta penugasan tiket."
    />

    <section class="ui-panel mt-6 overflow-hidden" aria-labelledby="skills-heading">
        <div class="flex flex-col gap-4 border-b border-[color:var(--tm-border-subtle)] px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div class="min-w-0">
                <h2 id="skills-heading" class="text-base font-bold tracking-tight text-[color:var(--tm-text)]">Daftar Keahlian</h2>
                <p class="mt-1 text-sm leading-5 text-[color:var(--tm-text-muted)]">Gunakan pemetaan ini untuk membantu triase dan penugasan.</p>
            </div>
            <button type="button" data-ui-modal-open="skill-create-modal" class="ui-btn ui-btn-primary shrink-0" aria-label="Tambah keahlian">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
                Tambah keahlian
            </button>
        </div>

        <form method="GET" action="{{ route('admin.skills.index') }}" class="flex flex-col gap-4 border-b border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-sunken)] px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div class="flex items-center gap-2 text-sm text-[color:var(--tm-text-secondary)]">
                <label for="skill-per-page" class="font-bold">Tampilkan</label>
                <select id="skill-per-page" name="per_page" class="ui-select w-auto tabular-nums" onchange="this.form.submit()">
                    @foreach ([10, 25, 50] as $pageSize)
                        <option value="{{ $pageSize }}" @selected($perPage === $pageSize)>{{ $pageSize }}</option>
                    @endforeach
                </select>
                <span>data</span>
            </div>

            <div class="flex w-full items-center gap-2 sm:w-auto">
                <label for="skill-search" class="shrink-0 text-sm font-bold text-[color:var(--tm-text-secondary)]">Cari:</label>
                <input id="skill-search" name="q" type="search" value="{{ $search }}" class="ui-input min-w-0 sm:w-56" placeholder="Cari keahlian" aria-label="Cari keahlian">
                <button type="submit" class="ui-btn ui-btn-secondary shrink-0">Cari</button>
            </div>
        </form>

        <div class="p-5 sm:p-6">
            <div class="space-y-4">
                @forelse ($skills as $skill)
                    <article class="rounded-[var(--tm-r-lg)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface)] p-5 transition-shadow duration-[var(--tm-dur-fast)] hover:shadow-[var(--tm-sh-sm)] sm:p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold uppercase tracking-wide tabular-nums text-[color:var(--tm-text-faint)]">No. {{ ($skills->firstItem() ?? 1) + $loop->index }}</span>
                                    <span class="sr-only">Nama Keahlian:</span>
                                </div>
                                <h3 class="mt-1 text-base font-bold text-[color:var(--tm-text)] sm:text-lg">{{ $skill->name }}</h3>
                                @if ($skill->description)
                                    <p class="mt-1 text-xs leading-relaxed text-[color:var(--tm-text-secondary)] sm:text-sm">{{ $skill->description }}</p>
                                @else
                                    <p class="mt-1 text-xs italic text-[color:var(--tm-text-faint)]">Tidak ada deskripsi.</p>
                                @endif
                            </div>
                            <span class="ui-status {{ $skill->is_active ? 'ui-status-active' : 'ui-status-inactive' }} shrink-0">{{ $skill->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        </div>

                        <!-- Mapped Services Section -->
                        <div class="mt-4 border-t border-[color:var(--tm-border-subtle)] pt-3.5">
                            <h4 class="text-[0.68rem] font-bold uppercase tracking-wider text-[color:var(--tm-text-muted)]">Layanan Terpetakan</h4>
                            @if ($skill->serviceTypes->isNotEmpty())
                                <div class="mt-2 flex flex-wrap gap-1.5">
                                    @foreach ($skill->serviceTypes as $serviceType)
                                        <span class="inline-flex items-center gap-1.5 rounded-[var(--tm-r-full)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-sunken)] px-2.5 py-1 text-xs text-[color:var(--tm-text-secondary)]">
                                            <span class="font-bold text-[color:var(--tm-brand-700)]">{{ $serviceType->code }}</span>
                                            <span>{{ $serviceType->name }}</span>
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <p class="mt-1.5 text-xs italic text-[color:var(--tm-text-faint)]">Belum ada layanan terpetakan</p>
                            @endif
                        </div>

                        <!-- Actions Section -->
                        <div class="mt-5 flex flex-wrap items-center justify-end gap-2 border-t border-[color:var(--tm-border-subtle)] pt-4">
                            <button type="button" data-ui-modal-open="skill-edit-modal-{{ $skill->id }}" class="ui-btn ui-btn-secondary !min-h-9 !px-3 !text-xs font-semibold" aria-label="Edit keahlian {{ $skill->name }}" title="Edit keahlian">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 2.651 2.651M4.5 19.5l4.04-.808a2 2 0 0 0 1.02-.55l8.95-8.95a2 2 0 0 0 0-2.828l-.884-.884a2 2 0 0 0-2.828 0l-8.95 8.95a2 2 0 0 0-.55 1.02L4.5 19.5Z" /></svg>
                                <span>Edit</span>
                            </button>

                            @if ($skill->is_active)
                                <form method="POST" action="{{ route('admin.skills.deactivate', $skill) }}" data-swal-confirm="Nonaktifkan keahlian {{ $skill->name }}? Pemetaan dan histori yang ada tetap disimpan.">
                                    @csrf
                                    <button type="submit" class="ui-btn ui-btn-ghost !min-h-9 !px-3 !text-xs font-semibold text-[color:var(--tm-warning-700)] hover:bg-[color:var(--tm-warning-50)]" aria-label="Nonaktifkan keahlian {{ $skill->name }}" title="Nonaktifkan keahlian">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M12 4v8M7.2 6.4a7 7 0 1 0 9.6 0" /></svg>
                                        <span>Nonaktifkan</span>
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.skills.activate', $skill) }}">
                                    @csrf
                                    <button type="submit" class="ui-btn ui-btn-ghost !min-h-9 !px-3 !text-xs font-semibold text-[color:var(--tm-success-700)] hover:bg-[color:var(--tm-success-50)]" aria-label="Aktifkan keahlian {{ $skill->name }}" title="Aktifkan keahlian">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg>
                                        <span>Aktifkan</span>
                                    </button>
                                </form>
                            @endif

                            <form method="POST" action="{{ route('admin.skills.destroy', $skill) }}" data-swal-confirm="Hapus keahlian {{ $skill->name }} secara lunak? Histori pemetaan tetap tersedia.">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="ui-btn ui-btn-ghost !min-h-9 !px-3 !text-xs font-semibold text-[color:var(--tm-danger-600)] hover:bg-[color:var(--tm-danger-50)]" aria-label="Hapus keahlian {{ $skill->name }}" title="Hapus keahlian">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3" /></svg>
                                    <span>Hapus</span>
                                </button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="rounded-[var(--tm-r-lg)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface)] p-8">
                        <x-empty-state :title="$skillEmptyTitle" :description="$skillEmptyDescription" />
                    </div>
                @endforelse
            </div>
        </div>

        <div class="border-t border-[color:var(--tm-border-subtle)] px-5 py-4 sm:px-6">
            <div class="flex flex-col gap-3 text-sm text-[color:var(--tm-text-muted)] sm:flex-row sm:items-center sm:justify-between">
                <p>
                    @if ($skills->total() > 0)
                        Menampilkan <span class="font-semibold tabular-nums text-[color:var(--tm-text-secondary)]">{{ $skills->firstItem() }}–{{ $skills->lastItem() }}</span> dari <span class="font-semibold tabular-nums text-[color:var(--tm-text-secondary)]">{{ $skills->total() }}</span> keahlian
                    @else
                        Tidak ada data keahlian
                    @endif
                </p>
                @if ($skills->hasPages())
                    <div>{{ $skills->links() }}</div>
                @endif
            </div>
        </div>
    </section>

    <!-- Create Skill Modal -->
    <div id="skill-create-modal" data-ui-modal data-auto-open="{{ $autoOpenForm === 'create' && $errors->any() ? 'true' : 'false' }}" data-reset-on-close="true" data-clear-on-close="true" class="fixed inset-0 z-50 hidden" aria-hidden="true">
        <div class="absolute inset-0 bg-slate-950/50 backdrop-blur-[3px]" data-ui-modal-close></div>
        <div class="relative flex min-h-full items-start justify-center overflow-y-auto p-4 sm:items-center sm:p-8">
            <section role="dialog" aria-modal="true" aria-labelledby="skill-create-title" class="relative max-h-[calc(100vh-2rem)] w-full max-w-2xl overflow-y-auto rounded-[var(--tm-r-lg)] border border-[color:var(--tm-border)] bg-[color:var(--tm-surface)] shadow-[var(--tm-sh-xl)]">
                <div class="sticky top-0 z-10 flex items-center justify-between gap-4 border-b border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface)] px-5 py-4 sm:px-6">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-[var(--tm-r-sm)] bg-[color:var(--tm-brand-50)] text-[color:var(--tm-brand-600)]" aria-hidden="true">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
                        </span>
                        <h2 id="skill-create-title" class="truncate text-base font-bold text-[color:var(--tm-text)]">Tambah Keahlian</h2>
                    </div>
                    <button type="button" data-ui-modal-close class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-[var(--tm-r-sm)] text-[color:var(--tm-text-muted)] transition-colors duration-[var(--tm-dur-fast)] hover:bg-[color:var(--tm-n-100)] hover:text-[color:var(--tm-text)]" aria-label="Tutup dialog tambah keahlian">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" d="m7 7 10 10M17 7 7 17" /></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.skills.store') }}" data-ui-modal-form class="p-5 sm:p-6">
                    @csrf
                    <input type="hidden" name="_skill_form" value="create">
                    <div>
                        <label for="skill-create-name" class="ui-field-label">Nama Keahlian <span class="text-[color:var(--tm-danger-600)]" aria-hidden="true">*</span><span class="sr-only"> wajib</span></label>
                        <input id="skill-create-name" data-ui-modal-focus name="name" type="text" value="{{ old('name') }}" autocomplete="off" required class="ui-input mt-2" placeholder="Contoh: Jaringan" @error('name') aria-invalid="true" aria-describedby="skill-create-name-error" @enderror>
                        @error('name')<x-field-error id="skill-create-name-error" data-ui-validation-error :message="$message" />@enderror
                    </div>
                    <div class="mt-4">
                        <label for="skill-create-description" class="ui-field-label">Deskripsi <span class="font-normal text-[color:var(--tm-text-faint)]">(opsional)</span></label>
                        <textarea id="skill-create-description" name="description" rows="3" class="ui-textarea mt-2" placeholder="Cakupan keahlian" @error('description') aria-invalid="true" aria-describedby="skill-create-description-error" @enderror>{{ old('description') }}</textarea>
                        @error('description')<x-field-error id="skill-create-description-error" data-ui-validation-error :message="$message" />@enderror
                    </div>
                    <div class="mt-5 flex flex-col-reverse gap-2 border-t border-[color:var(--tm-border-subtle)] pt-4 sm:flex-row sm:justify-end">
                        <button type="button" data-ui-modal-close class="ui-btn ui-btn-ghost">Tutup</button>
                        <button type="submit" data-ui-modal-submit class="ui-btn ui-btn-primary">
                            <span data-ui-modal-label>Simpan keahlian</span>
                            <span data-ui-modal-loading class="hidden">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>

    <!-- Edit Skill Modals -->
    @foreach ($skills as $skill)
        @php
            $isEditingSkill = $autoOpenForm === 'edit-'.$skill->id && $errors->any();
        @endphp
        <div id="skill-edit-modal-{{ $skill->id }}" data-ui-modal data-auto-open="{{ $isEditingSkill ? 'true' : 'false' }}" class="fixed inset-0 z-50 hidden" aria-hidden="true">
            <div class="absolute inset-0 bg-slate-950/50 backdrop-blur-[3px]" data-ui-modal-close></div>
            <div class="relative flex min-h-full items-start justify-center overflow-y-auto p-4 sm:items-center sm:p-8">
                <section role="dialog" aria-modal="true" aria-labelledby="skill-edit-title-{{ $skill->id }}" class="relative max-h-[calc(100vh-2rem)] w-full max-w-2xl overflow-y-auto rounded-[var(--tm-r-lg)] border border-[color:var(--tm-border)] bg-[color:var(--tm-surface)] shadow-[var(--tm-sh-xl)]">
                    <div class="sticky top-0 z-10 flex items-center justify-between gap-4 border-b border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface)] px-5 py-4 sm:px-6">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-[var(--tm-r-sm)] bg-[color:var(--tm-brand-50)] text-[color:var(--tm-brand-600)]" aria-hidden="true">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 2.651 2.651M4.5 19.5l4.04-.808a2 2 0 0 0 1.02-.55l8.95-8.95a2 2 0 0 0 0-2.828l-.884-.884a2 2 0 0 0-2.828 0l-8.95 8.95a2 2 0 0 0-.55 1.02L4.5 19.5Z" /></svg>
                            </span>
                            <h2 id="skill-edit-title-{{ $skill->id }}" class="truncate text-base font-bold text-[color:var(--tm-text)]">Edit Keahlian</h2>
                        </div>
                        <button type="button" data-ui-modal-close class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-[var(--tm-r-sm)] text-[color:var(--tm-text-muted)] transition-colors duration-[var(--tm-dur-fast)] hover:bg-[color:var(--tm-n-100)] hover:text-[color:var(--tm-text)]" aria-label="Tutup dialog edit keahlian {{ $skill->name }}">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" d="m7 7 10 10M17 7 7 17" /></svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('admin.skills.update', $skill) }}" data-ui-modal-form class="p-5 sm:p-6">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_skill_form" value="edit-{{ $skill->id }}">
                        <input type="hidden" name="_skill_edit" value="{{ $skill->id }}">
                        <div>
                            <label for="skill-edit-name-{{ $skill->id }}" class="ui-field-label">Nama Keahlian <span class="text-[color:var(--tm-danger-600)]" aria-hidden="true">*</span><span class="sr-only"> wajib</span></label>
                            <input id="skill-edit-name-{{ $skill->id }}" data-ui-modal-focus name="name" type="text" value="{{ $isEditingSkill ? old('name', $skill->name) : $skill->name }}" autocomplete="off" required class="ui-input mt-2" @error('name') aria-invalid="true" aria-describedby="skill-edit-name-error-{{ $skill->id }}" @enderror>
                            @error('name')<x-field-error id="skill-edit-name-error-{{ $skill->id }}" data-ui-validation-error :message="$message" />@enderror
                        </div>
                        <div class="mt-4">
                            <label for="skill-edit-description-{{ $skill->id }}" class="ui-field-label">Deskripsi <span class="font-normal text-[color:var(--tm-text-faint)]">(opsional)</span></label>
                            <textarea id="skill-edit-description-{{ $skill->id }}" name="description" rows="3" class="ui-textarea mt-2" @error('description') aria-invalid="true" aria-describedby="skill-edit-description-error-{{ $skill->id }}" @enderror>{{ $isEditingSkill ? old('description', $skill->description) : $skill->description }}</textarea>
                            @error('description')<x-field-error id="skill-edit-description-error-{{ $skill->id }}" data-ui-validation-error :message="$message" />@enderror
                        </div>
                        <div class="mt-5 flex flex-col-reverse gap-2 border-t border-[color:var(--tm-border-subtle)] pt-4 sm:flex-row sm:justify-end">
                            <button type="button" data-ui-modal-close class="ui-btn ui-btn-ghost">Tutup</button>
                            <button type="submit" data-ui-modal-submit class="ui-btn ui-btn-primary">
                                <span data-ui-modal-label>Simpan perubahan</span>
                                <span data-ui-modal-loading class="hidden">Menyimpan...</span>
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    @endforeach
@endsection
