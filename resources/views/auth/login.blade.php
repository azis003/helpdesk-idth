@extends('layouts.guest')

@section('title', 'Masuk - '.$branding['application_name'])

@section('content')
    <div class="space-y-1">
        <h1 class="text-xl font-extrabold tracking-tight text-[color:var(--tm-text)]" aria-label="Masuk ke {{ $branding['application_name'] }}">Masuk</h1>
        @if ($branding['tagline'])
            <p class="text-sm text-[color:var(--tm-text-secondary)]">{{ $branding['tagline'] }}</p>
        @else
            <p class="text-sm text-[color:var(--tm-text-secondary)]">Portal Layanan TI</p>
        @endif
    </div>

    <form method="POST" action="{{ route('login.store') }}" class="ui-login-form flex flex-col gap-4">
        @csrf
        <x-form-field name="username" label="Username" autocomplete="username" required autofocus class="ui-login-input" />
        <x-form-field name="password" label="Password" type="password" autocomplete="current-password" required class="ui-login-input" />

        <button type="submit" class="ui-login-submit ui-btn ui-btn-primary w-full justify-center">Masuk</button>
    </form>
@endsection
