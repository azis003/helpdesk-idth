@extends('layouts.app')

@section('title', 'Pengguna — SIHATI')

@section('content')
    <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.16em] text-cyan-700">Administrasi</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Pengguna dan akses</h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">Kelola kebutuhan reset password tanpa menampilkan password yang tersimpan. Role operasional tetap harus diberikan secara eksplisit.</p>
        </div>
        <a href="{{ route('admin.audit-logs.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-500 hover:text-slate-950 focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:ring-offset-2">Lihat audit log</a>
    </div>

    <section class="mt-8 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="hidden overflow-x-auto md:block">
            <table class="min-w-full text-left text-sm">
                <caption class="sr-only">Daftar pengguna dan role yang diberikan</caption>
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold">Pengguna</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Role</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Status</th>
                        <th scope="col" class="px-6 py-4 text-right font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($users as $listedUser)
                        <tr class="align-top">
                            <td class="px-6 py-5">
                                <p class="font-semibold text-slate-900">{{ $listedUser->name }}</p>
                                <p class="mt-1 text-slate-500">{{ $listedUser->username }}{{ $listedUser->nip ? ' · '.$listedUser->nip : '' }}</p>
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex max-w-sm flex-wrap gap-1.5">
                                    @forelse ($listedUser->roles as $role)
                                        <x-role-badge :label="$role->name" />
                                    @empty
                                        <span class="text-slate-500">Belum ada role</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                @if ($listedUser->is_active)
                                    <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-800">Aktif</span>
                                @else
                                    <span class="inline-flex items-center rounded-full border border-rose-200 bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-800">Nonaktif</span>
                                @endif
                                @if ($listedUser->requiresPasswordChange())
                                    <p class="mt-2 text-xs text-amber-700">Menunggu ganti password</p>
                                @endif
                            </td>
                            <td class="px-6 py-5 text-right">
                                @if ($listedUser->isNot(auth()->user()))
                                    <a href="{{ route('admin.users.reset-password.edit', $listedUser) }}" class="font-semibold text-cyan-700 underline decoration-cyan-300 underline-offset-4 hover:text-cyan-900">Reset password</a>
                                @else
                                    <span class="text-xs text-slate-400">Akun Anda</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-12 text-center text-sm text-slate-500">Belum ada pengguna yang terdaftar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="divide-y divide-slate-200 md:hidden">
            @forelse ($users as $listedUser)
                <article class="p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="font-semibold text-slate-900">{{ $listedUser->name }}</h2>
                            <p class="mt-1 text-sm text-slate-500">{{ $listedUser->username }}</p>
                        </div>
                        @if ($listedUser->is_active)
                            <span class="shrink-0 text-xs font-semibold text-emerald-700">Aktif</span>
                        @else
                            <span class="shrink-0 text-xs font-semibold text-rose-700">Nonaktif</span>
                        @endif
                    </div>
                    <div class="mt-4 flex flex-wrap gap-1.5">
                        @forelse ($listedUser->roles as $role)
                            <x-role-badge :label="$role->name" />
                        @empty
                            <span class="text-sm text-slate-500">Belum ada role</span>
                        @endforelse
                    </div>
                    @if ($listedUser->isNot(auth()->user()))
                        <a href="{{ route('admin.users.reset-password.edit', $listedUser) }}" class="mt-5 inline-flex text-sm font-semibold text-cyan-700 underline decoration-cyan-300 underline-offset-4">Reset password</a>
                    @endif
                </article>
            @empty
                <p class="p-8 text-center text-sm text-slate-500">Belum ada pengguna yang terdaftar.</p>
            @endforelse
        </div>

        @if ($users->hasPages())
            <div class="border-t border-slate-200 px-5 py-4 sm:px-6">{{ $users->links() }}</div>
        @endif
    </section>
@endsection
