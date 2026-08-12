@extends('layouts.app')

@php
    $actionLabels = [
        'auth.login' => 'Masuk ke aplikasi',
        'auth.logout' => 'Keluar dari aplikasi',
        'auth.role_required' => 'Akses ditolak karena peran',
        'auth.password_change_required' => 'Akses dibatasi sampai password diganti',
        'request.denied' => 'Permintaan ditolak',
        'admin.user.created' => 'Pengguna dibuat',
        'admin.user.updated' => 'Data pengguna diperbarui',
        'admin.user.activated' => 'Pengguna diaktifkan',
        'admin.user.deactivated' => 'Pengguna dinonaktifkan',
        'admin.user.deleted' => 'Pengguna dihapus',
        'admin.user.roles.updated' => 'Peran pengguna diperbarui',
        'admin.team.created' => 'Tim kerja dibuat',
        'admin.team.updated' => 'Tim kerja diperbarui',
        'admin.team.activated' => 'Tim kerja diaktifkan',
        'admin.team.deactivated' => 'Tim kerja dinonaktifkan',
        'admin.skill.created' => 'Keahlian dibuat',
        'admin.skill.updated' => 'Keahlian diperbarui',
        'admin.skill.activated' => 'Keahlian diaktifkan',
        'admin.skill.deactivated' => 'Keahlian dinonaktifkan',
        'admin.category.created' => 'Kategori masalah dibuat',
        'admin.category.updated' => 'Kategori masalah diperbarui',
        'admin.service_type.created' => 'Jenis layanan dibuat',
        'admin.service_type.updated' => 'Jenis layanan diperbarui',
        'admin.service_field.created' => 'Field layanan dibuat',
        'admin.service_field.updated' => 'Field layanan diperbarui',
        'admin.building.created' => 'Gedung dibuat',
        'admin.building.updated' => 'Gedung diperbarui',
        'admin.floor.created' => 'Lantai dibuat',
        'admin.floor.updated' => 'Lantai diperbarui',
        'admin.room.created' => 'Ruangan dibuat',
        'admin.room.updated' => 'Ruangan diperbarui',
        'admin.announcement.created' => 'Pengumuman dibuat',
        'admin.announcement.updated' => 'Pengumuman diperbarui',
        'admin.branding.updated' => 'Identitas aplikasi diperbarui',
        'admin.operational_setting.updated' => 'Pengaturan operasional diperbarui',
        'admin.service_calendar.updated' => 'Kalender layanan diperbarui',
        'admin.sla_policy.created' => 'Kebijakan SLA dibuat',
        'admin.sla_policy.updated' => 'Kebijakan SLA diperbarui',
        'admin.approver.assigned' => 'Approver ditetapkan',
        'admin.approver.replaced' => 'Approver diganti',
        'ticket.created' => 'Tiket dibuat',
        'ticket.create_self' => 'Tiket mandiri diajukan',
        'ticket.create_for_other' => 'Tiket dibuat atas nama pegawai',
        'ticket.claimed' => 'Tiket berhasil diklaim',
        'ticket.triaged' => 'Triase tiket selesai',
        'ticket.assign_tier_2' => 'Tiket ditugaskan ke Tier 2',
        'ticket.return_to_tier_1' => 'Tiket dikembalikan ke Tier 1',
        'ticket.category.changed' => 'Kategori tiket diubah',
        'ticket.priority.changed' => 'Prioritas tiket diubah',
        'ticket.comment.public' => 'Balasan ke pemohon dikirim',
        'ticket.comment.internal' => 'Catatan internal ditambahkan',
        'ticket.approval.requested' => 'Persetujuan tiket diminta',
        'ticket.approval.approved' => 'Persetujuan tiket diberikan',
        'ticket.approval.rejected' => 'Persetujuan tiket ditolak',
        'ticket.completed' => 'Tiket diselesaikan',
        'ticket.closed' => 'Tiket ditutup',
        'ticket.confirm' => 'Hasil tiket dikonfirmasi',
        'ticket.reopened' => 'Tiket dibuka kembali',
        'ticket.cancelled' => 'Tiket dibatalkan',
        'attachment.uploaded' => 'Lampiran diunggah',
        'attachment.download' => 'Lampiran diakses',
        'notification.read' => 'Notifikasi dibaca',
        'report.export' => 'Laporan diekspor',
    ];

    $targetLabels = [
        'User' => 'Pengguna',
        'Ticket' => 'Tiket',
        'Announcement' => 'Pengumuman',
        'Attachment' => 'Lampiran',
        'AttachmentPolicy' => 'Kebijakan lampiran',
        'ApproverAssignment' => 'Penetapan Approver',
        'Building' => 'Gedung',
        'Floor' => 'Lantai',
        'Room' => 'Ruangan',
        'Skill' => 'Keahlian',
        'ProblemCategory' => 'Kategori masalah',
        'ServiceType' => 'Jenis layanan',
        'ServiceFieldDefinition' => 'Field layanan',
        'SlaPolicy' => 'Kebijakan SLA',
        'OrganizationSetting' => 'Pengaturan organisasi',
        'WorkTeam' => 'Tim kerja',
        'DatabaseChangeControl' => 'Kontrol perubahan database',
        'ReportExport' => 'Ekspor laporan',
    ];

    $hasFilters = collect($filters)->contains(fn ($value): bool => filled($value));
    $formatJson = static fn ($value): string => json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
@endphp

@section('title', 'Audit Trail — '.$branding['application_name'])
@section('header_kicker', 'Administrasi')
@section('header_title', 'Audit Trail')

@section('content')
    <x-page-header
        eyebrow="Administrasi · Jejak aktivitas"
        title="Audit Trail"
        description="Riwayat perubahan data dan aktivitas akses yang tercatat oleh sistem."
    />

    <section class="ui-panel mt-6 overflow-hidden" aria-labelledby="audit-heading">
        <div class="flex flex-col gap-3 border-b border-[color:var(--tm-border-subtle)] px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div class="min-w-0">
                <h2 id="audit-heading" class="text-base font-bold tracking-tight text-[color:var(--tm-text)]">Daftar Aktivitas</h2>
                <p class="mt-1 text-sm text-[color:var(--tm-text-muted)]"><span class="font-semibold tabular-nums text-[color:var(--tm-text-secondary)]">{{ number_format($auditLogs->total(), 0, ',', '.') }}</span> catatan tersimpan</p>
            </div>
            <span class="inline-flex w-max items-center gap-1.5 rounded-[var(--tm-r-full)] border border-[color:var(--tm-border)] bg-[color:var(--tm-sunken)] px-3 py-1.5 text-xs font-bold text-[color:var(--tm-text-secondary)]">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V7a4.5 4.5 0 1 0-9 0v3.5M6 10.5h12a1 1 0 0 1 1 1V19a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1v-7.5a1 1 0 0 1 1-1Z" /></svg>
                Hanya-baca
            </span>
        </div>

        <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="grid gap-4 border-b border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-sunken)] px-5 py-5 sm:grid-cols-2 sm:px-6 lg:grid-cols-5" aria-label="Filter Audit Trail">
            <div>
                <label for="audit-action" class="ui-field-label">Aksi</label>
                <input id="audit-action" name="action" type="search" value="{{ $filters['action'] ?? '' }}" class="ui-input mt-2" placeholder="Contoh: admin.user.updated" autocomplete="off">
            </div>
            <div>
                <label for="audit-outcome" class="ui-field-label">Hasil</label>
                <select id="audit-outcome" name="outcome" class="ui-select mt-2">
                    <option value="">Semua hasil</option>
                    <option value="succeeded" @selected(($filters['outcome'] ?? '') === 'succeeded')>Berhasil</option>
                    <option value="denied" @selected(($filters['outcome'] ?? '') === 'denied')>Ditolak</option>
                </select>
            </div>
            <div>
                <label for="audit-actor" class="ui-field-label">Pelaku</label>
                <input id="audit-actor" name="actor" type="search" value="{{ $filters['actor'] ?? '' }}" class="ui-input mt-2" placeholder="Nama atau username" autocomplete="off">
            </div>
            <div>
                <label for="audit-from" class="ui-field-label">Dari tanggal</label>
                <input id="audit-from" name="from" type="date" value="{{ $filters['from'] ?? '' }}" class="ui-input mt-2">
            </div>
            <div>
                <label for="audit-to" class="ui-field-label">Sampai tanggal</label>
                <input id="audit-to" name="to" type="date" value="{{ $filters['to'] ?? '' }}" class="ui-input mt-2">
            </div>
            <div class="flex flex-wrap items-end gap-2 sm:col-span-2 lg:col-span-5">
                <button type="submit" class="ui-btn ui-btn-secondary">Terapkan filter</button>
                @if ($hasFilters)
                    <a href="{{ route('admin.audit-logs.index') }}" class="ui-btn ui-btn-ghost">Hapus filter</a>
                @endif
            </div>
        </form>

        <div class="px-5 py-5 sm:px-6">
            @if ($hasFilters)
                <p class="mb-4 flex flex-wrap items-center gap-1.5 text-xs text-[color:var(--tm-text-muted)]">
                    <svg class="h-3.5 w-3.5 shrink-0 text-[color:var(--tm-brand-600)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5h16l-6 7v6l-4 2v-8L4 5Z" /></svg>
                    Filter sedang digunakan.
                    <a href="{{ route('admin.audit-logs.index') }}" class="font-bold text-[color:var(--tm-brand-700)] underline-offset-2 hover:underline">Tampilkan semua catatan</a>
                </p>
            @endif

            @if ($auditLogs->isEmpty())
                <x-empty-state
                    :title="$hasFilters ? 'Tidak ada aktivitas yang cocok.' : 'Belum ada aktivitas yang tercatat.'"
                    :description="$hasFilters ? 'Coba ubah filter atau tampilkan semua catatan.' : 'Catatan audit akan muncul setelah ada aktivitas penting di aplikasi.'"
                    :action="$hasFilters ? route('admin.audit-logs.index') : null"
                    :action-label="$hasFilters ? 'Tampilkan semua catatan' : null"
                />
            @else
                <div class="hidden overflow-x-auto rounded-[var(--tm-r-md)] border border-[color:var(--tm-border-subtle)] md:block">
                    <table class="ui-table w-full min-w-[56rem]">
                        <caption class="sr-only">Daftar aktivitas Audit Trail</caption>
                        <thead>
                            <tr>
                                <th scope="col">Aksi</th>
                                <th scope="col">Pelaku</th>
                                <th scope="col" class="w-28">Hasil</th>
                                <th scope="col" class="w-44">Waktu</th>
                                <th scope="col">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($auditLogs as $auditLog)
                                @php
                                    $displayedAt = $auditLog->created_at?->timezone(config('app.timezone'));
                                    $actionLabel = $actionLabels[$auditLog->action] ?? 'Aktivitas sistem';
                                    $targetType = $auditLog->auditable_type ? class_basename($auditLog->auditable_type) : null;
                                    $targetLabel = $targetType ? ($targetLabels[$targetType] ?? $targetType) : 'Konteks umum';
                                    $actorName = $auditLog->user?->name ?? 'Tidak terautentikasi';
                                    $actorMeta = $auditLog->user?->username ?? 'Tanpa akun';
                                @endphp
                                <tr>
                                    <td class="align-top">
                                        <p class="font-bold text-[color:var(--tm-text)]">{{ $actionLabel }}</p>
                                        <code class="mt-1.5 inline-block break-all rounded-[var(--tm-r-xs)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-sunken)] px-1.5 py-0.5 text-xs text-[color:var(--tm-text-muted)]">{{ $auditLog->action }}</code>
                                    </td>
                                    <td class="align-top">
                                        <p class="font-semibold text-[color:var(--tm-text)]">{{ $actorName }}</p>
                                        <p class="mt-0.5 text-xs text-[color:var(--tm-text-faint)]">{{ $actorMeta }}</p>
                                    </td>
                                    <td class="align-top">
                                        <span class="ui-status {{ $auditLog->outcome === 'succeeded' ? 'ui-status-active' : 'ui-status-danger' }}">{{ $auditLog->outcome === 'succeeded' ? 'Berhasil' : 'Ditolak' }}</span>
                                    </td>
                                    <td class="whitespace-nowrap align-top tabular-nums text-[color:var(--tm-text-secondary)]">
                                        {{ $displayedAt?->format('d/m/Y H:i:s') ?? 'Waktu tidak tersedia' }}{{ $displayedAt ? ' WIB' : '' }}
                                    </td>
                                    <td class="max-w-md align-top text-[color:var(--tm-text-secondary)]">
                                        <p>{{ $auditLog->reason ?: 'Tidak ada keterangan tambahan.' }}</p>
                                        <p class="mt-2 text-xs text-[color:var(--tm-text-faint)]">Target: {{ $targetLabel }}{{ $auditLog->auditable_id ? ' #'.$auditLog->auditable_id : '' }}</p>
                                        @include('admin.audit-logs._details', ['auditLog' => $auditLog, 'formatJson' => $formatJson])
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="space-y-3 md:hidden">
                    @foreach ($auditLogs as $auditLog)
                        @php
                            $displayedAt = $auditLog->created_at?->timezone(config('app.timezone'));
                            $actionLabel = $actionLabels[$auditLog->action] ?? 'Aktivitas sistem';
                            $targetType = $auditLog->auditable_type ? class_basename($auditLog->auditable_type) : null;
                            $targetLabel = $targetType ? ($targetLabels[$targetType] ?? $targetType) : 'Konteks umum';
                            $actorName = $auditLog->user?->name ?? 'Tidak terautentikasi';
                            $actorMeta = $auditLog->user?->username ?? 'Tanpa akun';
                        @endphp
                        <article class="rounded-[var(--tm-r-md)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-sunken)] p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3 class="font-bold leading-5 text-[color:var(--tm-text)]">{{ $actionLabel }}</h3>
                                    <code class="mt-1.5 inline-block break-all rounded-[var(--tm-r-xs)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface)] px-1.5 py-0.5 text-xs text-[color:var(--tm-text-muted)]">{{ $auditLog->action }}</code>
                                </div>
                                <span class="ui-status {{ $auditLog->outcome === 'succeeded' ? 'ui-status-active' : 'ui-status-danger' }} shrink-0">{{ $auditLog->outcome === 'succeeded' ? 'Berhasil' : 'Ditolak' }}</span>
                            </div>
                            <dl class="mt-4 grid gap-3 rounded-[var(--tm-r-sm)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface)] p-3 text-xs sm:grid-cols-2">
                                <div><dt class="font-bold uppercase tracking-wide text-[color:var(--tm-text-faint)]">Pelaku</dt><dd class="mt-1 text-[color:var(--tm-text-secondary)]">{{ $actorName }}<span class="block text-[color:var(--tm-text-faint)]">{{ $actorMeta }}</span></dd></div>
                                <div><dt class="font-bold uppercase tracking-wide text-[color:var(--tm-text-faint)]">Waktu</dt><dd class="mt-1 tabular-nums text-[color:var(--tm-text-secondary)]">{{ $displayedAt?->format('d/m/Y H:i:s') ?? 'Waktu tidak tersedia' }}{{ $displayedAt ? ' WIB' : '' }}</dd></div>
                                <div class="sm:col-span-2"><dt class="font-bold uppercase tracking-wide text-[color:var(--tm-text-faint)]">Target</dt><dd class="mt-1 text-[color:var(--tm-text-secondary)]">{{ $targetLabel }}{{ $auditLog->auditable_id ? ' #'.$auditLog->auditable_id : '' }}</dd></div>
                            </dl>
                            <p class="mt-4 text-sm leading-6 text-[color:var(--tm-text-secondary)]">{{ $auditLog->reason ?: 'Tidak ada keterangan tambahan.' }}</p>
                            @include('admin.audit-logs._details', ['auditLog' => $auditLog, 'formatJson' => $formatJson])
                        </article>
                    @endforeach
                </div>

                <div class="mt-5 flex flex-col gap-3 border-t border-[color:var(--tm-border-subtle)] pt-4 text-sm text-[color:var(--tm-text-muted)] sm:flex-row sm:items-center sm:justify-between">
                    <p>Menampilkan <span class="font-semibold tabular-nums text-[color:var(--tm-text-secondary)]">{{ $auditLogs->firstItem() }}–{{ $auditLogs->lastItem() }}</span> dari <span class="font-semibold tabular-nums text-[color:var(--tm-text-secondary)]">{{ number_format($auditLogs->total(), 0, ',', '.') }}</span> catatan.</p>
                    @if ($auditLogs->hasPages())
                        <div>{{ $auditLogs->links() }}</div>
                    @endif
                </div>
            @endif
        </div>
    </section>
@endsection
