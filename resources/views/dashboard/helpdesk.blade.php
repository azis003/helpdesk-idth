@php
    $isTierOne = $agentDashboard['is_tier_one'];
    $assignedTickets = $agentDashboard['assigned_tickets'] ?? collect();
    $queueTickets = $agentDashboard['queue_tickets'] ?? collect();
    $todayLabel = now($periodTimezone)->locale('id')->translatedFormat('l, d M Y');
    $ticketNumber = static fn ($ticket): string => $ticket->ticket_number ?? 'Tiket #'.$ticket->id;
    $serviceName = static fn ($ticket): string => $ticket->service_type_name_snapshot
        ?: $ticket->serviceType?->name
        ?: $ticket->service_type_code_snapshot
        ?: 'Layanan belum dikategorikan';
    $requesterName = static fn ($ticket): string => $ticket->requester_name_snapshot
        ?: $ticket->requester?->name
        ?: 'Pemohon belum tercatat';
    $ticketAge = static function ($value) use ($periodTimezone): string {
        if ($value === null) {
            return 'Belum tercatat';
        }

        return $value->copy()->timezone($periodTimezone)->locale('id')->diffForHumans(now($periodTimezone), true);
    };
    $formatReportedAt = static function ($value) use ($periodTimezone): string {
        if ($value === null) {
            return 'Belum tercatat';
        }

        return $value->copy()->timezone($periodTimezone)->locale('id')->translatedFormat('l, d M Y H:i');
    };
@endphp

<div class="hd-dashboard" data-helpdesk-dashboard>
    <header class="hd-dashboard-header">
        <div>
            <p class="hd-eyebrow"><span class="hd-eyebrow-dot" aria-hidden="true"></span>Ruang Kerja</p>
            <h1 id="dashboard-title" class="hd-page-title">Dashboard</h1>
        </div>
        <div class="hd-dashboard-date">
            <div class="hd-date-label">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3.5" y="5" width="17" height="15" rx="1.5" /><path stroke-linecap="round" d="M7.5 3.5v3M16.5 3.5v3M3.5 9.5h17" /></svg>
                <span>{{ $todayLabel }}</span>
            </div>
        </div>
    </header>

    <div class="hd-period-toolbar">
        <details class="hd-period-menu">
            <summary class="hd-period-summary">Periode: {{ $periodLabel }}</summary>
            <form method="GET" action="{{ route('dashboard') }}" class="hd-period-form" aria-describedby="helpdesk-period-help">
                <div>
                    <label for="helpdesk-start-date">Mulai</label>
                    <input id="helpdesk-start-date" name="start_date" type="date" value="{{ $periodStart->format('Y-m-d') }}">
                </div>
                <div>
                    <label for="helpdesk-end-date">Sampai</label>
                    <input id="helpdesk-end-date" name="end_date" type="date" value="{{ $periodEnd->format('Y-m-d') }}">
                </div>
                <button type="submit">Terapkan</button>
                @if ($errors->has('start_date') || $errors->has('end_date'))
                    <p class="hd-inline-error" role="alert">{{ $errors->first('start_date') ?: $errors->first('end_date') }}</p>
                @endif
            </form>
        </details>
    </div>

    @if ($approverDashboard['visible'])
        <section class="hd-panel hd-approval-focus" aria-labelledby="helpdesk-approval-heading">
            <div class="hd-panel-header">
                <div class="hd-section-heading">
                    <span class="hd-section-icon hd-section-icon--warning" aria-hidden="true">!</span>
                    <div>
                        <h2 id="helpdesk-approval-heading" class="hd-section-title">Perlu Tindakan Saya</h2>
                        <p class="hd-section-description">Persetujuan tertunda yang perlu Anda putuskan sebagai Approver aktif.</p>
                    </div>
                </div>
                <a href="{{ route('approvals.index') }}" class="hd-link">Lihat semua</a>
            </div>
            <div class="hd-approval-list">
                @forelse ($approverDashboard['pending'] as $approval)
                    <a href="{{ route('tickets.show', $approval->ticket_id) }}" class="hd-approval-item">
                        <div>
                            <span class="hd-ticket-number">{{ $approval->ticket?->ticket_number ?? 'Tiket #'.$approval->ticket_id }}</span>
                            <h3>{{ $approval->ticket?->subject ?? 'Tiket tidak tersedia' }}</h3>
                        </div>
                        <div class="hd-approval-meta">
                            <span>{{ $approval->ticket?->requester?->name ?? 'Pemohon tidak tersedia' }}</span>
                            <span>Menunggu {{ $approval->requested_at?->copy()->locale('id')->diffForHumans(now($periodTimezone), true) ?? 'belum tercatat' }}</span>
                        </div>
                    </a>
                @empty
                    <p class="hd-empty-state">Belum ada persetujuan tertunda.</p>
                @endforelse
            </div>
        </section>
    @endif

    <section aria-labelledby="helpdesk-summary-heading">
        <h2 id="helpdesk-summary-heading" class="sr-only">Ringkasan pekerjaan Helpdesk</h2>
        <dl class="hd-stat-grid">
            <div class="hd-stat-card">
                <div class="hd-stat-card-head"><dt>Total Tiket</dt><span class="hd-stat-icon hd-stat-icon--blue" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4.5" y="5" width="15" height="14" rx="1.5" /><path stroke-linecap="round" d="M8 9h8M8 12.5h5M8 16h6" /></svg></span></div>
                <dd class="hd-stat-value">{{ $agentDashboard['summary']['total'] }}</dd>
            </div>
            <div class="hd-stat-card">
                <div class="hd-stat-card-head"><dt>Antrian Tiket</dt><span class="hd-stat-icon hd-stat-icon--blue" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" d="M5 6.5h14M5 12h14M5 17.5h9" /><path stroke-linecap="round" d="M18 17.5h.01" /></svg></span></div>
                <dd class="hd-stat-value">{{ $agentDashboard['summary']['queue'] }}</dd>
            </div>
            <div class="hd-stat-card">
                <div class="hd-stat-card-head"><dt>Dikerjakan Sendiri</dt><span class="hd-stat-icon hd-stat-icon--indigo" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="3" /><path stroke-linecap="round" d="M5.5 19a6.5 6.5 0 0 1 13 0" /></svg></span></div>
                <dd class="hd-stat-value">{{ $agentDashboard['summary']['self_handled'] }}</dd>
            </div>
            <div class="hd-stat-card">
                <div class="hd-stat-card-head"><dt>Dikerjakan Teknisi</dt><span class="hd-stat-icon hd-stat-icon--warning" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m14.5 6.5 3-3 3 3-3 3M16.5 8.5 9 16m-3.5-.5 3 3M4.5 19.5l2.5-1 1-2.5-3-3-2.5 1-1 2.5 3 3Z" /></svg></span></div>
                <dd class="hd-stat-value">{{ $agentDashboard['summary']['technician'] }}</dd>
            </div>
            <div class="hd-stat-card">
                <div class="hd-stat-card-head"><dt>Tiket Selesai</dt><span class="hd-stat-icon hd-stat-icon--success" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="8" /><path stroke-linecap="round" stroke-linejoin="round" d="m8.5 12 2.3 2.3 4.7-4.7" /></svg></span></div>
                <dd class="hd-stat-value">{{ $agentDashboard['summary']['finished'] }}</dd>
            </div>
        </dl>
    </section>

    <div class="hd-dashboard-grid">
        <div class="hd-dashboard-primary">
            <section class="hd-panel hd-queue-panel" aria-labelledby="helpdesk-queue-heading">
                <div class="hd-panel-header">
                    <div class="hd-section-heading">
                        <span class="hd-section-icon hd-section-icon--danger" aria-hidden="true">!</span>
                        <div>
                            <h2 id="helpdesk-queue-heading" class="hd-section-title">Antrian Tiket</h2>
                        </div>
                    </div>
                    <a href="{{ $isTierOne ? route('tickets.queue') : route('tickets.queue', ['tab' => 'mine']) }}" class="hd-link">Lihat semua</a>
                </div>

                @if ($isTierOne && $queueTickets->isNotEmpty())
                    <div class="hd-table-wrap">
                        <table class="hd-table">
                            <caption class="sr-only">Lima tiket pada antrian untuk Agen Tier 1</caption>
                            <thead>
                                <tr>
                                    <th scope="col">No Tiket</th>
                                    <th scope="col">Layanan</th>
                                    <th scope="col">Judul</th>
                                    <th scope="col">Pelapor</th>
                                    <th scope="col">Prioritas</th>
                                    <th scope="col">Waktu Lapor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($queueTickets as $ticket)
                                    @php($reportedAt = $ticket->submitted_at ?? $ticket->created_at)
                                    <tr>
                                        <td><a href="{{ route('tickets.show', $ticket) }}" class="hd-ticket-number">{{ $ticketNumber($ticket) }}</a></td>
                                        <td><span class="hd-cell-primary hd-cell-service">{{ $serviceName($ticket) }}</span><span class="hd-cell-secondary hd-cell-service">{{ $ticket->service_type_code_snapshot ?: 'Layanan TI' }}</span></td>
                                        <td><span class="hd-cell-primary hd-cell-subject">{{ $ticket->subject }}</span></td>
                                        <td>{{ $requesterName($ticket) }}</td>
                                        <td><x-priority-badge :priority="$ticket->priority" /></td>
                                        <td class="hd-reported-at"><time datetime="{{ $reportedAt?->copy()->timezone($periodTimezone)->toIso8601String() }}">{{ $formatReportedAt($reportedAt) }}</time></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="hd-mobile-list">
                        @foreach ($queueTickets as $ticket)
                            @php($reportedAt = $ticket->submitted_at ?? $ticket->created_at)
                            <a href="{{ route('tickets.show', $ticket) }}" class="hd-mobile-ticket-card">
                                <div class="hd-mobile-ticket-top"><span class="hd-ticket-number">{{ $ticketNumber($ticket) }}</span><x-priority-badge :priority="$ticket->priority" /></div>
                                <h3>{{ $ticket->subject }}</h3>
                                <p>{{ $serviceName($ticket) }}{{ $ticket->service_type_code_snapshot ? ' · '.$ticket->service_type_code_snapshot : '' }}</p>
                                <div class="hd-mobile-ticket-meta"><span>{{ $requesterName($ticket) }}</span><time class="hd-reported-at" datetime="{{ $reportedAt?->copy()->timezone($periodTimezone)->toIso8601String() }}">{{ $formatReportedAt($reportedAt) }}</time></div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="hd-empty-state hd-empty-state--panel">
                        <span class="hd-empty-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M5 6.5h14v11H5zM8 9.5h8M8 13h5" /></svg></span>
                        <h3>{{ $isTierOne ? 'Belum ada tiket terbaru pada antrean ini.' : 'Belum ada tiket yang ditugaskan.' }}</h3>
                        <p>{{ $isTierOne ? 'Tiket dari Pemohon yang belum diambil akan muncul di sini setelah tercatat.' : 'Tiket Tier 2 yang ditugaskan kepada Anda akan muncul di sini.' }}</p>
                        <a href="{{ $isTierOne ? route('tickets.queue') : route('tickets.queue', ['tab' => 'mine']) }}" class="hd-secondary-button">Buka Antrian Tiket</a>
                    </div>
                @endif
            </section>

        </div>

        <aside class="hd-dashboard-sidebar" aria-label="Ringkasan tugas dan informasi Helpdesk">
            <section class="hd-panel" aria-labelledby="helpdesk-assigned-heading">
                <div class="hd-panel-header">
                    <div class="hd-section-heading">
                        <span class="hd-section-icon hd-section-icon--blue" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="3" /><path stroke-linecap="round" d="M5.5 19a6.5 6.5 0 0 1 13 0" /></svg></span>
                        <h2 id="helpdesk-assigned-heading" class="hd-section-title">Tiket Saya</h2>
                    </div>
                    <span class="hd-panel-count">{{ $agentDashboard['assigned_count'] }} Aktif</span>
                </div>
                <div class="hd-task-list">
                    @forelse ($assignedTickets as $ticket)
                        <a href="{{ route('tickets.show', $ticket) }}" class="hd-task-card">
                            <div class="hd-task-card-top"><span class="hd-ticket-number">{{ $ticketNumber($ticket) }}</span><x-status-badge :status="$ticket->status" /></div>
                            <h3>{{ $ticket->subject }}</h3>
                            <p class="hd-task-meta"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="8" /><path stroke-linecap="round" d="M12 8v4l2.5 2.5" /></svg>Diperbarui {{ $ticketAge($ticket->updated_at) }} lalu</p>
                        </a>
                    @empty
                        <p class="hd-empty-state hd-empty-state--compact">Belum ada tiket yang ditugaskan kepada Anda.</p>
                    @endforelse
                </div>
                <a href="{{ route('tickets.queue', ['tab' => 'mine']) }}" class="hd-outline-button">Buka Tiket Saya</a>
            </section>

            <section class="hd-panel" aria-labelledby="helpdesk-announcements-heading">
                <div class="hd-panel-header">
                    <div class="hd-section-heading">
                        <span class="hd-section-icon hd-section-icon--blue" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 11.5h3l8-4v9l-8-4h-3a1 1 0 0 1-1-1v-1a1 1 0 0 1 1-1Z" /><path stroke-linecap="round" d="M8 16.5 9.5 20h2L10 16.5M18.5 10a3 3 0 0 1 0 4" /></svg></span>
                        <h2 id="helpdesk-announcements-heading" class="hd-section-title">Pengumuman Internal</h2>
                    </div>
                </div>
                <div class="hd-announcement-list">
                    @forelse ($announcements as $announcement)
                        <article class="hd-announcement-item">
                            <span class="hd-announcement-dot" aria-hidden="true"></span>
                            <time datetime="{{ $announcement->starts_at?->toIso8601String() }}">{{ $formatDate($announcement->starts_at) }}</time>
                            <h3>{{ $announcement->title }}</h3>
                            <p>{{ $announcement->body }}</p>
                        </article>
                    @empty
                        <p class="hd-empty-state hd-empty-state--compact">Belum ada pengumuman internal yang aktif.</p>
                    @endforelse
                </div>
            </section>
        </aside>
    </div>

</div>
