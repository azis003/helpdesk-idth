@extends('layouts.guest')

@section('title', 'Masuk - '.$branding['application_name'])

@section('content')
    <h1 class="ui-login-title" aria-label="Masuk ke {{ $branding['application_name'] }}">{{ $branding['tagline'] ?: 'Portal Layanan TI' }}</h1>

    <form method="POST" action="{{ route('login.store') }}" class="ui-login-form">
        @csrf
        <x-form-field name="username" label="Username" autocomplete="username" required autofocus class="ui-login-input" />
        <x-form-field name="password" label="Password" type="password" autocomplete="current-password" required class="ui-login-input" />

        <button type="submit" class="ui-login-submit">Masuk</button>
    </form>
@endsection
