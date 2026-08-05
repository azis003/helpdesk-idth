@extends('layouts.guest')

@section('title', 'Ganti Password — SIHATI')

@section('content')
    <div>
        <p class="text-sm font-semibold uppercase tracking-[0.16em] text-cyan-700">Keamanan akun</p>
        <h1 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">Ganti password</h1>
        <p class="mt-3 text-sm leading-6 text-slate-600">{{ $isInitialChange ? 'Password awal wajib diganti sebelum Anda dapat menggunakan fitur lain.' : 'Gunakan password baru yang hanya Anda ketahui.' }}</p>
    </div>

    <form method="POST" action="{{ route('password.update') }}" class="mt-8 space-y-5">
        @csrf
        @method('PUT')
        <x-form-field name="current_password" label="Password saat ini" type="password" autocomplete="current-password" required />
        <x-form-field
            name="password"
            label="Password baru"
            type="password"
            autocomplete="new-password"
            help="Minimal 12 karakter, mengandung huruf besar, huruf kecil, angka, dan simbol."
            required
        />
        <x-form-field name="password_confirmation" label="Konfirmasi password baru" type="password" autocomplete="new-password" required />

        <button type="submit" class="w-full rounded-xl bg-slate-950 px-4 py-3.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:ring-offset-2">Simpan password baru</button>
    </form>

    <p class="mt-6 text-center text-xs leading-5 text-slate-500">Jangan bagikan password Anda kepada siapa pun.</p>
@endsection
