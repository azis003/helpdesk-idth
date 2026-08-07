@extends('layouts.app')

@php
    $activeTeams = $teams->where('is_active', true)->count();
    $memberCount = $teams->sum(fn ($team) => $team->currentMembers->count());
@endphp

@section('title', 'Tim Kerja — '.$branding['application_name'])
@section('header_kicker', 'Administrasi organisasi')
@section('header_title', 'Struktur tim kerja')

@section('content')
    <div class="ui-page-header">
        <div>
            <p class="ui-eyebrow"><span class="ui-eyebrow-dot" aria-hidden="true"></span>Struktur organisasi</p>
            <h1 class="ui-page-title">Tim kerja</h1>
            <p class="ui-page-description">Atur tim, ketua, dan anggota aktif. Perpindahan anggota disimpan sebagai histori agar struktur organisasi selalu dapat ditelusuri.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.users.index') }}" class="ui-btn ui-btn-ghost">Pengguna</a>
            <a href="{{ route('admin.skills.index') }}" class="ui-btn ui-btn-secondary">Data keahlian</a>
        </div>
    </div>

    <section class="mt-8 grid gap-3 sm:grid-cols-3" aria-label="Ringkasan tim kerja">
        <article class="ui-stat-card"><span class="ui-stat-icon" aria-hidden="true"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 19.5h16M6 19.5V9.7a1 1 0 0 1 .5-.87l5-2.83a1 1 0 0 1 1 0l5 2.83a1 1 0 0 1 .5.87v9.8" /></svg></span><div><p class="ui-stat-label">Total tim</p><p class="ui-stat-value">{{ $teams->count() }}</p></div></article>
        <article class="ui-stat-card"><span class="ui-stat-icon !bg-[#e8faf4] !text-[#087f5b]" aria-hidden="true"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg></span><div><p class="ui-stat-label">Tim aktif</p><p class="ui-stat-value">{{ $activeTeams }}</p></div></article>
        <article class="ui-stat-card"><span class="ui-stat-icon !bg-[#eef3ff] !text-[#4f63a6]" aria-hidden="true"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M16 19v-1.5a3.5 3.5 0 0 0-3.5-3.5h-5A3.5 3.5 0 0 0 4 17.5V19m6-9a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm5.5-6.5a3 3 0 0 1 0 5.8M17 14.3a3.5 3.5 0 0 1 3 3.4V19" /></svg></span><div><p class="ui-stat-label">Anggota aktif</p><p class="ui-stat-value">{{ $memberCount }}</p></div></article>
    </section>

    <section class="ui-panel ui-panel--accent mt-8 p-5 sm:p-6" aria-labelledby="create-team-heading">
        <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
            <div>
                <p class="ui-eyebrow !text-[#0f625e]"><span class="ui-eyebrow-dot" aria-hidden="true"></span>Tambah struktur</p>
                <h2 id="create-team-heading" class="mt-2 text-lg font-extrabold tracking-tight text-[#17313c]">Buat tim baru</h2>
                <p class="mt-1 text-sm leading-6 text-[#52747b]">Tim baru akan aktif dan siap menerima anggota.</p>
            </div>
            <form method="POST" action="{{ route('admin.teams.store') }}" class="grid w-full gap-3 lg:max-w-3xl lg:grid-cols-[1fr_1.25fr_auto]">
                @csrf
                <div>
                    <label for="new-team-name" class="ui-field-label !text-[#35505b]">Nama tim <span class="text-rose-600">*</span></label>
                    <input id="new-team-name" name="name" value="{{ old('name') }}" required class="ui-input mt-2" placeholder="Contoh: Infrastruktur">
                </div>
                <div>
                    <label for="new-team-description" class="ui-field-label !text-[#35505b]">Deskripsi <span class="font-normal text-[#78909a]">(opsional)</span></label>
                    <input id="new-team-description" name="description" value="{{ old('description') }}" class="ui-input mt-2" placeholder="Fokus layanan tim">
                </div>
                <button type="submit" class="ui-btn ui-btn-primary lg:self-end">Tambah tim</button>
            </form>
        </div>
    </section>

    <section class="mt-10" aria-labelledby="teams-heading">
        <div class="flex items-end justify-between gap-4">
            <div>
                <h2 id="teams-heading" class="ui-section-title text-xl">Daftar tim</h2>
                <p class="ui-section-description">Pilih ketua dari anggota aktif tim yang sama.</p>
            </div>
            <span class="hidden text-xs font-bold text-[#78909a] sm:block">{{ $teams->count() }} tim terdaftar</span>
        </div>

        <div class="mt-5 grid gap-5 xl:grid-cols-2">
            @forelse ($teams as $team)
                <article class="ui-panel overflow-hidden">
                    <div class="h-1.5 bg-[#54cfc0]" aria-hidden="true"></div>
                    <div class="p-5 sm:p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="truncate text-lg font-extrabold tracking-tight text-[#17313c]">{{ $team->name }}</h3>
                                    <span class="ui-status {{ $team->is_active ? 'ui-status-active' : 'ui-status-inactive' }}">{{ $team->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                </div>
                                <p class="mt-2 text-sm leading-6 text-[#6a8089]">{{ $team->description ?: 'Belum ada deskripsi tim.' }}</p>
                            </div>
                            <div class="shrink-0 rounded-lg bg-[#f1f7f7] px-2.5 py-1.5 text-center">
                                <p class="text-lg font-extrabold leading-none text-[#0f766e]">{{ $team->currentMembers->count() }}</p>
                                <p class="mt-1 text-[0.6rem] font-extrabold uppercase tracking-[0.1em] text-[#759199]">anggota</p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('admin.teams.update', $team) }}" class="mt-5 rounded-xl bg-[#f7fafb] p-4">
                            @csrf
                            @method('PUT')
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <label for="team-name-{{ $team->id }}" class="ui-field-label">Nama tim</label>
                                    <input id="team-name-{{ $team->id }}" name="name" value="{{ $team->name }}" required class="ui-input mt-2">
                                </div>
                                <div>
                                    <label for="team-description-{{ $team->id }}" class="ui-field-label">Deskripsi</label>
                                    <input id="team-description-{{ $team->id }}" name="description" value="{{ $team->description }}" class="ui-input mt-2">
                                </div>
                            </div>
                            <div class="mt-3 flex justify-end">
                                <button type="submit" class="ui-btn ui-btn-ghost !min-h-9 !px-3 !text-xs">Simpan detail</button>
                            </div>
                        </form>

                        <div class="mt-5 grid gap-4 md:grid-cols-2">
                            <div class="rounded-xl border border-[#e5edef] p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-xs font-extrabold uppercase tracking-[0.1em] text-[#78909a]">Ketua tim</p>
                                        <p class="mt-1 text-sm font-bold text-[#35505b]">{{ $team->currentChair?->user?->name ?? 'Belum ditetapkan' }}</p>
                                    </div>
                                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-[#fff6df] text-[#a16207]" aria-hidden="true">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3.8 14.1 9l5.4.45-4.12 3.5 1.24 5.25L12 15.35l-4.62 2.85 1.24-5.25-4.12-3.5L9.9 9 12 3.8Z" /></svg>
                                    </span>
                                </div>
                                <form method="POST" action="{{ route('admin.teams.chair.update', $team) }}" class="mt-4 space-y-2">
                                    @csrf
                                    @method('PUT')
                                    <label for="chair-{{ $team->id }}" class="sr-only">Ketua tim {{ $team->name }}</label>
                                    <select id="chair-{{ $team->id }}" name="user_id" class="ui-select" @disabled(! $team->is_active)>
                                        <option value="">Tidak ada ketua</option>
                                        @foreach ($team->currentMembers as $member)
                                            <option value="{{ $member->id }}" @selected($team->currentChair?->user_id === $member->id)>{{ $member->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="ui-btn ui-btn-secondary w-full !min-h-9 !text-xs" @disabled(! $team->is_active)>Simpan ketua</button>
                                </form>
                            </div>

                            <div class="rounded-xl border border-[#e5edef] p-4">
                                <p class="text-xs font-extrabold uppercase tracking-[0.1em] text-[#78909a]">Tambah anggota</p>
                                <p class="mt-1 text-sm font-bold text-[#35505b]">Masukkan pengguna aktif</p>
                                <form method="POST" action="{{ route('admin.teams.members.assign', $team) }}" class="mt-4 space-y-2">
                                    @csrf
                                    <label for="member-{{ $team->id }}" class="sr-only">Anggota baru untuk {{ $team->name }}</label>
                                    <select id="member-{{ $team->id }}" name="user_id" required class="ui-select" @disabled(! $team->is_active)>
                                        <option value="">Pilih pengguna aktif</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="ui-btn ui-btn-primary w-full !min-h-9 !text-xs" @disabled(! $team->is_active)>Tetapkan anggota</button>
                                </form>
                            </div>
                        </div>

                        <div class="mt-5 border-t border-[#e7eef1] pt-5">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <h4 class="text-sm font-extrabold text-[#35505b]">Anggota aktif</h4>
                                    <p class="mt-1 text-xs text-[#78909a]">Daftar anggota yang saat ini terhubung ke tim.</p>
                                </div>
                                <span class="rounded-full bg-[#f1f7f7] px-2.5 py-1 text-xs font-extrabold text-[#0f766e]">{{ $team->currentMembers->count() }} orang</span>
                            </div>
                            <ul class="mt-3 divide-y divide-[#edf2f4] rounded-xl border border-[#e5edef]">
                                @forelse ($team->currentMembers as $member)
                                    <li class="flex items-center justify-between gap-4 px-3.5 py-3">
                                        <div class="flex min-w-0 items-center gap-2.5">
                                            <span class="ui-avatar !h-8 !w-8 !rounded-lg text-xs">{{ strtoupper(substr(trim($member->name), 0, 1)) }}</span>
                                            <div class="min-w-0"><p class="truncate text-sm font-bold text-[#35505b]">{{ $member->name }}</p><p class="truncate text-[0.68rem] text-[#78909a]">{{ '@'.$member->username }}</p></div>
                                        </div>
                                        <form method="POST" action="{{ route('admin.teams.members.remove', [$team, $member]) }}" onsubmit="return confirm('Keluarkan pengguna ini dari tim? Histori perpindahan tetap disimpan.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-extrabold text-rose-700 hover:text-rose-900">Keluarkan</button>
                                        </form>
                                    </li>
                                @empty
                                    <li class="px-4 py-5 text-center text-sm text-[#78909a]">Belum ada anggota aktif di tim ini.</li>
                                @endforelse
                            </ul>
                        </div>

                        <div class="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-[#e7eef1] pt-4">
                            @if ($team->is_active)
                                <form method="POST" action="{{ route('admin.teams.deactivate', $team) }}" onsubmit="return confirm('Nonaktifkan tim ini? Tim dengan anggota aktif harus dikosongkan terlebih dahulu.');">
                                    @csrf
                                    <button type="submit" class="ui-btn ui-btn-warning !min-h-9 !px-3 !text-xs">Nonaktifkan</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.teams.activate', $team) }}">
                                    @csrf
                                    <button type="submit" class="ui-btn ui-btn-secondary !min-h-9 !px-3 !text-xs">Aktifkan tim</button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('admin.teams.destroy', $team) }}" onsubmit="return confirm('Hapus tim secara lunak? Data histori tidak akan dihapus, tetapi tim tidak dapat dipakai kembali.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="ui-btn ui-btn-danger !min-h-9 !px-3 !text-xs">Hapus secara lunak</button>
                            </form>
                        </div>
                    </div>
                </article>
            @empty
                <div class="ui-empty xl:col-span-2">
                    <h3 class="font-extrabold text-[#35505b]">Belum ada tim kerja</h3>
                    <p class="mt-2 text-sm leading-6">Tambahkan tim pertama melalui formulir di atas.</p>
                </div>
            @endforelse
        </div>
    </section>
@endsection
