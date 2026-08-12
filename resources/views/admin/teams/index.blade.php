@extends('layouts.app')

@php
    $autoOpenCreate = old('_team_create') === '1' && $errors->any();
@endphp

@section('title', 'Tim Kerja — '.$branding['application_name'])
@section('header_kicker', 'Data Master')
@section('header_title', 'Tim kerja')

@section('content')
    <x-page-header
        eyebrow="Data Master · Organisasi"
        title="Manajemen Tim Kerja"
        description="Kelola data tim kerja. Penetapan ketua dan anggota dilakukan dari Manajemen Pengguna."
    />

    <section class="ui-panel mt-6 overflow-hidden" aria-labelledby="teams-heading">
        <div class="flex flex-col gap-4 border-b border-[color:var(--tm-border-subtle)] px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div class="min-w-0">
                <h2 id="teams-heading" class="text-base font-bold tracking-tight text-[color:var(--tm-text)]">Daftar Tim Kerja</h2>
                <p class="mt-1 text-sm leading-5 text-[color:var(--tm-text-muted)]">Lihat cakupan anggota dan kelola struktur tim kerja.</p>
            </div>
            <button type="button" data-team-create-open class="ui-btn ui-btn-primary shrink-0" aria-haspopup="dialog" aria-controls="team-create-modal">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
                Tambah tim
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="ui-table w-full min-w-[62rem]">
                <caption class="sr-only">Daftar tim kerja beserta ketua, anggota, deskripsi, dan aksi</caption>
                <thead>
                    <tr>
                        <th scope="col" class="w-14 text-center">No</th>
                        <th scope="col">Nama Tim Kerja</th>
                        <th scope="col">Ketua Tim Kerja</th>
                        <th scope="col">Anggota</th>
                        <th scope="col">Deskripsi</th>
                        <th scope="col" class="w-24 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($teams as $team)
                        @php
                            $chairUser = $team->currentChair?->user;
                            $memberRows = $team->currentMembers
                                ->reject(fn ($member): bool => $chairUser !== null && (int) $member->id === (int) $chairUser->id)
                                ->values();
                        @endphp
                        <tr>
                            <td class="text-center font-semibold tabular-nums text-[color:var(--tm-text-muted)]">{{ $loop->iteration }}</td>
                            <td>
                                <p class="font-semibold text-[color:var(--tm-text)]">{{ $team->name }}</p>
                                <span class="ui-status {{ $team->is_active ? 'ui-status-active' : 'ui-status-inactive' }} mt-2">
                                    {{ $team->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td>
                                @if ($chairUser)
                                    <p class="font-semibold text-[color:var(--tm-text)]">{{ $chairUser->name }}</p>
                                    <p class="mt-0.5 text-xs text-[color:var(--tm-text-faint)]">{{ '@'.$chairUser->username }}</p>
                                @else
                                    <span class="text-[color:var(--tm-text-faint)]">—</span>
                                @endif
                            </td>
                            <td>
                                @if ($memberRows->isNotEmpty())
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        @foreach ($memberRows as $member)
                                            <span class="inline-flex items-center rounded-[var(--tm-r-full)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-sunken)] px-2.5 py-1 text-xs font-medium text-[color:var(--tm-text-secondary)]">{{ $member->name }}</span>
                                        @endforeach
                                        <span class="inline-flex items-center rounded-[var(--tm-r-full)] bg-[color:var(--tm-brand-50)] px-2 py-1 text-xs font-bold tabular-nums text-[color:var(--tm-brand-700)]">{{ $memberRows->count() }}</span>
                                    </div>
                                @else
                                    <span class="text-[color:var(--tm-text-faint)]">—</span>
                                @endif
                            </td>
                            <td class="max-w-xs text-[color:var(--tm-text-secondary)]">{{ $team->description ?: '—' }}</td>
                            <td>
                                <div class="flex justify-center gap-2">
                                    <button type="button" data-ui-modal-open="team-edit-modal-{{ $team->id }}" class="inline-flex h-9 w-9 items-center justify-center rounded-[var(--tm-r-sm)] border border-[color:var(--tm-border)] bg-[color:var(--tm-surface)] text-[color:var(--tm-text-secondary)] transition-colors duration-[var(--tm-dur-fast)] hover:border-[color:var(--tm-brand-300)] hover:bg-[color:var(--tm-brand-50)] hover:text-[color:var(--tm-brand-700)]" aria-label="Edit tim {{ $team->name }}" title="Edit tim">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 2.651 2.651M4.5 19.5l4.04-.808a2 2 0 0 0 1.02-.55l8.95-8.95a2 2 0 0 0 0-2.828l-.884-.884a2 2 0 0 0-2.828 0l-8.95 8.95a2 2 0 0 0-.55 1.02L4.5 19.5Z" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-0">
                                <x-empty-state
                                    title="Belum ada tim kerja"
                                    description="Tambahkan tim kerja terlebih dahulu, lalu tetapkan ketua dan anggotanya dari Manajemen Pengguna."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div id="team-create-modal" data-team-create-modal data-auto-open="{{ $autoOpenCreate ? 'true' : 'false' }}" class="fixed inset-0 z-50 hidden" aria-hidden="true">
        <div class="absolute inset-0 bg-slate-950/50 backdrop-blur-[3px]" data-team-create-close></div>
        <div class="relative flex min-h-full items-center justify-center p-4 sm:p-8">
            <section role="dialog" aria-modal="true" aria-labelledby="team-create-title" class="w-full max-w-lg rounded-[var(--tm-r-lg)] border border-[color:var(--tm-border)] bg-[color:var(--tm-surface)] shadow-[var(--tm-sh-xl)]">
                <div class="flex items-center justify-between gap-4 border-b border-[color:var(--tm-border-subtle)] px-5 py-4 sm:px-6">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-[var(--tm-r-sm)] bg-[color:var(--tm-brand-50)] text-[color:var(--tm-brand-600)]" aria-hidden="true">
                            <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
                        </span>
                        <h2 id="team-create-title" class="truncate text-base font-bold text-[color:var(--tm-text)]">Tambah Tim Kerja</h2>
                    </div>
                    <button type="button" data-team-create-close class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-[var(--tm-r-sm)] text-[color:var(--tm-text-muted)] transition-colors duration-[var(--tm-dur-fast)] hover:bg-[color:var(--tm-n-100)] hover:text-[color:var(--tm-text)]" aria-label="Tutup dialog tambah tim kerja">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" d="m7 7 10 10M17 7 7 17" /></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.teams.store') }}" data-team-create-form class="p-5 sm:p-6">
                    @csrf
                    <input type="hidden" name="_team_create" value="1">
                    <div>
                        <label for="team-create-name" class="ui-field-label">Nama Tim Kerja <span class="text-[color:var(--tm-danger-600)]" aria-hidden="true">*</span><span class="sr-only"> wajib</span></label>
                        <input id="team-create-name" name="name" type="text" value="{{ old('name') }}" required data-team-create-focus class="ui-input mt-2" placeholder="Contoh: Infrastruktur" @error('name') aria-invalid="true" aria-describedby="team-create-name-error" @enderror>
                        @error('name')<x-field-error id="team-create-name-error" :message="$message" />@enderror
                    </div>
                    <div class="mt-4">
                        <label for="team-create-description" class="ui-field-label">Deskripsi</label>
                        <textarea id="team-create-description" name="description" rows="3" class="ui-textarea mt-2" placeholder="Deskripsi singkat tim kerja" @error('description') aria-invalid="true" aria-describedby="team-create-description-error" @enderror>{{ old('description') }}</textarea>
                        @error('description')<x-field-error id="team-create-description-error" :message="$message" />@enderror
                    </div>
                    <div class="mt-6 flex flex-col-reverse gap-2 border-t border-[color:var(--tm-border-subtle)] pt-4 sm:flex-row sm:justify-end">
                        <button type="button" data-team-create-close class="ui-btn ui-btn-ghost">Batal</button>
                        <button type="submit" data-team-create-submit class="ui-btn ui-btn-primary">
                            <span data-team-create-label>Simpan</span>
                            <span data-team-create-loading class="hidden">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>

    @foreach ($teams as $team)
        @php($isEditError = (string) old('_team_edit') === (string) $team->id && $errors->any())
        <div id="team-edit-modal-{{ $team->id }}" data-ui-modal data-auto-open="{{ $isEditError ? 'true' : 'false' }}" class="fixed inset-0 z-50 hidden" aria-hidden="true">
            <div class="absolute inset-0 bg-slate-950/50 backdrop-blur-[3px]" data-ui-modal-close></div>
            <div class="relative flex min-h-full items-center justify-center p-4 sm:p-8">
                <section role="dialog" aria-modal="true" aria-labelledby="team-edit-title-{{ $team->id }}" class="w-full max-w-lg rounded-[var(--tm-r-lg)] border border-[color:var(--tm-border)] bg-[color:var(--tm-surface)] shadow-[var(--tm-sh-xl)]">
                    <div class="flex items-center justify-between gap-4 border-b border-[color:var(--tm-border-subtle)] px-5 py-4 sm:px-6">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-[var(--tm-r-sm)] bg-[color:var(--tm-brand-50)] text-[color:var(--tm-brand-600)]" aria-hidden="true">
                                <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 2.651 2.651M4.5 19.5l4.04-.808a2 2 0 0 0 1.02-.55l8.95-8.95a2 2 0 0 0 0-2.828l-.884-.884a2 2 0 0 0-2.828 0l-8.95 8.95a2 2 0 0 0-.55 1.02L4.5 19.5Z" /></svg>
                            </span>
                            <h2 id="team-edit-title-{{ $team->id }}" class="truncate text-base font-bold text-[color:var(--tm-text)]">Edit Tim Kerja</h2>
                        </div>
                        <button type="button" data-ui-modal-close class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-[var(--tm-r-sm)] text-[color:var(--tm-text-muted)] transition-colors duration-[var(--tm-dur-fast)] hover:bg-[color:var(--tm-n-100)] hover:text-[color:var(--tm-text)]" aria-label="Tutup dialog edit tim kerja">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" d="m7 7 10 10M17 7 7 17" /></svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('admin.teams.update', $team) }}" data-ui-modal-form class="p-5 sm:p-6">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_team_edit" value="{{ $team->id }}">
                        <div>
                            <label for="team-edit-name-{{ $team->id }}" class="ui-field-label">Nama Tim Kerja <span class="text-[color:var(--tm-danger-600)]" aria-hidden="true">*</span><span class="sr-only"> wajib</span></label>
                            <input id="team-edit-name-{{ $team->id }}" name="name" type="text" value="{{ $isEditError ? old('name') : $team->name }}" required data-ui-modal-focus class="ui-input mt-2" @if($isEditError && $errors->has('name')) aria-invalid="true" aria-describedby="team-edit-name-error-{{ $team->id }}" @endif>
                            @if ($isEditError)
                                @error('name')<x-field-error id="team-edit-name-error-{{ $team->id }}" :message="$message" />@enderror
                            @endif
                        </div>
                        <div class="mt-4">
                            <label for="team-edit-description-{{ $team->id }}" class="ui-field-label">Deskripsi</label>
                            <textarea id="team-edit-description-{{ $team->id }}" name="description" rows="3" class="ui-textarea mt-2" @if($isEditError && $errors->has('description')) aria-invalid="true" aria-describedby="team-edit-description-error-{{ $team->id }}" @endif>{{ $isEditError ? old('description') : $team->description }}</textarea>
                            @if ($isEditError)
                                @error('description')<x-field-error id="team-edit-description-error-{{ $team->id }}" :message="$message" />@enderror
                            @endif
                        </div>
                        <div class="mt-6 flex flex-col-reverse gap-2 border-t border-[color:var(--tm-border-subtle)] pt-4 sm:flex-row sm:justify-end">
                            <button type="button" data-ui-modal-close class="ui-btn ui-btn-ghost">Batal</button>
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
