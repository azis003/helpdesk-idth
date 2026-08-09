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

        <div class="mt-6 rounded-2xl border border-[#f1d49a] bg-[#fff9e9] p-5 text-sm leading-6 text-[#7a4d07]">
            <p class="font-extrabold">Perhatikan prosedur distribusi.</p>
            <p class="mt-1">Password sementara digunakan untuk akses berikutnya. Sampaikan melalui kanal terpisah yang disetujui dan jangan memasukkannya ke catatan audit atau pesan aplikasi.</p>
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
                <label class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm leading-6 text-slate-700">
                    <input type="checkbox" name="confirm_reset" value="1" @checked(old('confirm_reset')) class="mt-1 h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500" required>
                    <span>Saya memahami bahwa password sementara harus disalurkan secara aman.</span>
                </label>
                @error('confirm_reset')<p class="text-sm text-rose-700">{{ $message }}</p>@enderror
            </div>
            <div class="mt-7 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route('admin.users.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-500 hover:text-slate-950 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:ring-offset-2">Batal</a>
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-950 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:ring-offset-2">Atur ulang password</button>
            </div>
        </form>
    </div>
@endsection
