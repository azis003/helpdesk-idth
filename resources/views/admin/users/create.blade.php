@extends('layouts.app')

@php
    $selectedRoleIds = collect(old('role_ids', []))->map(fn ($id) => (string) $id)->all();
    $selectedSkillIds = collect(old('skill_ids', []))->map(fn ($id) => (string) $id)->all();
@endphp

@section('title', 'Buat Pengguna — '.$branding['application_name'])

@section('header_kicker', 'Administrasi identitas')
@section('header_title', 'Buat pengguna')

@section('content')
    <div class="max-w-5xl">
        <a href="{{ route('admin.users.index') }}" class="text-sm font-semibold text-cyan-700 underline decoration-cyan-300 underline-offset-4 hover:text-cyan-900">← Kembali ke daftar pengguna</a>
        <p class="mt-8 text-sm font-semibold uppercase tracking-[0.16em] text-cyan-700">Administrasi identitas</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Buat pengguna</h1>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">Buat akun baru, tetapkan akses, lalu sampaikan password awal melalui prosedur aman. Pengguna wajib mengganti password saat login pertama.</p>

        <form method="POST" action="{{ route('admin.users.store') }}" class="mt-8 space-y-6">
            @csrf
            <section class="ui-panel p-5 sm:p-7" aria-labelledby="profile-heading">
                <h2 id="profile-heading" class="ui-section-title">Profil pengguna</h2>
                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <x-form-field name="name" label="Nama lengkap" required />
                    <x-form-field name="username" label="Username" help="Gunakan huruf, angka, tanda hubung, atau garis bawah." autocomplete="username" required />
                    <x-form-field name="email" label="Email" type="email" autocomplete="email" />
                    <x-form-field name="nip" label="NIP" />
                </div>
            </section>

            <section class="ui-panel p-5 sm:p-7" aria-labelledby="access-heading">
                <h2 id="access-heading" class="ui-section-title">Akses dan organisasi</h2>
                <p class="mt-1 text-sm leading-6 text-slate-600">Role operasional harus diberikan secara eksplisit. Satu pengguna hanya dapat memiliki satu tim utama aktif.</p>

                <fieldset class="mt-5">
                    <legend class="text-sm font-semibold text-slate-800">Role pengguna</legend>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
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
                    @error('role_ids')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                </fieldset>

                <div class="mt-6">
                    <label for="team_id" class="block text-sm font-semibold text-slate-800">Tim utama</label>
                    <select id="team_id" name="team_id" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-sm text-slate-950 shadow-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-200">
                        <option value="">Belum ditetapkan</option>
                        @foreach ($teams as $team)
                            <option value="{{ $team->id }}" @selected((string) old('team_id') === (string) $team->id)>{{ $team->name }}</option>
                        @endforeach
                    </select>
                    @error('team_id')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                </div>

                <fieldset class="mt-6">
                    <legend class="text-sm font-semibold text-slate-800">Bidang keahlian</legend>
                    <p class="mt-1 text-xs leading-5 text-slate-500">Keahlian digunakan untuk membantu rekomendasi teknisi. Pemilihan teknisi tetap dilakukan secara manual oleh Agen Tier 1.</p>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        @forelse ($skills as $skill)
                            <label class="flex items-center gap-3 rounded-xl border border-slate-200 p-4 text-sm text-slate-700 transition hover:border-cyan-300 hover:bg-cyan-50/40">
                                <input type="checkbox" name="skill_ids[]" value="{{ $skill->id }}" @checked(in_array((string) $skill->id, $selectedSkillIds, true)) class="h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
                                <span class="font-medium text-slate-900">{{ $skill->name }}</span>
                            </label>
                        @empty
                            <p class="text-sm text-slate-500">Belum ada keahlian aktif. Anda dapat menambahkannya dari halaman Data keahlian.</p>
                        @endforelse
                    </div>
                    @error('skill_ids')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                </fieldset>
            </section>

            <section class="rounded-2xl border border-[#f1d49a] bg-[#fff9e9] p-5 sm:p-7" aria-labelledby="password-heading">
                <h2 id="password-heading" class="text-lg font-extrabold text-[#7a4d07]">Password awal</h2>
                <p class="mt-1 text-sm leading-6 text-amber-900">Password ini hanya untuk inisialisasi akun. Jangan kirim melalui catatan aplikasi atau audit log.</p>
                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <x-form-field name="temporary_password" label="Password awal" type="password" autocomplete="new-password" help="Minimal 12 karakter, huruf besar, huruf kecil, angka, dan simbol." required />
                    <x-form-field name="temporary_password_confirmation" label="Konfirmasi password awal" type="password" autocomplete="new-password" required />
                </div>
            </section>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route('admin.users.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-500 hover:text-slate-950 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:ring-offset-2">Batal</a>
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-950 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:ring-offset-2">Buat pengguna</button>
            </div>
        </form>
    </div>
@endsection
