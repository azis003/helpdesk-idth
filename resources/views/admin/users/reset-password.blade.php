@extends('layouts.app')

@section('title', 'Reset Password — '.$branding['application_name'])

@section('header_kicker', 'Data Master · akses')
@section('header_title', 'Atur ulang password')

@section('content')
    <div class="max-w-2xl">
        <x-page-header
            eyebrow="Data Master · Akses"
            title="Atur ulang password pengguna"
            description="Reset untuk {{ $user->name }} ({{ $user->username }})."
            :back-url="route('admin.users.index')"
            back-label="Kembali ke daftar pengguna"
        />

        <div class="mt-6 flex items-start gap-3 rounded-[var(--tm-r-lg)] border border-[color:var(--tm-warning-200)] bg-[color:var(--tm-warning-50)] p-5 text-sm leading-6 text-[color:var(--tm-warning-700)]">
            <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9.25v4m0 3.25h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" />
            </svg>
            <div>
                <p class="font-extrabold">Perhatikan prosedur distribusi.</p>
                <p class="mt-1">Password sementara digunakan untuk akses berikutnya. Sampaikan melalui kanal terpisah yang disetujui dan jangan memasukkannya ke catatan audit atau pesan aplikasi.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.users.reset-password', $user) }}" class="ui-panel mt-6 p-5 sm:p-7">
            @csrf
            @method('PUT')
            <div class="space-y-5">
                <x-form-field
                    name="temporary_password"
                    label="Password sementara"
                    type="password"
                    autocomplete="new-password"
                    help="Minimal 12 karakter, mengandung huruf besar, huruf kecil, angka, dan simbol."
                    required
                />
                <x-form-field name="temporary_password_confirmation" label="Konfirmasi password sementara" type="password" autocomplete="new-password" required />
                <label class="flex items-start gap-3 rounded-[var(--tm-r-md)] border border-[color:var(--tm-border)] bg-[color:var(--tm-n-25)] p-4 text-sm leading-6 text-[color:var(--tm-text-secondary)] transition hover:border-[color:var(--tm-brand-300)] hover:bg-[color:var(--tm-brand-50)]">
                    <input type="checkbox" name="confirm_reset" value="1" @checked(old('confirm_reset')) class="ui-checkbox mt-1" required>
                    <span>Saya memahami bahwa password sementara harus disalurkan secara aman.</span>
                </label>
                @error('confirm_reset')<p class="text-sm font-medium text-[color:var(--tm-danger-700)]">{{ $message }}</p>@enderror
            </div>
            <div class="mt-7 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route('admin.users.index') }}" class="ui-btn ui-btn-secondary">Batal</a>
                <button type="submit" class="ui-btn ui-btn-primary">Atur ulang password</button>
            </div>
        </form>
    </div>
@endsection
