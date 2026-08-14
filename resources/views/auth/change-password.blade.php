@extends('layouts.guest')

@section('title', 'Ganti kata sandi - '.$branding['application_name'])

@section('content')
    <div class="space-y-1">
        <h1 class="text-xl font-extrabold tracking-tight text-[color:var(--tm-text)]">Ganti kata sandi</h1>
        <p class="text-sm text-[color:var(--tm-text-secondary)]">Gunakan kata sandi baru yang hanya Anda ketahui.</p>
    </div>

    <form method="POST" action="{{ route('password.update') }}" class="ui-login-form flex flex-col gap-4">
        @csrf
        @method('PUT')
        <x-form-field name="current_password" label="Kata sandi saat ini" type="password" autocomplete="current-password" required class="ui-login-input" />
        <x-form-field
            name="password"
            label="Kata sandi baru"
            type="password"
            autocomplete="new-password"
            help="Minimal 12 karakter, mengandung huruf besar, huruf kecil, angka, dan simbol."
            required
            class="ui-login-input"
        />
        <x-form-field name="password_confirmation" label="Konfirmasi kata sandi baru" type="password" autocomplete="new-password" required class="ui-login-input" />

        <button type="submit" class="ui-login-submit ui-btn ui-btn-primary w-full justify-center">Simpan kata sandi baru</button>
    </form>

    <div class="rounded-lg bg-[color:var(--tm-n-50)] p-3 border border-[color:var(--tm-border-subtle)] text-xs text-[color:var(--tm-text-muted)]">
        <p class="font-bold mb-1">Panduan Keamanan:</p>
        <p>Jaga kerahasiaan kata sandi dan jangan membagikannya kepada pihak lain.</p>
    </div>
@endsection
