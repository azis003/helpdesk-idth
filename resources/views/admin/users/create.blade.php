@extends('layouts.app')

@php
    $selectedRoleIds = collect(old('role_ids', []))->map(fn ($id) => (string) $id)->all();
    $selectedSkillIds = collect(old('skill_ids', []))->map(fn ($id) => (string) $id)->all();
@endphp

@section('title', 'Tambah Pengguna — '.$branding['application_name'])

@section('header_kicker', 'Administrasi')
@section('header_title', 'Tambah pengguna')

@section('content')
    <div class="max-w-4xl">
        <a href="{{ route('admin.users.index') }}" class="ui-action-link">← Kembali ke daftar pengguna</a>
        <h1 class="ui-page-title mt-8">Tambah pengguna</h1>

        <form method="POST" action="{{ route('admin.users.store') }}" class="ui-panel mt-6 p-5 sm:p-7">
            @csrf

            <div class="grid gap-5 sm:grid-cols-2">
                <x-form-field name="name" label="Nama lengkap" required />
                <x-form-field name="username" label="Username" autocomplete="username" required />
                <x-form-field name="email" label="Email" type="email" autocomplete="email" />
                <x-form-field name="nip" label="NIP" />
            </div>

            <div class="mt-8 border-t border-[#e7eef1] pt-7">
                <h2 class="ui-section-title">Akses pengguna</h2>

                <fieldset class="mt-5">
                    <legend class="text-sm font-semibold text-slate-800">Role pengguna</legend>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        @foreach ($roles as $role)
                            <label class="flex items-center gap-3 rounded-xl border border-slate-200 p-4 text-sm text-slate-700 transition hover:border-cyan-300 hover:bg-cyan-50/40">
                                <input type="checkbox" name="role_ids[]" value="{{ $role->id }}" @checked(in_array((string) $role->id, $selectedRoleIds, true)) class="h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
                                <span class="font-semibold text-slate-900">{{ $role->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('role_ids')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                </fieldset>

                <div class="mt-6">
                    <label for="team_id" class="block text-sm font-semibold text-slate-800">Tim utama</label>
                    <select id="team_id" name="team_id" class="ui-select mt-2">
                        <option value="">Belum ditetapkan</option>
                        @foreach ($teams as $team)
                            <option value="{{ $team->id }}" @selected((string) old('team_id') === (string) $team->id)>{{ $team->name }}</option>
                        @endforeach
                    </select>
                    @error('team_id')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                </div>

                <fieldset class="mt-6">
                    <legend class="text-sm font-semibold text-slate-800">Bidang keahlian</legend>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        @forelse ($skills as $skill)
                            <label class="flex items-center gap-3 rounded-xl border border-slate-200 p-4 text-sm text-slate-700 transition hover:border-cyan-300 hover:bg-cyan-50/40">
                                <input type="checkbox" name="skill_ids[]" value="{{ $skill->id }}" @checked(in_array((string) $skill->id, $selectedSkillIds, true)) class="h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
                                <span class="font-semibold text-slate-900">{{ $skill->name }}</span>
                            </label>
                        @empty
                            <p class="text-sm text-slate-500">Belum ada keahlian aktif.</p>
                        @endforelse
                    </div>
                    @error('skill_ids')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                </fieldset>
            </div>

            <div class="mt-8 border-t border-[#e7eef1] pt-7">
                <h2 class="ui-section-title">Password awal</h2>
                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <x-form-field name="temporary_password" label="Password awal" type="password" autocomplete="new-password" required />
                    <x-form-field name="temporary_password_confirmation" label="Konfirmasi password awal" type="password" autocomplete="new-password" required />
                </div>
            </div>

            <div class="mt-8 flex flex-col-reverse gap-3 border-t border-[#e7eef1] pt-6 sm:flex-row sm:justify-end">
                <a href="{{ route('admin.users.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-500 hover:text-slate-950 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:ring-offset-2">Batal</a>
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-950 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:ring-offset-2">Tambah pengguna</button>
            </div>
        </form>
    </div>
@endsection
