@extends('layouts.app')

@php
    $selectedRoleIds = collect(old('role_ids', $user->roles->pluck('id')->all()))->map(fn ($id) => (string) $id)->all();
    $selectedSkillIds = collect(old('skill_ids', $user->skills->pluck('id')->all()))->map(fn ($id) => (string) $id)->all();
@endphp

@section('title', 'Edit Pengguna — SIHATI')

@section('header_kicker', 'Administrasi identitas')
@section('header_title', 'Detail pengguna')

@section('content')
    <div class="max-w-5xl">
        <a href="{{ route('admin.users.index') }}" class="text-sm font-semibold text-cyan-700 underline decoration-cyan-300 underline-offset-4 hover:text-cyan-900">← Kembali ke daftar pengguna</a>
        <div class="mt-8 flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.16em] text-cyan-700">Administrasi identitas</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">{{ $user->name }}</h1>
                <p class="mt-3 text-sm leading-6 text-slate-600">{{ $user->username }}{{ $user->nip ? ' · '.$user->nip : '' }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if ($user->is_active)
                    <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-800">Aktif</span>
                @else
                    <span class="inline-flex items-center rounded-full border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-800">Nonaktif</span>
                @endif
                @if ($user->isNot(auth()->user()))
                    <a href="{{ route('admin.users.reset-password.edit', $user) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-500 hover:text-slate-950 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:ring-offset-2">Atur ulang password</a>
                @endif
            </div>
        </div>

        <div class="mt-8 space-y-6">
            <section class="ui-panel p-5 sm:p-7" aria-labelledby="profile-heading">
                <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
                    <div>
                        <h2 id="profile-heading" class="text-lg font-semibold text-slate-950">Profil pengguna</h2>
                        <p class="mt-1 text-sm leading-6 text-slate-600">Perubahan profil dicatat bersama pelaku dan waktu perubahan.</p>
                    </div>
                    @if ($user->isNot(auth()->user()))
                        @if ($user->is_active)
                            <form method="POST" action="{{ route('admin.users.deactivate', $user) }}" onsubmit="return confirm('Nonaktifkan akun ini? Pengguna tidak dapat login sampai diaktifkan kembali.');">
                                @csrf
                                <button type="submit" class="inline-flex items-center justify-center rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-semibold text-rose-800 transition hover:border-rose-300 hover:bg-rose-100 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:ring-offset-2">Nonaktifkan akun</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.users.activate', $user) }}">
                                @csrf
                                <button type="submit" class="inline-flex items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-800 transition hover:border-emerald-300 hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2">Aktifkan akun</button>
                            </form>
                        @endif
                    @endif
                </div>

                <form method="POST" action="{{ route('admin.users.update', $user) }}" class="mt-5">
                    @csrf
                    @method('PUT')
                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-form-field name="name" label="Nama lengkap" :value="$user->name" required />
                        <x-form-field name="username" label="Username" :value="$user->username" autocomplete="username" required />
                        <x-form-field name="email" label="Email" type="email" :value="$user->email" autocomplete="email" />
                        <x-form-field name="nip" label="NIP" :value="$user->nip" />
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-950 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:ring-offset-2">Simpan profil</button>
                    </div>
                </form>
            </section>

            <section class="ui-panel p-5 sm:p-7" aria-labelledby="roles-heading">
                <h2 id="roles-heading" class="text-lg font-semibold text-slate-950">Role dan kewenangan</h2>
                <p class="mt-1 text-sm leading-6 text-slate-600">Super Admin tidak otomatis dapat mengerjakan tiket. Berikan role operasional hanya sesuai kewenangan yang disetujui.</p>
                <form method="POST" action="{{ route('admin.users.roles.update', $user) }}" class="mt-5">
                    @csrf
                    @method('PUT')
                    <fieldset>
                        <legend class="sr-only">Pilih role pengguna</legend>
                        <div class="grid gap-3 sm:grid-cols-2">
                            @foreach ($roles as $role)
                                <label class="flex items-start gap-3 rounded-xl border border-slate-200 p-4 text-sm text-slate-700 transition hover:border-cyan-300 hover:bg-cyan-50/40">
                                    <input type="checkbox" name="role_ids[]" value="{{ $role->id }}" @checked(in_array((string) $role->id, $selectedRoleIds, true)) class="mt-1 h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
                                    <span>
                                        <span class="block font-semibold text-slate-900">{{ $role->name }}</span>
                                        <span class="mt-1 block text-xs leading-5 text-slate-500">{{ $role->slug }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-950 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:ring-offset-2">Simpan role</button>
                    </div>
                </form>
            </section>

            <section class="ui-panel p-5 sm:p-7" aria-labelledby="team-heading">
                <h2 id="team-heading" class="text-lg font-semibold text-slate-950">Tim utama</h2>
                <p class="mt-1 text-sm leading-6 text-slate-600">Satu pengguna hanya memiliki satu tim utama aktif. Setiap perpindahan akan masuk histori.</p>
                <form method="POST" action="{{ route('admin.users.team.update', $user) }}" class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-end">
                    @csrf
                    @method('PUT')
                    <div class="min-w-0 flex-1">
                        <label for="team_id" class="block text-sm font-semibold text-slate-800">Pilih tim</label>
                        <select id="team_id" name="team_id" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-sm text-slate-950 shadow-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200">
                            <option value="">Pilih tim aktif</option>
                            @foreach ($teams as $team)
                                <option value="{{ $team->id }}" @selected((string) old('team_id', $user->currentTeamMembership?->work_team_id) === (string) $team->id)>{{ $team->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-950 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:ring-offset-2">Simpan tim</button>
                </form>
                @if ($user->currentTeamMembership?->workTeam)
                    <form method="POST" action="{{ route('admin.users.team.remove', $user) }}" class="mt-3" onsubmit="return confirm('Keluarkan pengguna dari tim utama? Histori perpindahan tetap disimpan.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-sm font-semibold text-rose-700 underline decoration-rose-300 underline-offset-4 hover:text-rose-900">Keluarkan dari tim utama</button>
                    </form>
                @else
                    <p class="mt-4 text-sm text-slate-500">Belum ada tim utama aktif.</p>
                @endif
            </section>

            <section class="ui-panel p-5 sm:p-7" aria-labelledby="skills-heading">
                <h2 id="skills-heading" class="text-lg font-semibold text-slate-950">Bidang keahlian</h2>
                <p class="mt-1 text-sm leading-6 text-slate-600">Keahlian dipakai sebagai dasar saran teknisi berbasis pemetaan kategori, bukan sebagai penugasan otomatis.</p>
                <form method="POST" action="{{ route('admin.users.skills.update', $user) }}" class="mt-5">
                    @csrf
                    @method('PUT')
                    <fieldset>
                        <legend class="sr-only">Pilih bidang keahlian pengguna</legend>
                        <div class="grid gap-3 sm:grid-cols-2">
                            @forelse ($skills as $skill)
                                <label class="flex items-center gap-3 rounded-xl border border-slate-200 p-4 text-sm text-slate-700 transition hover:border-cyan-300 hover:bg-cyan-50/40">
                                    <input type="checkbox" name="skill_ids[]" value="{{ $skill->id }}" @checked(in_array((string) $skill->id, $selectedSkillIds, true)) class="h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
                                    <span class="font-medium text-slate-900">{{ $skill->name }}</span>
                                </label>
                            @empty
                                <p class="text-sm text-slate-500">Belum ada keahlian aktif.</p>
                            @endforelse
                        </div>
                    </fieldset>
                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-950 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:ring-offset-2">Simpan keahlian</button>
                    </div>
                </form>
            </section>

            <section class="ui-panel p-5 sm:p-7" aria-labelledby="history-heading">
                <h2 id="history-heading" class="text-lg font-semibold text-slate-950">Riwayat organisasi</h2>
                <div class="mt-5 grid gap-6 lg:grid-cols-2">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-800">Riwayat tim</h3>
                        <ol class="mt-3 space-y-3 border-l border-slate-200 pl-4">
                            @forelse ($user->teamMemberships->sortByDesc('started_at') as $membership)
                                <li class="relative text-sm leading-6 text-slate-600">
                                    <span class="absolute -left-[1.32rem] top-2 h-2 w-2 rounded-full {{ $membership->is_active ? 'bg-cyan-600' : 'bg-slate-300' }}" aria-hidden="true"></span>
                                    <p class="font-semibold text-slate-900">{{ $membership->workTeam?->name ?? 'Tim dihapus' }}</p>
                                    <p>{{ $membership->started_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') }} — {{ $membership->is_active ? 'Aktif' : 'Berakhir '.$membership->ended_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</p>
                                </li>
                            @empty
                                <li class="text-sm text-slate-500">Belum ada riwayat tim.</li>
                            @endforelse
                        </ol>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-800">Riwayat role</h3>
                        <ol class="mt-3 space-y-3 border-l border-slate-200 pl-4">
                            @forelse ($roleHistories as $history)
                                <li class="relative text-sm leading-6 text-slate-600">
                                    <span class="absolute -left-[1.32rem] top-2 h-2 w-2 rounded-full {{ $history->action === 'assigned' ? 'bg-emerald-600' : 'bg-rose-500' }}" aria-hidden="true"></span>
                                    <p class="font-semibold text-slate-900">{{ $history->role?->name }}</p>
                                    <p>{{ $history->action === 'assigned' ? 'Diberikan' : 'Dicabut' }} · {{ $history->occurred_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</p>
                                </li>
                            @empty
                                <li class="text-sm text-slate-500">Belum ada histori perubahan role.</li>
                            @endforelse
                        </ol>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection
