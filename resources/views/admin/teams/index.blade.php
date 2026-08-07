@extends('layouts.app')

@php
    $activeTeams = $teams->where('is_active', true)->count();
    $memberCount = $teams->sum(fn ($team) => $team->currentMembers->count());
@endphp

@section('title', 'Tim Kerja — '.$branding['application_name'])
@section('header_kicker', 'Administrasi')
@section('header_title', 'Tim kerja')

@section('content')
    <div class="ui-page-header">
        <div>
            <h1 class="ui-page-title">Tim Kerja</h1>
            <p class="ui-page-description">Kelola tim, ketua, dan anggota.</p>
        </div>
    </div>

    <section class="mt-8 grid gap-3 sm:grid-cols-2 xl:grid-cols-3" aria-label="Ringkasan tim kerja">
        <article class="ui-stat-card">
            <span class="ui-stat-icon" aria-hidden="true">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 19.5h16M6 19.5V9.7a1 1 0 0 1 .5-.87l5-2.83a1 1 0 0 1 1 0l5 2.83a1 1 0 0 1 .5.87v9.8" /></svg>
            </span>
            <div><p class="ui-stat-label">Total tim</p><p class="ui-stat-value">{{ $teams->count() }}</p></div>
        </article>
        <article class="ui-stat-card">
            <span class="ui-stat-icon !bg-[#e8faf4] !text-[#087f5b]" aria-hidden="true">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg>
            </span>
            <div><p class="ui-stat-label">Tim aktif</p><p class="ui-stat-value">{{ $activeTeams }}</p></div>
        </article>
        <article class="ui-stat-card">
            <span class="ui-stat-icon !bg-[#eef3ff] !text-[#4f63a6]" aria-hidden="true">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 19v-1.5a3.5 3.5 0 0 0-3.5-3.5h-5A3.5 3.5 0 0 0 4 17.5V19m6-9a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm5.5-6.5a3 3 0 0 1 0 5.8M17 14.3a3.5 3.5 0 0 1 3 3.4V19" /></svg>
            </span>
            <div><p class="ui-stat-label">Anggota aktif</p><p class="ui-stat-value">{{ $memberCount }}</p></div>
        </article>
    </section>

    <section class="ui-panel mt-8 overflow-hidden" aria-labelledby="teams-heading">
        <div class="ui-panel-header flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
            <h2 id="teams-heading" class="ui-section-title">Daftar Tim Kerja</h2>
            <button type="button" data-team-create-open class="ui-btn ui-btn-primary w-full shrink-0 sm:w-auto" aria-haspopup="dialog" aria-controls="team-create-modal">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
                Tambah tim
            </button>
        </div>

        <div class="divide-y divide-[#edf2f4]">
            @forelse ($teams as $team)
                @php($initial = strtoupper(substr(trim($team->name), 0, 1)))
                    <details class="group odd:bg-white even:bg-[#f8fbfc]" data-team-accordion>
                        <summary aria-label="Kelola tim {{ $team->name }}" class="cursor-pointer list-none p-5 transition hover:bg-[#f1f8fa] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#2bb8aa] sm:p-6 [&::-webkit-details-marker]:hidden">
                        <span class="flex items-start justify-between gap-4">
                            <span class="flex min-w-0 items-start gap-3">
                                <span class="ui-avatar" aria-hidden="true">{{ $initial }}</span>
                                <span class="min-w-0">
                                    <span class="flex flex-wrap items-center gap-2">
                                        <span role="heading" aria-level="3" class="truncate font-bold text-[#17313c]">{{ $team->name }}</span>
                                        <span class="ui-status {{ $team->is_active ? 'ui-status-active' : 'ui-status-inactive' }}">{{ $team->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                    </span>
                                    @if ($team->description)
                                        <span class="mt-1 block truncate text-xs text-[#78909a]">{{ $team->description }}</span>
                                    @endif
                                </span>
                            </span>
                            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-[#dce7eb] bg-white text-[#55707a] transition group-open:border-[#2bb8aa] group-open:bg-[#effcf9] group-open:text-[#0f766e]" aria-hidden="true">
                                <svg class="h-4 w-4 transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" /></svg>
                            </span>
                        </span>
                        <span class="mt-4 grid max-w-xl grid-cols-2 gap-4 rounded-xl bg-[#f7fafb] p-3.5">
                            <span>
                                <span class="block text-[0.65rem] font-extrabold uppercase tracking-[0.1em] text-[#8aa0a8]">Ketua</span>
                                <span class="mt-1 block truncate text-sm font-semibold text-[#35505b]">{{ $team->currentChair?->user?->name ?? 'Belum ditetapkan' }}</span>
                            </span>
                            <span>
                                <span class="block text-[0.65rem] font-extrabold uppercase tracking-[0.1em] text-[#8aa0a8]">Anggota</span>
                                <span class="mt-1 block text-sm font-semibold text-[#35505b]">{{ $team->currentMembers->count() }} orang</span>
                            </span>
                        </span>
                    </summary>

                            <div data-team-accordion-panel class="overflow-hidden transition-[height,opacity] duration-500 ease-in-out motion-reduce:transition-none">
                        <div class="border-t border-[#e7eef1] bg-[#fbfdfd] p-5 sm:p-6">
                        <div class="grid gap-4 lg:grid-cols-2">
                            <form method="POST" action="{{ route('admin.teams.update', $team) }}" class="rounded-xl border border-[#e5edef] bg-white p-4">
                                @csrf
                                @method('PUT')
                                <div class="flex items-center justify-between gap-3">
                                    <h3 class="text-sm font-extrabold text-[#35505b]">Detail tim</h3>
                                    <button type="submit" class="ui-btn ui-btn-ghost !min-h-9 !px-3 !text-xs disabled:cursor-not-allowed disabled:opacity-50" @disabled(! $team->is_active)>Simpan</button>
                                </div>
                                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                    <div>
                                        <label for="team-name-{{ $team->id }}" class="ui-field-label">Nama tim</label>
                                        <input id="team-name-{{ $team->id }}" name="name" value="{{ $team->name }}" required class="ui-input mt-2 disabled:cursor-not-allowed disabled:bg-[#f3f6f7] disabled:text-[#8aa0a8]" @disabled(! $team->is_active)>
                                    </div>
                                    <div>
                                        <label for="team-description-{{ $team->id }}" class="ui-field-label">Deskripsi</label>
                                        <input id="team-description-{{ $team->id }}" name="description" value="{{ $team->description }}" class="ui-input mt-2 disabled:cursor-not-allowed disabled:bg-[#f3f6f7] disabled:text-[#8aa0a8]" @disabled(! $team->is_active)>
                                    </div>
                                </div>
                            </form>

                            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
                                <div class="rounded-xl border border-[#e5edef] bg-white p-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <h3 class="text-sm font-extrabold text-[#35505b]">Ketua tim</h3>
                                            <p class="mt-1 text-xs text-[#78909a]">{{ $team->currentChair?->user?->name ?? 'Belum ditetapkan' }}</p>
                                        </div>
                                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-[#fff6df] text-[#a16207]" aria-hidden="true">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3.8 14.1 9l5.4.45-4.12 3.5 1.24 5.25L12 15.35l-4.62 2.85 1.24-5.25-4.12-3.5L9.9 9 12 3.8Z" /></svg>
                                        </span>
                                    </div>
                                    <form method="POST" action="{{ route('admin.teams.chair.update', $team) }}" class="mt-4">
                                        @csrf
                                        @method('PUT')
                                        <label for="chair-{{ $team->id }}" class="ui-field-label">Pilih ketua</label>
                                        <select id="chair-{{ $team->id }}" name="user_id" class="ui-select mt-2 disabled:cursor-not-allowed disabled:bg-[#f3f6f7] disabled:text-[#8aa0a8]" @disabled(! $team->is_active)>
                                            <option value="">Tidak ada ketua</option>
                                            @foreach ($team->currentMembers as $member)
                                                <option value="{{ $member->id }}" @selected($team->currentChair?->user_id === $member->id)>{{ $member->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="ui-btn ui-btn-secondary mt-2 w-full !min-h-9 !text-xs disabled:cursor-not-allowed disabled:opacity-50" @disabled(! $team->is_active)>Simpan ketua</button>
                                    </form>
                                </div>

                                <div class="rounded-xl border border-[#e5edef] bg-white p-4">
                                    <h3 class="text-sm font-extrabold text-[#35505b]">Tambah anggota</h3>
                                    <form method="POST" action="{{ route('admin.teams.members.assign', $team) }}" class="mt-4">
                                        @csrf
                                        <label for="member-{{ $team->id }}" class="ui-field-label">Pilih pengguna</label>
                                        <select id="member-{{ $team->id }}" name="user_id" required class="ui-select mt-2 disabled:cursor-not-allowed disabled:bg-[#f3f6f7] disabled:text-[#8aa0a8]" @disabled(! $team->is_active)>
                                            <option value="">Pilih pengguna aktif</option>
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="ui-btn ui-btn-primary mt-2 w-full !min-h-9 !text-xs disabled:cursor-not-allowed disabled:opacity-50" @disabled(! $team->is_active)>Tambah anggota</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 rounded-xl border border-[#e5edef] bg-white">
                            <div class="flex items-center justify-between gap-3 border-b border-[#edf2f4] px-4 py-3">
                                <h3 class="text-sm font-extrabold text-[#35505b]">Anggota</h3>
                                <span class="ui-chip">{{ $team->currentMembers->count() }} orang</span>
                            </div>
                            <ul class="divide-y divide-[#edf2f4]">
                                @forelse ($team->currentMembers as $member)
                                    <li class="flex items-center justify-between gap-4 px-4 py-3">
                                        <div class="flex min-w-0 items-center gap-2.5">
                                            <span class="ui-avatar !h-8 !w-8 !rounded-lg text-xs">{{ strtoupper(substr(trim($member->name), 0, 1)) }}</span>
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-bold text-[#35505b]">{{ $member->name }}</p>
                                                <p class="truncate text-[0.68rem] text-[#78909a]">{{ '@'.$member->username }}</p>
                                            </div>
                                        </div>
                                        <form method="POST" action="{{ route('admin.teams.members.remove', [$team, $member]) }}" onsubmit="return confirm('Hapus anggota ini dari tim?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="ui-btn ui-btn-danger shrink-0 !min-h-8 !px-2.5 !text-xs disabled:cursor-not-allowed disabled:opacity-50" aria-label="Hapus anggota {{ $member->name }}" title="Hapus anggota" @disabled(! $team->is_active)>Hapus anggota</button>
                                        </form>
                                    </li>
                                @empty
                                    <li class="px-4 py-5 text-center text-sm text-[#78909a]">Belum ada anggota.</li>
                                @endforelse
                            </ul>
                        </div>

                        <div class="mt-4 flex flex-wrap items-center justify-end gap-2 border-t border-[#e7eef1] pt-4">
                            @if ($team->is_active)
                                <form method="POST" action="{{ route('admin.teams.deactivate', $team) }}" onsubmit="return confirm('Nonaktifkan tim ini?');">
                                    @csrf
                                    <button type="submit" class="ui-btn ui-btn-warning !min-h-9 !px-3 !text-xs">Nonaktifkan</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.teams.activate', $team) }}">
                                    @csrf
                                    <button type="submit" class="ui-btn ui-btn-secondary !min-h-9 !px-3 !text-xs">Aktifkan</button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('admin.teams.destroy', $team) }}" onsubmit="return confirm('Hapus tim ini? Histori tetap disimpan dan tim tidak dapat digunakan kembali.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="ui-btn ui-btn-danger !min-h-9 !px-3 !text-xs">Hapus</button>
                            </form>
                        </div>
                        </div>
                    </div>
                </details>
            @empty
                <div class="ui-empty m-4">Belum ada tim kerja.</div>
            @endforelse
        </div>
    </section>

    <div id="team-create-modal" data-team-create-modal data-auto-open="{{ old('_team_create') ? 'true' : 'false' }}" class="fixed inset-0 z-50 hidden" aria-hidden="true">
        <div class="absolute inset-0 bg-slate-950/40" data-team-create-close></div>
        <div class="relative flex min-h-full items-center justify-center p-4">
            <section role="dialog" aria-modal="true" aria-labelledby="team-create-title" class="w-full max-w-lg rounded-2xl border border-[#dfe8ec] bg-white shadow-[0_20px_55px_rgba(38,58,67,0.2)]">
                <div class="flex items-center justify-between gap-4 border-b border-[#e7eef1] px-5 py-4 sm:px-6">
                    <h2 id="team-create-title" class="text-base font-extrabold text-[#17313c]">Tambah tim</h2>
                    <button type="button" data-team-create-close class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-[#78909a] transition hover:bg-[#f4f8f9] hover:text-[#35505b] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#2bb8aa]" aria-label="Tutup">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="m7 7 10 10M17 7 7 17" /></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.teams.store') }}" data-team-create-form class="p-5 sm:p-6">
                    @csrf
                    <input type="hidden" name="_team_create" value="1">

                    <div>
                        <label for="team-create-name" class="ui-field-label">Nama tim <span class="text-rose-600">*</span></label>
                        <input id="team-create-name" name="name" value="{{ old('name') }}" required class="ui-input mt-2" placeholder="Contoh: Infrastruktur" @error('name') aria-invalid="true" aria-describedby="team-create-name-error" @enderror>
                        @error('name')
                            <p id="team-create-name-error" class="mt-2 text-sm text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mt-4">
                        <label for="team-create-description" class="ui-field-label">Deskripsi <span class="font-normal text-[#78909a]">(opsional)</span></label>
                        <input id="team-create-description" name="description" value="{{ old('description') }}" class="ui-input mt-2" placeholder="Contoh: Dukungan aplikasi dan jaringan" @error('description') aria-invalid="true" aria-describedby="team-create-description-error" @enderror>
                        @error('description')
                            <p id="team-create-description-error" class="mt-2 text-sm text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" data-team-create-close class="ui-btn ui-btn-ghost">Batal</button>
                        <button type="submit" data-team-create-submit class="ui-btn ui-btn-primary">
                            <span data-team-create-label>Tambah tim</span>
                            <span data-team-create-loading class="hidden">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>
@endsection
