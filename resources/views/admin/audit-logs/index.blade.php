@extends('layouts.app')

@section('title', 'Audit Log — SIHATI')
@section('header_kicker', 'Administrasi')
@section('header_title', 'Jejak perubahan')

@section('content')
    <div class="ui-page-header">
        <div>
            <p class="ui-eyebrow"><span class="ui-eyebrow-dot" aria-hidden="true"></span>Akuntabilitas akses</p>
            <h1 class="ui-page-title">Audit log</h1>
            <p class="ui-page-description">Riwayat aksi penting dan percobaan yang ditolak. Log ini hanya-baca dan menyimpan kondisi sebelum serta sesudah perubahan master.</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="ui-btn ui-btn-ghost">Kembali ke pengguna <span aria-hidden="true">→</span></a>
    </div>

    <section class="ui-panel mt-8 overflow-hidden">
        <div class="ui-panel-header flex items-center justify-between gap-3">
            <div><h2 class="ui-section-title">Aktivitas terbaru</h2><p class="ui-section-description">{{ $auditLogs->total() }} catatan tersimpan</p></div>
            <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-[#f1f7f7] text-[#0f766e]" aria-hidden="true"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 3.5h9l3 3V20a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V4.5a1 1 0 0 1 1-1Z" /><path stroke-linecap="round" d="M14 3.5V7h4M8 11h8M8 14.5h8" /></svg></span>
        </div>

        <div class="hidden overflow-x-auto md:block">
            <table class="ui-table">
                <caption class="sr-only">Daftar audit log</caption>
                <thead><tr><th scope="col">Waktu</th><th scope="col">Aksi</th><th scope="col">Pelaku</th><th scope="col">Hasil</th><th scope="col">Keterangan dan perubahan</th></tr></thead>
                <tbody>
                    @forelse ($auditLogs as $auditLog)
                        <tr>
                            <td class="whitespace-nowrap text-[#6a8089]">{{ $auditLog->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i:s') }}</td>
                            <td><span class="rounded-lg bg-[#f1f7f7] px-2 py-1 text-xs font-extrabold text-[#35505b]">{{ $auditLog->action }}</span></td>
                            <td class="text-[#6a8089]">{{ $auditLog->user?->username ?? 'Anonymous' }}</td>
                            <td><span class="ui-status {{ $auditLog->outcome === 'succeeded' ? 'ui-status-active' : 'ui-status-inactive !bg-[#fff1f2] !text-[#be123c]' }}">{{ $auditLog->outcome === 'succeeded' ? 'Berhasil' : 'Ditolak' }}</span></td>
                            <td class="max-w-md text-[#6a8089]">
                                <p>{{ $auditLog->reason ?: '—' }}</p>
                                @if ($auditLog->before || $auditLog->after)
                                    <details class="mt-3 rounded-lg border border-[#e1eaed] bg-[#f7fafb] p-3">
                                        <summary class="ui-disclosure-summary flex items-center justify-between gap-3 text-xs font-extrabold text-[#45606a]">Lihat sebelum/sesudah</summary>
                                        <div class="mt-3 grid gap-3 text-xs lg:grid-cols-2">
                                            <div><p class="font-extrabold uppercase tracking-[0.1em] text-[#8aa0a8]">Sebelum</p><pre class="mt-2 overflow-x-auto whitespace-pre-wrap break-words text-[#526b75]">{{ json_encode($auditLog->before, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></div>
                                            <div><p class="font-extrabold uppercase tracking-[0.1em] text-[#8aa0a8]">Sesudah</p><pre class="mt-2 overflow-x-auto whitespace-pre-wrap break-words text-[#526b75]">{{ json_encode($auditLog->after, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></div>
                                        </div>
                                    </details>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="ui-empty">Belum ada audit log.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="divide-y divide-[#edf2f4] md:hidden">
            @forelse ($auditLogs as $auditLog)
                <article class="space-y-3 p-5">
                    <div class="flex items-start justify-between gap-4"><p class="font-extrabold text-[#35505b]">{{ $auditLog->action }}</p><span class="ui-status {{ $auditLog->outcome === 'succeeded' ? 'ui-status-active' : 'ui-status-inactive !bg-[#fff1f2] !text-[#be123c]' }}">{{ $auditLog->outcome === 'succeeded' ? 'Berhasil' : 'Ditolak' }}</span></div>
                    <p class="text-xs text-[#78909a]">{{ $auditLog->user?->username ?? 'Anonymous' }} · {{ $auditLog->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i:s') }}</p>
                    @if ($auditLog->reason)<p class="text-sm leading-6 text-[#6a8089]">{{ $auditLog->reason }}</p>@endif
                    @if ($auditLog->before || $auditLog->after)
                        <details class="rounded-lg border border-[#e1eaed] bg-[#f7fafb] p-3"><summary class="ui-disclosure-summary flex items-center justify-between gap-3 text-xs font-extrabold text-[#45606a]">Lihat sebelum/sesudah</summary><div class="mt-3 space-y-3 text-xs"><div><p class="font-extrabold uppercase tracking-[0.1em] text-[#8aa0a8]">Sebelum</p><pre class="mt-2 whitespace-pre-wrap break-words text-[#526b75]">{{ json_encode($auditLog->before, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></div><div><p class="font-extrabold uppercase tracking-[0.1em] text-[#8aa0a8]">Sesudah</p><pre class="mt-2 whitespace-pre-wrap break-words text-[#526b75]">{{ json_encode($auditLog->after, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></div></div></details>
                    @endif
                </article>
            @empty
                <div class="ui-empty m-4">Belum ada audit log.</div>
            @endforelse
        </div>

        @if ($auditLogs->hasPages())
            <div class="border-t border-[#e7eef1] px-5 py-4 sm:px-6">{{ $auditLogs->links() }}</div>
        @endif
    </section>
@endsection
