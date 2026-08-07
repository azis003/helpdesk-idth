@extends('layouts.app')

@php
    $selectedRoleIds = collect(old('role_ids', $user->roles->pluck('id')->all()))->map(fn ($id) => (string) $id)->all();
    $selectedSkillIds = collect(old('skill_ids', $user->skills->pluck('id')->all()))->map(fn ($id) => (string) $id)->all();
@endphp

@section('title', 'Edit Pengguna — '.$branding['application_name'])

@section('header_kicker', 'Administrasi')
@section('header_title', 'Edit pengguna')

@section('content')
    <div class="max-w-4xl">
        <h1 class="ui-page-title">Edit pengguna</h1>

        <section class="ui-panel mt-6 overflow-hidden" aria-label="Form edit pengguna">
            <div class="p-5 sm:p-7">
                <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                    <h2 class="ui-section-title">Profil pengguna</h2>
                    <div class="flex flex-wrap items-center gap-2">
                        @if ($user->is_active)
                            <span class="ui-status ui-status-active">Aktif</span>
                        @else
                            <span class="ui-status ui-status-inactive">Nonaktif</span>
                        @endif
                        @if ($user->isNot(auth()->user()))
                            @if ($user->is_active)
                                <form method="POST" action="{{ route('admin.users.deactivate', $user) }}" onsubmit="return confirm('Nonaktifkan akun ini?');">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center justify-center rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-800 transition hover:border-rose-300 hover:bg-rose-100 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:ring-offset-2">Nonaktifkan</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.users.activate', $user) }}">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-800 transition hover:border-emerald-300 hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:ring-offset-2">Aktifkan</button>
                                </form>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.users.update', $user) }}">
                @csrf
                @method('PUT')

                <div class="border-t border-[#e7eef1] p-5 sm:p-7">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-form-field name="name" label="Nama lengkap" :value="$user->name" required />
                        <x-form-field name="username" label="Username" :value="$user->username" autocomplete="username" required />
                        <x-form-field name="email" label="Email" type="email" :value="$user->email" autocomplete="email" />
                        <x-form-field name="nip" label="NIP" :value="$user->nip" />
                    </div>
                </div>

                <div class="border-t border-[#e7eef1] p-5 sm:p-7">
                    <h2 class="ui-section-title">Role pengguna</h2>
                    <fieldset class="mt-5">
                        <legend class="sr-only">Pilih role pengguna</legend>
                        <div class="grid gap-3 sm:grid-cols-2">
                            @foreach ($roles as $role)
                                <label class="flex items-center gap-3 rounded-xl border border-slate-200 p-4 text-sm text-slate-700 transition hover:border-cyan-300 hover:bg-cyan-50/40">
                                    <input type="checkbox" name="role_ids[]" value="{{ $role->id }}" @checked(in_array((string) $role->id, $selectedRoleIds, true)) class="h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
                                    <span class="font-semibold text-slate-900">{{ $role->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                    @error('role_ids')
                        <p class="mt-2 text-sm text-rose-700">{{ $message }}</p>
                    @enderror
                </div>

                <div class="border-t border-[#e7eef1] p-5 sm:p-7">
                    <h2 class="ui-section-title">Tim utama</h2>
                    <label for="team_id" class="sr-only">Tim utama</label>
                    <select id="team_id" name="team_id" class="ui-select mt-5">
                        <option value="">Belum ditetapkan</option>
                        @foreach ($teams as $team)
                            <option value="{{ $team->id }}" @selected((string) old('team_id', $user->currentTeamMembership?->work_team_id) === (string) $team->id)>{{ $team->name }}</option>
                        @endforeach
                    </select>
                    @error('team_id')
                        <p class="mt-2 text-sm text-rose-700">{{ $message }}</p>
                    @enderror
                </div>

                <div class="border-t border-[#e7eef1] p-5 sm:p-7">
                    <h2 class="ui-section-title">Bidang keahlian</h2>
                    <fieldset class="mt-5">
                        <legend class="sr-only">Pilih bidang keahlian pengguna</legend>
                        <div class="grid gap-3 sm:grid-cols-2">
                            @forelse ($skills as $skill)
                                <label class="flex items-center gap-3 rounded-xl border border-slate-200 p-4 text-sm text-slate-700 transition hover:border-cyan-300 hover:bg-cyan-50/40">
                                    <input type="checkbox" name="skill_ids[]" value="{{ $skill->id }}" @checked(in_array((string) $skill->id, $selectedSkillIds, true)) class="h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
                                    <span class="font-semibold text-slate-900">{{ $skill->name }}</span>
                                </label>
                            @empty
                                <p class="text-sm text-slate-500">Belum ada keahlian aktif.</p>
                            @endforelse
                        </div>
                    </fieldset>
                    @error('skill_ids')
                        <p class="mt-2 text-sm text-rose-700">{{ $message }}</p>
                    @enderror
                </div>

                <div class="border-t border-[#e7eef1] p-5 sm:p-7" aria-labelledby="history-heading">
                    <h2 id="history-heading" class="ui-section-title">Riwayat organisasi</h2>
                    <div class="mt-5 grid gap-6 lg:grid-cols-2">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-800">Riwayat tim</h3>
                            <ol class="mt-3 space-y-3 border-l border-slate-200 pl-4">
                                @forelse ($user->teamMemberships->sortByDesc('started_at') as $membership)
                                    <li class="relative text-sm leading-6 text-slate-600">
                                        <span class="absolute -left-[1.32rem] top-2 h-2 w-2 rounded-full {{ $membership->is_active ? 'bg-cyan-600' : 'bg-slate-300' }}" aria-hidden="true"></span>
                                        <p class="font-semibold text-slate-900">{{ $membership->workTeam?->name ?? 'Tim dihapus' }}</p>
                                        <p>{{ $membership->started_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') }} &mdash; {{ $membership->is_active ? 'Aktif' : 'Berakhir '.$membership->ended_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</p>
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
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-[#e7eef1] p-5 sm:flex-row sm:justify-end sm:p-7">
                    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-500 hover:text-slate-950 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:ring-offset-2">Kembali</a>
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-950 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:ring-offset-2">Simpan</button>
                </div>
            </form>
        </section>
    </div>
@endsection
