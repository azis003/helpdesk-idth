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

    <section class="mt-7 overflow-hidden rounded-lg border border-[#d7dde0] bg-white shadow-[0_2px_8px_rgba(36,57,67,0.06)]" aria-labelledby="teams-heading">
        <div class="flex flex-col gap-4 bg-[#075998] px-5 py-5 text-white sm:flex-row sm:items-center sm:justify-between sm:px-8">
            <div>
                <h2 id="teams-heading" class="text-xl font-extrabold tracking-tight">Daftar Tim Kerja</h2>
                <p class="mt-1 text-xs leading-5 text-blue-100">Lihat cakupan anggota dan kelola struktur tim kerja.</p>
            </div>
            <button type="button" data-team-create-open class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-[#7138e8] px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-[#6229d5] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-[#075998]" aria-haspopup="dialog" aria-controls="team-create-modal">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
                Tambah
            </button>
        </div>

        <div class="px-5 py-5 sm:px-8">
            <div class="overflow-x-auto rounded-lg border border-[#d7dde0]">
                <table class="min-w-[980px] w-full border-collapse text-left text-sm">
                    <caption class="sr-only">Daftar tim kerja beserta ketua, anggota, deskripsi, dan aksi</caption>
                    <thead class="bg-[#fbfcfd] text-[#34495a]">
                        <tr>
                            <th scope="col" class="w-16 border-b border-[#cfd6da] px-4 py-3 text-center text-xs font-extrabold uppercase tracking-wide">No</th>
                            <th scope="col" class="border-b border-[#cfd6da] px-4 py-3 text-xs font-extrabold uppercase tracking-wide">Nama Tim Kerja</th>
                            <th scope="col" class="border-b border-[#cfd6da] px-4 py-3 text-xs font-extrabold uppercase tracking-wide">Ketua Tim Kerja</th>
                            <th scope="col" class="border-b border-[#cfd6da] px-4 py-3 text-xs font-extrabold uppercase tracking-wide">Anggota</th>
                            <th scope="col" class="border-b border-[#cfd6da] px-4 py-3 text-xs font-extrabold uppercase tracking-wide">Deskripsi</th>
                            <th scope="col" class="w-28 border-b border-[#cfd6da] px-4 py-3 text-center text-xs font-extrabold uppercase tracking-wide">Aksi</th>
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
                            <tr class="odd:bg-[#f8fafb] even:bg-white hover:bg-[#eef7fc]">
                                <td class="border-b border-[#e5eaed] px-4 py-5 text-center font-semibold text-[#172d45]">{{ $loop->iteration }}</td>
                                <td class="border-b border-[#e5eaed] px-4 py-5">
                                    <p class="font-semibold text-[#112b49]">{{ $team->name }}</p>
                                    <span class="mt-2 inline-flex items-center rounded-full px-2 py-1 text-[0.65rem] font-bold {{ $team->is_active ? 'bg-[#e8faf4] text-[#087f5b]' : 'bg-[#eef2f4] text-[#657984]' }}">
                                        {{ $team->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="border-b border-[#e5eaed] px-4 py-5 text-[#172d45]">
                                    @if ($chairUser)
                                        <p class="font-semibold">{{ $chairUser->name }}</p>
                                        <p class="mt-1 text-xs text-[#78909a]">{{ '@'.$chairUser->username }}</p>
                                    @else
                                        <span class="text-[#78909a]">—</span>
                                    @endif
                                </td>
                                <td class="border-b border-[#e5eaed] px-4 py-5 text-[#172d45]">
                                    @if ($memberRows->isNotEmpty())
                                        <ul class="list-disc space-y-1 pl-4">
                                            @foreach ($memberRows as $member)
                                                <li>{{ $member->name }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="text-[#78909a]">—</span>
                                    @endif
                                </td>
                                <td class="max-w-xs border-b border-[#e5eaed] px-4 py-5 text-[#172d45]">{{ $team->description ?: '—' }}</td>
                                <td class="border-b border-[#e5eaed] px-4 py-5">
                                    <div class="flex justify-center gap-2">
                                        <button type="button" data-ui-modal-open="team-edit-modal-{{ $team->id }}" class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-[#0b98e5] text-white transition hover:bg-[#087fc1] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0b98e5] focus-visible:ring-offset-2" aria-label="Edit tim {{ $team->name }}" title="Edit tim">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 2.651 2.651M4.5 19.5l4.04-.808a2 2 0 0 0 1.02-.55l8.95-8.95a2 2 0 0 0 0-2.828l-.884-.884a2 2 0 0 0-2.828 0l-8.95 8.95a2 2 0 0 0-.55 1.02L4.5 19.5Z" /></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-[#718088]">Belum ada tim kerja.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <div id="team-create-modal" data-team-create-modal data-auto-open="{{ $autoOpenCreate ? 'true' : 'false' }}" class="fixed inset-0 z-50 hidden" aria-hidden="true">
        <div class="absolute inset-0 bg-slate-950/45" data-team-create-close></div>
        <div class="relative flex min-h-full items-center justify-center p-4 sm:p-8">
            <section role="dialog" aria-modal="true" aria-labelledby="team-create-title" class="w-full max-w-lg rounded-xl border border-[#dfe8ec] bg-white shadow-[0_20px_55px_rgba(38,58,67,0.2)]">
                <div class="flex items-center justify-between gap-4 bg-[#6098c6] px-5 py-4 text-white sm:px-6">
                    <h2 id="team-create-title" class="text-lg font-extrabold">Tambah Tim Kerja</h2>
                    <button type="button" data-team-create-close class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-white transition hover:bg-white/15 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white" aria-label="Tutup dialog tambah tim kerja">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" d="m7 7 10 10M17 7 7 17" /></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.teams.store') }}" data-team-create-form class="p-5 sm:p-6">
                    @csrf
                    <input type="hidden" name="_team_create" value="1">
                    <div>
                        <label for="team-create-name" class="ui-field-label">Nama Tim Kerja <span class="text-rose-600" aria-hidden="true">*</span><span class="sr-only"> wajib</span></label>
                        <input id="team-create-name" name="name" type="text" value="{{ old('name') }}" required data-team-create-focus class="ui-input mt-2" placeholder="Contoh: Infrastruktur" @error('name') aria-invalid="true" aria-describedby="team-create-name-error" @enderror>
                        @error('name')<p id="team-create-name-error" class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                    </div>
                    <div class="mt-4">
                        <label for="team-create-description" class="ui-field-label">Deskripsi</label>
                        <textarea id="team-create-description" name="description" rows="3" class="ui-textarea mt-2" placeholder="Deskripsi singkat tim kerja" @error('description') aria-invalid="true" aria-describedby="team-create-description-error" @enderror>{{ old('description') }}</textarea>
                        @error('description')<p id="team-create-description-error" class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                    </div>
                    <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
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
            <div class="absolute inset-0 bg-slate-950/45" data-ui-modal-close></div>
            <div class="relative flex min-h-full items-center justify-center p-4 sm:p-8">
                <section role="dialog" aria-modal="true" aria-labelledby="team-edit-title-{{ $team->id }}" class="w-full max-w-lg rounded-xl border border-[#dfe8ec] bg-white shadow-[0_20px_55px_rgba(38,58,67,0.2)]">
                    <div class="flex items-center justify-between gap-4 bg-[#6098c6] px-5 py-4 text-white sm:px-6">
                        <h2 id="team-edit-title-{{ $team->id }}" class="text-lg font-extrabold">Edit Tim Kerja</h2>
                        <button type="button" data-ui-modal-close class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-white transition hover:bg-white/15 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white" aria-label="Tutup dialog edit tim kerja">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" d="m7 7 10 10M17 7 7 17" /></svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('admin.teams.update', $team) }}" data-ui-modal-form class="p-5 sm:p-6">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_team_edit" value="{{ $team->id }}">
                        <div>
                            <label for="team-edit-name-{{ $team->id }}" class="ui-field-label">Nama Tim Kerja <span class="text-rose-600" aria-hidden="true">*</span><span class="sr-only"> wajib</span></label>
                            <input id="team-edit-name-{{ $team->id }}" name="name" type="text" value="{{ $isEditError ? old('name') : $team->name }}" required data-ui-modal-focus class="ui-input mt-2" @if($isEditError && $errors->has('name')) aria-invalid="true" aria-describedby="team-edit-name-error-{{ $team->id }}" @endif>
                            @if ($isEditError)
                                @error('name')<p id="team-edit-name-error-{{ $team->id }}" class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                            @endif
                        </div>
                        <div class="mt-4">
                            <label for="team-edit-description-{{ $team->id }}" class="ui-field-label">Deskripsi</label>
                            <textarea id="team-edit-description-{{ $team->id }}" name="description" rows="3" class="ui-textarea mt-2" @if($isEditError && $errors->has('description')) aria-invalid="true" aria-describedby="team-edit-description-error-{{ $team->id }}" @endif>{{ $isEditError ? old('description') : $team->description }}</textarea>
                            @if ($isEditError)
                                @error('description')<p id="team-edit-description-error-{{ $team->id }}" class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                            @endif
                        </div>
                        <div class="mt-6 flex flex-col-reverse gap-2 border-t border-[#edf2f4] pt-4 sm:flex-row sm:justify-end">
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
