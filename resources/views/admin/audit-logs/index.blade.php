@extends('layouts.app')

@section('title', 'Audit Log — SIHATI')

@section('content')
    <div>
        <p class="text-sm font-semibold uppercase tracking-[0.16em] text-cyan-700">Administrasi</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Audit log</h1>
        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">Riwayat aksi penting dan percobaan yang ditolak. Log ini bersifat hanya-baca dari aplikasi.</p>
    </div>

    <section class="mt-8 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="hidden overflow-x-auto md:block">
            <table class="min-w-full text-left text-sm">
                <caption class="sr-only">Daftar audit log</caption>
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold">Waktu</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Aksi</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Pelaku</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Hasil</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($auditLogs as $auditLog)
                        <tr class="align-top">
                            <td class="whitespace-nowrap px-6 py-5 text-slate-600">{{ $auditLog->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i:s') }}</td>
                            <td class="px-6 py-5 font-semibold text-slate-900">{{ $auditLog->action }}</td>
                            <td class="px-6 py-5 text-slate-600">{{ $auditLog->user?->username ?? 'Anonymous' }}</td>
                            <td class="px-6 py-5">
                                @if ($auditLog->outcome === 'succeeded')
                                    <span class="font-semibold text-emerald-700">Berhasil</span>
                                @else
                                    <span class="font-semibold text-rose-700">Ditolak</span>
                                @endif
                            </td>
                            <td class="max-w-md px-6 py-5 text-slate-600">{{ $auditLog->reason ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-12 text-center text-sm text-slate-500">Belum ada audit log.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="divide-y divide-slate-200 md:hidden">
            @forelse ($auditLogs as $auditLog)
                <article class="space-y-2 p-5">
                    <div class="flex items-start justify-between gap-4">
                        <p class="font-semibold text-slate-900">{{ $auditLog->action }}</p>
                        @if ($auditLog->outcome === 'succeeded')
                            <span class="shrink-0 text-xs font-semibold text-emerald-700">Berhasil</span>
                        @else
                            <span class="shrink-0 text-xs font-semibold text-rose-700">Ditolak</span>
                        @endif
                    </div>
                    <p class="text-sm text-slate-600">{{ $auditLog->user?->username ?? 'Anonymous' }} · {{ $auditLog->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i:s') }}</p>
                    @if ($auditLog->reason)<p class="text-sm leading-6 text-slate-500">{{ $auditLog->reason }}</p>@endif
                </article>
            @empty
                <p class="p-8 text-center text-sm text-slate-500">Belum ada audit log.</p>
            @endforelse
        </div>

        @if ($auditLogs->hasPages())
            <div class="border-t border-slate-200 px-5 py-4 sm:px-6">{{ $auditLogs->links() }}</div>
        @endif
    </section>
@endsection
