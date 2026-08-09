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

    <section class="mt-7 overflow-hidden rounded-lg border border-[#d7dde0] bg-white shadow-[0_2px_8px_rgba(36,57,67,0.06)]" aria-labelledby="audit-heading">
        <div class="flex flex-col gap-3 bg-[#075998] px-5 py-5 text-white sm:flex-row sm:items-center sm:justify-between sm:px-8">
            <div>
                <h2 id="audit-heading" class="text-xl font-extrabold tracking-tight">Daftar Aktivitas</h2>
                <p class="mt-1 text-sm text-[#d9edf6]">{{ number_format($auditLogs->total(), 0, ',', '.') }} catatan tersimpan</p>
            </div>
            <span class="inline-flex w-max items-center rounded-full border border-white/30 bg-white/10 px-3 py-1.5 text-xs font-bold text-white">Hanya-baca</span>
        </div>

        <div class="px-5 py-5 sm:px-8">
            <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="grid gap-4 border-b border-[#e5eaed] pb-5 sm:grid-cols-2 lg:grid-cols-5" aria-label="Filter Audit Trail">
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

            @if ($hasFilters)
                <p class="mt-4 text-xs text-[#718088]">Filter sedang digunakan. <a href="{{ route('admin.audit-logs.index') }}" class="font-bold text-[#147a79] hover:underline">Tampilkan semua catatan</a></p>
            @endif

            @if ($auditLogs->isEmpty())
                <div class="mt-5">
                    <x-empty-state
                        :title="$hasFilters ? 'Tidak ada aktivitas yang cocok.' : 'Belum ada aktivitas yang tercatat.'"
                        :description="$hasFilters ? 'Coba ubah filter atau tampilkan semua catatan.' : 'Catatan audit akan muncul setelah ada aktivitas penting di aplikasi.'"
                        :action="$hasFilters ? route('admin.audit-logs.index') : null"
                        :action-label="$hasFilters ? 'Tampilkan semua catatan' : null"
                    />
                </div>
            @else
                <div class="mt-5 hidden overflow-x-auto rounded-lg border border-[#cfd6da] md:block">
                    <table class="min-w-[900px] w-full border-collapse text-left text-sm">
                        <caption class="sr-only">Daftar aktivitas Audit Trail</caption>
                        <thead class="bg-[#fbfcfd] text-[#34495a]">
                            <tr>
                                <th scope="col" class="border-b border-[#cfd6da] px-4 py-3 text-xs font-extrabold uppercase tracking-wide">Aksi</th>
                                <th scope="col" class="border-b border-[#cfd6da] px-4 py-3 text-xs font-extrabold uppercase tracking-wide">Pelaku</th>
                                <th scope="col" class="border-b border-[#cfd6da] px-4 py-3 text-xs font-extrabold uppercase tracking-wide">Hasil</th>
                                <th scope="col" class="border-b border-[#cfd6da] px-4 py-3 text-xs font-extrabold uppercase tracking-wide">Waktu</th>
                                <th scope="col" class="border-b border-[#cfd6da] px-4 py-3 text-xs font-extrabold uppercase tracking-wide">Keterangan</th>
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
                                <tr class="odd:bg-[#f8fafb] even:bg-white hover:bg-[#eef7fc]">
                                    <td class="border-b border-[#e5eaed] px-4 py-5 align-top">
                                        <p class="font-bold text-[#112b49]">{{ $actionLabel }}</p>
                                        <code class="mt-1 block break-all text-xs text-[#78909a]">{{ $auditLog->action }}</code>
                                    </td>
                                    <td class="border-b border-[#e5eaed] px-4 py-5 align-top">
                                        <p class="font-semibold text-[#172d45]">{{ $actorName }}</p>
                                        <p class="mt-1 text-xs text-[#78909a]">{{ $actorMeta }}</p>
                                    </td>
                                    <td class="border-b border-[#e5eaed] px-4 py-5 align-top">
                                        <span class="ui-status {{ $auditLog->outcome === 'succeeded' ? 'ui-status-active' : 'ui-status-danger' }}">{{ $auditLog->outcome === 'succeeded' ? 'Berhasil' : 'Ditolak' }}</span>
                                    </td>
                                    <td class="whitespace-nowrap border-b border-[#e5eaed] px-4 py-5 align-top text-[#526f79]">
                                        {{ $displayedAt?->format('d/m/Y H:i:s') ?? 'Waktu tidak tersedia' }}{{ $displayedAt ? ' WIB' : '' }}
                                    </td>
                                    <td class="max-w-md border-b border-[#e5eaed] px-4 py-5 align-top text-[#526f79]">
                                        <p>{{ $auditLog->reason ?: 'Tidak ada keterangan tambahan.' }}</p>
                                        <p class="mt-2 text-xs text-[#78909a]">Target: {{ $targetLabel }}{{ $auditLog->auditable_id ? ' #'.$auditLog->auditable_id : '' }}</p>
                                        @include('admin.audit-logs._details', ['auditLog' => $auditLog, 'formatJson' => $formatJson])
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 space-y-3 md:hidden">
                    @foreach ($auditLogs as $auditLog)
                        @php
                            $displayedAt = $auditLog->created_at?->timezone(config('app.timezone'));
                            $actionLabel = $actionLabels[$auditLog->action] ?? 'Aktivitas sistem';
                            $targetType = $auditLog->auditable_type ? class_basename($auditLog->auditable_type) : null;
                            $targetLabel = $targetType ? ($targetLabels[$targetType] ?? $targetType) : 'Konteks umum';
                            $actorName = $auditLog->user?->name ?? 'Tidak terautentikasi';
                            $actorMeta = $auditLog->user?->username ?? 'Tanpa akun';
                        @endphp
                        <article class="rounded-lg border border-[#dfe8ec] bg-[#fbfcfd] p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3 class="font-bold leading-5 text-[#112b49]">{{ $actionLabel }}</h3>
                                    <code class="mt-1 block break-all text-xs text-[#78909a]">{{ $auditLog->action }}</code>
                                </div>
                                <span class="ui-status {{ $auditLog->outcome === 'succeeded' ? 'ui-status-active' : 'ui-status-danger' }}">{{ $auditLog->outcome === 'succeeded' ? 'Berhasil' : 'Ditolak' }}</span>
                            </div>
                            <dl class="mt-4 grid gap-3 rounded-lg bg-white p-3 text-xs sm:grid-cols-2">
                                <div><dt class="font-bold uppercase tracking-wide text-[#78909a]">Pelaku</dt><dd class="mt-1 text-[#526f79]">{{ $actorName }}<span class="block text-[#78909a]">{{ $actorMeta }}</span></dd></div>
                                <div><dt class="font-bold uppercase tracking-wide text-[#78909a]">Waktu</dt><dd class="mt-1 text-[#526f79]">{{ $displayedAt?->format('d/m/Y H:i:s') ?? 'Waktu tidak tersedia' }}{{ $displayedAt ? ' WIB' : '' }}</dd></div>
                                <div class="sm:col-span-2"><dt class="font-bold uppercase tracking-wide text-[#78909a]">Target</dt><dd class="mt-1 text-[#526f79]">{{ $targetLabel }}{{ $auditLog->auditable_id ? ' #'.$auditLog->auditable_id : '' }}</dd></div>
                            </dl>
                            <p class="mt-4 text-sm leading-6 text-[#526f79]">{{ $auditLog->reason ?: 'Tidak ada keterangan tambahan.' }}</p>
                            @include('admin.audit-logs._details', ['auditLog' => $auditLog, 'formatJson' => $formatJson])
                        </article>
                    @endforeach
                </div>

                <div class="mt-4 flex flex-col gap-3 text-sm text-[#718088] sm:flex-row sm:items-center sm:justify-between">
                    <p>Menampilkan {{ $auditLogs->firstItem() }}–{{ $auditLogs->lastItem() }} dari {{ number_format($auditLogs->total(), 0, ',', '.') }} catatan.</p>
                    @if ($auditLogs->hasPages())
                        <div>{{ $auditLogs->links() }}</div>
                    @endif
                </div>
            @endif
        </div>
    </section>
@endsection
