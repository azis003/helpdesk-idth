@extends('layouts.guest')

@section('title', 'Ganti kata sandi - '.$branding['application_name'])

@section('content')
    <h1 class="ui-login-title">Ganti kata sandi</h1>
    <p class="ui-login-description">Gunakan kata sandi baru yang hanya Anda ketahui.</p>

    <form method="POST" action="{{ route('password.update') }}" class="ui-login-form">
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

        <button type="submit" class="ui-login-submit">Simpan kata sandi baru</button>
    </form>

    <p class="ui-login-note">Jangan bagikan kata sandi Anda kepada siapa pun.</p>
@endsection
