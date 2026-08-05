@extends('layouts.guest')

@section('title', 'Masuk — SIHATI')

@section('content')
    <div>
        <p class="text-sm font-semibold uppercase tracking-[0.16em] text-cyan-700">Akses internal</p>
        <h1 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">Masuk ke SIHATI</h1>
        <p class="mt-3 text-sm leading-6 text-slate-600">Gunakan username dan password akun internal Anda untuk melanjutkan.</p>
    </div>

    <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5">
        @csrf
        <x-form-field name="username" label="Username" autocomplete="username" required autofocus />
        <x-form-field name="password" label="Password" type="password" autocomplete="current-password" required />

        <label class="flex items-start gap-3 text-sm text-slate-600">
            <input type="checkbox" name="remember" value="1" class="mt-0.5 h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
            <span>Ingat sesi ini pada perangkat yang aman.</span>
        </label>

        <button type="submit" class="w-full rounded-xl bg-slate-950 px-4 py-3.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:ring-offset-2">Masuk</button>
    </form>

@endsection
