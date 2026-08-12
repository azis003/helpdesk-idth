@extends('layouts.app')

@php
    $autoOpenForm = old('_skill_form');

    if (! $autoOpenForm && old('_skill_create')) {
        $autoOpenForm = 'create';
    }

    if (! $autoOpenForm && old('_skill_edit')) {
        $autoOpenForm = 'edit-'.old('_skill_edit');
    }
@endphp

@php
    // Presentasional saja: kelas tombol aksi supaya tidak diulang di desktop & mobile.
    $skillActionBase = 'inline-flex h-9 w-9 items-center justify-center rounded-[var(--tm-r-sm)] border transition-colors duration-[var(--tm-dur-fast)]';
    $skillActionEdit = $skillActionBase.' border-[color:var(--tm-border)] bg-[color:var(--tm-surface)] text-[color:var(--tm-text-secondary)] hover:border-[color:var(--tm-brand-300)] hover:bg-[color:var(--tm-brand-50)] hover:text-[color:var(--tm-brand-700)]';
    $skillActionWarning = $skillActionBase.' border-[color:var(--tm-warning-200)] bg-[color:var(--tm-warning-50)] text-[color:var(--tm-warning-700)] hover:bg-[color:var(--tm-warning-100)]';
    $skillActionSuccess = $skillActionBase.' border-[color:var(--tm-success-200)] bg-[color:var(--tm-success-50)] text-[color:var(--tm-success-700)] hover:bg-[color:var(--tm-success-100)]';
    $skillActionDanger = $skillActionBase.' border-[color:var(--tm-danger-200)] bg-[color:var(--tm-danger-50)] text-[color:var(--tm-danger-700)] hover:bg-[color:var(--tm-danger-100)]';
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

        <div class="px-5 py-5 sm:px-6">
            <div class="hidden overflow-x-auto rounded-[var(--tm-r-md)] border border-[color:var(--tm-border-subtle)] md:block">
                <table class="ui-table w-full min-w-[52rem]">
                    <caption class="sr-only">Daftar keahlian beserta layanan terpetakan, status, dan aksi</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="w-14 text-center">No</th>
                            <th scope="col">Nama Keahlian</th>
                            <th scope="col">Layanan Terpetakan</th>
                            <th scope="col" class="w-28 text-center">Status</th>
                            <th scope="col" class="w-36 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($skills as $skill)
                            <tr>
                                <td class="text-center font-semibold tabular-nums text-[color:var(--tm-text-muted)]">{{ ($skills->firstItem() ?? 1) + $loop->index }}</td>
                                <td>
                                    <p class="font-semibold text-[color:var(--tm-text)]">{{ $skill->name }}</p>
                                    @if ($skill->description)
                                        <p class="mt-1 max-w-[24rem] truncate text-xs text-[color:var(--tm-text-faint)]">{{ $skill->description }}</p>
                                    @endif
                                </td>
                                <td>
                                    @if ($skill->serviceTypes->isNotEmpty())
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach ($skill->serviceTypes as $serviceType)
                                                <span class="inline-flex items-center gap-1.5 rounded-[var(--tm-r-full)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-sunken)] px-2.5 py-1 text-xs text-[color:var(--tm-text-secondary)]">
                                                    <span class="font-bold text-[color:var(--tm-brand-700)]">{{ $serviceType->code }}</span>
                                                    {{ $serviceType->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-[color:var(--tm-text-faint)]">Belum ada layanan</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="ui-status {{ $skill->is_active ? 'ui-status-active' : 'ui-status-inactive' }}">{{ $skill->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                </td>
                                <td>
                                    <div class="flex justify-center gap-2">
                                        <button type="button" data-ui-modal-open="skill-edit-modal-{{ $skill->id }}" class="{{ $skillActionEdit }}" aria-label="Edit keahlian {{ $skill->name }}" title="Edit keahlian">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 2.651 2.651M4.5 19.5l4.04-.808a2 2 0 0 0 1.02-.55l8.95-8.95a2 2 0 0 0 0-2.828l-.884-.884a2 2 0 0 0-2.828 0l-8.95 8.95a2 2 0 0 0-.55 1.02L4.5 19.5Z" /></svg>
                                        </button>
                                        @if ($skill->is_active)
                                            <form method="POST" action="{{ route('admin.skills.deactivate', $skill) }}" data-swal-confirm="Nonaktifkan keahlian {{ $skill->name }}? Pemetaan dan histori yang ada tetap disimpan." class="flex">
                                                @csrf
                                                <button type="submit" class="{{ $skillActionWarning }}" aria-label="Nonaktifkan keahlian {{ $skill->name }}" title="Nonaktifkan keahlian">
                                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="M12 4v8M7.2 6.4a7 7 0 1 0 9.6 0" /></svg>
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.skills.activate', $skill) }}" class="flex">
                                                @csrf
                                                <button type="submit" class="{{ $skillActionSuccess }}" aria-label="Aktifkan keahlian {{ $skill->name }}" title="Aktifkan keahlian">
                                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg>
                                                </button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('admin.skills.destroy', $skill) }}" data-swal-confirm="Hapus keahlian {{ $skill->name }} secara lunak? Histori pemetaan tetap tersedia." class="flex">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="{{ $skillActionDanger }}" aria-label="Hapus keahlian {{ $skill->name }}" title="Hapus keahlian">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3" /></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-0">
                                    <x-empty-state :title="$skillEmptyTitle" :description="$skillEmptyDescription" />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex flex-col gap-3 text-sm text-[color:var(--tm-text-muted)] sm:flex-row sm:items-center sm:justify-between">
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

        <div class="divide-y divide-[color:var(--tm-border-subtle)] border-t border-[color:var(--tm-border-subtle)] md:hidden">
            @forelse ($skills as $skill)
                <article class="p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs font-bold uppercase tracking-wide tabular-nums text-[color:var(--tm-text-faint)]">No. {{ ($skills->firstItem() ?? 1) + $loop->index }}</p>
                            <h3 class="mt-1 font-bold text-[color:var(--tm-text)]">{{ $skill->name }}</h3>
                            @if ($skill->description)
                                <p class="mt-1 text-xs leading-5 text-[color:var(--tm-text-muted)]">{{ $skill->description }}</p>
                            @endif
                        </div>
                        <span class="ui-status {{ $skill->is_active ? 'ui-status-active' : 'ui-status-inactive' }} shrink-0">{{ $skill->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    </div>
                    <dl class="mt-4 rounded-[var(--tm-r-md)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-sunken)] p-4 text-sm">
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wide text-[color:var(--tm-text-faint)]">Layanan Terpetakan</dt>
                            <dd class="mt-1 text-[color:var(--tm-text-secondary)]">
                                @if ($skill->serviceTypes->isNotEmpty())
                                    {{ $skill->serviceTypes->map(fn ($serviceType) => $serviceType->code.' · '.$serviceType->name)->join(', ') }}
                                @else
                                    <span class="text-[color:var(--tm-text-faint)]">Belum ada layanan</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                    <div class="mt-4 flex justify-end gap-2">
                        <button type="button" data-ui-modal-open="skill-edit-modal-{{ $skill->id }}" class="{{ $skillActionEdit }}" aria-label="Edit keahlian {{ $skill->name }}" title="Edit keahlian">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 2.651 2.651M4.5 19.5l4.04-.808a2 2 0 0 0 1.02-.55l8.95-8.95a2 2 0 0 0 0-2.828l-.884-.884a2 2 0 0 0-2.828 0l-8.95 8.95a2 2 0 0 0-.55 1.02L4.5 19.5Z" /></svg>
                        </button>
                        @if ($skill->is_active)
                            <form method="POST" action="{{ route('admin.skills.deactivate', $skill) }}" data-swal-confirm="Nonaktifkan keahlian {{ $skill->name }}? Pemetaan dan histori yang ada tetap disimpan." class="flex">
                                @csrf
                                <button type="submit" class="{{ $skillActionWarning }}" aria-label="Nonaktifkan keahlian {{ $skill->name }}" title="Nonaktifkan keahlian">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="M12 4v8M7.2 6.4a7 7 0 1 0 9.6 0" /></svg>
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.skills.activate', $skill) }}" class="flex">
                                @csrf
                                <button type="submit" class="{{ $skillActionSuccess }}" aria-label="Aktifkan keahlian {{ $skill->name }}" title="Aktifkan keahlian">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg>
                                </button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('admin.skills.destroy', $skill) }}" data-swal-confirm="Hapus keahlian {{ $skill->name }} secara lunak? Histori pemetaan tetap tersedia." class="flex">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="{{ $skillActionDanger }}" aria-label="Hapus keahlian {{ $skill->name }}" title="Hapus keahlian">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3" /></svg>
                            </button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="p-5">
                    <x-empty-state :title="$skillEmptyTitle" :description="$skillEmptyDescription" />
                </div>
            @endforelse
        </div>
    </section>

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
                        <div>
                            <label for="skill-create-name" class="ui-field-label">Nama Keahlian <span class="text-[color:var(--tm-danger-600)]" aria-hidden="true">*</span><span class="sr-only"> wajib</span></label>
                            <input id="skill-create-name" data-ui-modal-focus name="name" type="text" value="{{ old('name') }}" autocomplete="off" required class="ui-input mt-2" placeholder="Contoh: Jaringan" @error('name') aria-invalid="true" aria-describedby="skill-create-name-error" @enderror>
                            @error('name')<x-field-error id="skill-create-name-error" data-ui-validation-error :message="$message" />@enderror
                        </div>
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

    @foreach ($skills as $skill)
        @php($isEditingSkill = $autoOpenForm === 'edit-'.$skill->id && $errors->any())
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
                            <div>
                                <label for="skill-edit-name-{{ $skill->id }}" class="ui-field-label">Nama Keahlian <span class="text-[color:var(--tm-danger-600)]" aria-hidden="true">*</span><span class="sr-only"> wajib</span></label>
                                <input id="skill-edit-name-{{ $skill->id }}" data-ui-modal-focus name="name" type="text" value="{{ $isEditingSkill ? old('name', $skill->name) : $skill->name }}" autocomplete="off" required class="ui-input mt-2" @error('name') aria-invalid="true" aria-describedby="skill-edit-name-error-{{ $skill->id }}" @enderror>
                                @error('name')<x-field-error id="skill-edit-name-error-{{ $skill->id }}" data-ui-validation-error :message="$message" />@enderror
                            </div>
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
