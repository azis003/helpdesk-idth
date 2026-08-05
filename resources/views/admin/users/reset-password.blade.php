@extends('layouts.app')

@section('title', 'Reset Password — SIHATI')

@section('content')
    <div class="max-w-2xl">
        <a href="{{ route('admin.users.index') }}" class="text-sm font-semibold text-cyan-700 underline decoration-cyan-300 underline-offset-4 hover:text-cyan-900">← Kembali ke daftar pengguna</a>
        <p class="mt-8 text-sm font-semibold uppercase tracking-[0.16em] text-cyan-700">Administrasi akses</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Reset password pengguna</h1>
        <p class="mt-3 text-sm leading-6 text-slate-600">Reset untuk <span class="font-semibold text-slate-900">{{ $user->name }}</span> ({{ $user->username }}).</p>

        <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm leading-6 text-amber-950">
            <p class="font-semibold">Perhatikan prosedur distribusi.</p>
            <p class="mt-1">Password sementara hanya digunakan untuk satu kali inisialisasi. Sampaikan melalui kanal terpisah yang disetujui dan jangan memasukkannya ke catatan audit atau pesan aplikasi.</p>
        </div>

        <form method="POST" action="{{ route('admin.users.reset-password', $user) }}" class="mt-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
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
                    <span>Saya memahami bahwa password sementara harus disalurkan secara aman dan pengguna wajib menggantinya saat login berikutnya.</span>
                </label>
                @error('confirm_reset')<p class="text-sm text-rose-700">{{ $message }}</p>@enderror
            </div>
            <div class="mt-7 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route('admin.users.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-500 hover:text-slate-950 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:ring-offset-2">Batal</a>
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-950 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:ring-offset-2">Reset password</button>
            </div>
        </form>
    </div>
@endsection
