<?php

namespace App\Services;

use App\Enums\TicketStatus;
use App\Models\Attachment;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\TicketFieldValue;
use App\Models\TicketStatusHistory;
use App\ViewModels\RequesterTicketMessage;
use App\ViewModels\RequesterTicketView;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Builds the Pemohon-facing read model for a ticket.
 *
 * All wording, ordering and progress logic lives here so the Blade view stays
 * a thin presentation layer instead of a second place where workflow rules get
 * re-implemented. This mirrors the projection already used for Ketua Tim Kerja.
 */
class RequesterTicketPresenter
{
    /** Milestones a requester actually follows, in order. */
    private const JOURNEY = [
        ['label' => 'Diajukan', 'caption' => 'Tiket diterima Tim TI'],
        ['label' => 'Ditangani', 'caption' => 'Tim TI mengerjakan permintaan'],
        ['label' => 'Konfirmasi', 'caption' => 'Anda meninjau hasilnya'],
        ['label' => 'Selesai', 'caption' => 'Tiket ditutup'],
    ];

    /** Statuses that end a ticket before it can reach the closed milestone. */
    private const ENDED_STATUSES = [
        TicketStatus::Ditolak,
        TicketStatus::TidakDisetujui,
        TicketStatus::Dibatalkan,
    ];

    /**
     * @param  array<string, mixed>|null  $slaMetrics
     */
    public function present(Ticket $ticket, ?array $slaMetrics = null, ?string $decisionNote = null): RequesterTicketView
    {
        [$headline, $narrative] = $this->narrativeFor($ticket);
        [$noticeTitle, $noticeBody] = $this->noticeFor($ticket, $decisionNote);

        return new RequesterTicketView(
            id: (int) $ticket->getKey(),
            label: filled($ticket->ticket_number) ? (string) $ticket->ticket_number : 'Tiket #'.$ticket->getKey(),
            subject: (string) $ticket->subject,
            description: filled($ticket->description) ? (string) $ticket->description : 'Deskripsi belum tersedia.',
            status: $ticket->status,
            priority: $ticket->priority,
            statusHeadline: $headline,
            statusNarrative: $narrative,
            noticeTitle: $noticeTitle,
            noticeBody: $noticeBody,
            solution: filled($ticket->solution) ? (string) $ticket->solution : null,
            journey: $this->journeyFor($ticket),
            sla: $this->slaFor($ticket, $slaMetrics),
            facts: $this->factsFor($ticket),
            submittedFields: $this->submittedFieldsFor($ticket),
            attachments: $this->attachmentsFor($ticket),
            timeline: $this->timelineFor($ticket),
            messages: $this->messagesFor($ticket),
        );
    }

    /**
     * Plain-language answer to "what is happening with my ticket right now?".
     *
     * @return array{0: string, 1: string}
     */
    private function narrativeFor(Ticket $ticket): array
    {
        return match ($ticket->status) {
            TicketStatus::Baru => [
                'Tiket menunggu diproses',
                'Permintaan Anda sudah masuk dan menunggu petugas Tim TI mengambil tiket ini.',
            ],
            TicketStatus::Diproses => [
                'Tiket sedang ditinjau',
                'Petugas sedang memeriksa detail permintaan dan menyiapkan langkah penanganan.',
            ],
            TicketStatus::Dikerjakan => [
                'Tiket sedang dikerjakan',
                'Petugas sedang menangani permintaan Anda. Anda akan dihubungi bila ada informasi yang dibutuhkan.',
            ],
            TicketStatus::MenungguPersetujuan => [
                'Menunggu persetujuan',
                'Permintaan ini memerlukan keputusan pemberi persetujuan sebelum pekerjaan dilanjutkan.',
            ],
            TicketStatus::MenungguPemohon => [
                'Menunggu balasan Anda',
                $this->requesterWaitNarrative($ticket),
            ],
            TicketStatus::MenungguPihakKetiga => [
                'Menunggu pihak ketiga',
                'Penyelesaian menunggu tindak lanjut dari pihak di luar Tim TI. Hitungan waktu layanan dijeda sementara.',
            ],
            TicketStatus::MenungguKonfirmasi => [
                'Hasil siap Anda tinjau',
                $this->confirmationNarrative($ticket),
            ],
            TicketStatus::Ditutup => [
                'Tiket selesai',
                $ticket->closed_reason === 'auto_closed'
                    ? 'Tiket ditutup otomatis karena batas waktu konfirmasi terlewati.'
                    : 'Terima kasih. Hasil pekerjaan sudah Anda konfirmasi dan tiket ditutup.',
            ],
            TicketStatus::Ditolak => [
                'Permintaan tidak dilanjutkan',
                'Tim TI tidak melanjutkan permintaan ini. Alasan lengkap tercantum di bawah.',
            ],
            TicketStatus::TidakDisetujui => [
                'Permintaan tidak disetujui',
                'Permintaan tidak mendapat persetujuan. Silakan ajukan tiket baru bila kebutuhan berubah.',
            ],
            TicketStatus::Dibatalkan => [
                'Tiket dibatalkan',
                'Tiket ini dibatalkan dan tidak diproses lebih lanjut.',
            ],
            default => [
                'Status tiket diperbarui',
                'Tim TI akan melanjutkan tiket ini sesuai statusnya.',
            ],
        };
    }

    private function requesterWaitNarrative(Ticket $ticket): string
    {
        $due = $ticket->waits
            ->first(fn ($wait): bool => $wait->ended_at === null)?->due_at;

        return $due === null
            ? 'Tim TI membutuhkan informasi tambahan dari Anda untuk dapat melanjutkan tiket ini.'
            : 'Mohon lengkapi informasi yang diminta sebelum '.$this->dateTime($due).'.';
    }

    private function confirmationNarrative(Ticket $ticket): string
    {
        return $ticket->confirmation_due_at === null
            ? 'Silakan tinjau hasil pekerjaan lalu konfirmasi apakah sudah sesuai kebutuhan Anda.'
            : 'Silakan konfirmasi sebelum '.$this->dateTime($ticket->confirmation_due_at).'. Setelah itu tiket ditutup otomatis.';
    }

    /**
     * @return array{0: ?string, 1: ?string}
     */
    private function noticeFor(Ticket $ticket, ?string $decisionNote): array
    {
        if ($ticket->status === TicketStatus::Ditolak && filled($ticket->rejection_reason)) {
            return ['Alasan penolakan', (string) $ticket->rejection_reason];
        }

        if ($ticket->status === TicketStatus::TidakDisetujui && filled($decisionNote)) {
            return ['Catatan keputusan', (string) $decisionNote];
        }

        return [null, null];
    }

    /**
     * @return list<array{label: string, caption: string, state: string}>
     */
    private function journeyFor(Ticket $ticket): array
    {
        if (in_array($ticket->status, self::ENDED_STATUSES, true)) {
            return [];
        }

        $reached = match ($ticket->status) {
            TicketStatus::Baru => 0,
            TicketStatus::Diproses,
            TicketStatus::Dikerjakan,
            TicketStatus::MenungguPersetujuan,
            TicketStatus::MenungguPemohon,
            TicketStatus::MenungguPihakKetiga => 1,
            TicketStatus::MenungguKonfirmasi => 2,
            TicketStatus::Ditutup => 3,
            default => 0,
        };

        $isClosed = $ticket->status === TicketStatus::Ditutup;
        $steps = [];

        foreach (self::JOURNEY as $index => $step) {
            $steps[] = [
                'label' => $step['label'],
                'caption' => $step['caption'],
                'state' => match (true) {
                    $index < $reached => 'done',
                    $index === $reached => $isClosed ? 'done' : 'current',
                    default => 'upcoming',
                },
            ];
        }

        return $steps;
    }

    /**
     * Turns raw SLA metrics into one sentence a requester can act on.
     *
     * @param  array<string, mixed>|null  $metrics
     * @return array{headline: string, detail: string, target: string}|null
     */
    private function slaFor(Ticket $ticket, ?array $metrics): ?array
    {
        if ($metrics === null || ($metrics['uses_sla'] ?? false) !== true) {
            return null;
        }

        if ($ticket->status === TicketStatus::Ditutup || in_array($ticket->status, self::ENDED_STATUSES, true)) {
            return null;
        }

        $targetDays = $metrics['target_working_days'] ?? null;
        $deadline = $metrics['deadline_at'] ?? null;
        $target = $deadline instanceof Carbon
            ? $this->dateOnly($deadline)
            : ($targetDays !== null ? $targetDays.' hari kerja' : 'sesuai standar layanan');

        if (($metrics['paused'] ?? false) === true) {
            return [
                'headline' => 'Hitungan waktu sedang dijeda',
                'detail' => 'Waktu penyelesaian berhenti sementara selama tiket menunggu pihak lain.',
                'target' => $target,
            ];
        }

        if (($metrics['overdue'] ?? false) === true) {
            return [
                'headline' => 'Melewati target waktu penyelesaian',
                'detail' => 'Tim TI memprioritaskan penyelesaian tiket ini.',
                'target' => $target,
            ];
        }

        return [
            'headline' => 'Sisa waktu '.$this->humanMinutes((int) ($metrics['remaining_minutes'] ?? 0)),
            'detail' => ($metrics['near_limit'] ?? false) === true
                ? 'Tiket mendekati batas waktu penyelesaian.'
                : 'Perkiraan penyelesaian masih sesuai target layanan.',
            'target' => $target,
        ];
    }

    private function humanMinutes(int $minutes): string
    {
        $minutes = max(0, $minutes);
        $hours = intdiv($minutes, 60);
        $rest = $minutes % 60;

        if ($hours === 0) {
            return $rest.' menit kerja';
        }

        if ($rest === 0) {
            return $hours.' jam kerja';
        }

        return $hours.' jam '.$rest.' menit kerja';
    }

    /**
     * @return list<array{label: string, value: string}>
     */
    private function factsFor(Ticket $ticket): array
    {
        $facts = [
            ['label' => 'Nomor tiket', 'value' => filled($ticket->ticket_number) ? (string) $ticket->ticket_number : 'Belum tersedia'],
            ['label' => 'Layanan', 'value' => $this->serviceLabel($ticket)],
        ];

        $location = $this->locationLabel($ticket);

        if ($location !== null) {
            $facts[] = ['label' => 'Lokasi', 'value' => $location];
        }

        $facts[] = ['label' => 'Diajukan', 'value' => $this->dateTime($ticket->submitted_at ?? $ticket->created_at)];
        $facts[] = ['label' => 'Pembaruan terakhir', 'value' => $this->dateTime($ticket->updated_at)];
        $facts[] = ['label' => 'Ditangani oleh', 'value' => $ticket->assignee?->name ?: 'Menunggu petugas'];

        if ($ticket->is_self_created === false) {
            $facts[] = ['label' => 'Dicatat oleh', 'value' => $ticket->creator?->name ?: 'Tim TI'];
        }

        if ((int) $ticket->reopen_count > 0) {
            $facts[] = ['label' => 'Dibuka kembali', 'value' => $ticket->reopen_count.' kali'];
        }

        return $facts;
    }

    private function serviceLabel(Ticket $ticket): string
    {
        $name = $ticket->service_type_name_snapshot ?: 'Layanan tidak tercatat';
        $variant = $ticket->service_type_variant_label_snapshot;

        return filled($variant) ? $name.' - '.$variant : (string) $name;
    }

    /**
     * Location was computed but never rendered in the previous view; the
     * requester now actually sees where the ticket was reported.
     */
    private function locationLabel(Ticket $ticket): ?string
    {
        $parts = array_filter([
            $ticket->building_name_snapshot,
            $ticket->floor_name_snapshot,
            $ticket->room_name_snapshot,
        ], static fn ($part): bool => filled($part));

        return $parts === [] ? null : implode(' - ', $parts);
    }

    /**
     * @return list<array{label: string, value: string}>
     */
    private function submittedFieldsFor(Ticket $ticket): array
    {
        return $ticket->fieldValues
            ->map(fn (TicketFieldValue $field): array => [
                'label' => (string) $field->label_snapshot,
                'value' => $this->fieldValue($field),
            ])
            ->values()
            ->all();
    }

    private function fieldValue(TicketFieldValue $field): string
    {
        $value = $field->value;

        if (is_array($value)) {
            $value = implode(', ', $value);
        }

        if ($field->field_type_snapshot === 'boolean') {
            return filled($value) && $value !== '0' ? 'Ya' : 'Tidak';
        }

        return filled($value) ? (string) $value : 'Tidak diisi';
    }

    /**
     * Lampiran yang tampil di kartu utama hanya berasal dari pengajuan tiket.
     * Lampiran komentar diproyeksikan bersama pesan terkait.
     *
     * @return list<array{name: string, meta: string, url: string}>
     */
    private function attachmentsFor(Ticket $ticket): array
    {
        return $ticket->attachments
            ->filter(static fn (Attachment $attachment): bool => $attachment->ticket_comment_id === null)
            ->map(fn (Attachment $attachment): array => [
                'name' => (string) $attachment->original_name,
                'meta' => ($attachment->type_label_snapshot ?: 'Lampiran').' - '.$this->humanSize((int) $attachment->size_bytes),
                'url' => route('attachments.download', $attachment),
            ])
            ->values()
            ->all();
    }

    private function humanSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1, ',', '.').' MB';
        }

        return number_format(max(1, (int) round($bytes / 1024)), 0, ',', '.').' KB';
    }

    /**
     * @return Collection<int, RequesterTicketMessage>
     */
    private function messagesFor(Ticket $ticket): Collection
    {
        $requesterId = (int) $ticket->requester_id;

        return $ticket->comments
            ->map(fn (TicketComment $comment): RequesterTicketMessage => new RequesterTicketMessage(
                authorName: $comment->author?->name ?: 'Tim TI',
                body: (string) $comment->body,
                createdAt: $comment->created_at,
                fromRequester: (int) $comment->author_id === $requesterId,
                attachments: $comment->attachments
                    ->map(fn (Attachment $attachment): array => [
                        'name' => (string) $attachment->original_name,
                        'url' => route('attachments.download', $attachment),
                    ])
                    ->values()
                    ->all(),
            ))
            ->values();
    }

    /**
     * Requester-safe history: status transitions only.
     * Attachments remain available in the dedicated attachments section.
     *
     * @return list<array{title: string, description: string, occurredAt: ?Carbon, tone: string}>
     */
    private function timelineFor(Ticket $ticket): array
    {
        $events = [];

        foreach ($ticket->statusHistories as $history) {
            $event = $this->statusEvent($history);

            if ($event !== null) {
                $events[] = $event;
            }
        }

        usort($events, static function (array $left, array $right): int {
            return ($right['occurredAt']?->getTimestamp() ?? 0) <=> ($left['occurredAt']?->getTimestamp() ?? 0);
        });

        return $events;
    }

    /**
     * @return array{title: string, description: string, occurredAt: ?Carbon, tone: string}|null
     */
    private function statusEvent(TicketStatusHistory $history): ?array
    {
        $reason = filled($history->reason) ? (string) $history->reason : null;

        [$title, $description, $tone] = match (true) {
            $history->action === 'ticket.completed' => ['Hasil pekerjaan dikirim', 'Tim TI menyelesaikan pekerjaan dan meminta konfirmasi Anda.', 'success'],
            $history->action === 'ticket.closed' => ['Tiket ditutup', 'Hasil pekerjaan telah dikonfirmasi.', 'success'],
            $history->action === 'ticket.auto_closed' => ['Tiket ditutup otomatis', 'Batas waktu konfirmasi terlewati.', 'neutral'],
            $history->action === 'ticket.confirmation.not_satisfied' => ['Anda meminta perbaikan', $reason ?? 'Tiket kembali dikerjakan Tim TI.', 'attention'],
            $history->action === 'ticket.reopened' => ['Tiket dibuka kembali', $reason ?? 'Tim TI melanjutkan penanganan.', 'attention'],
            $history->to_status === TicketStatus::Baru => ['Tiket diajukan', 'Permintaan masuk ke antrean Tim TI.', 'neutral'],
            $history->to_status === TicketStatus::Diproses => ['Tiket mulai ditinjau', 'Petugas memeriksa detail permintaan.', 'progress'],
            $history->to_status === TicketStatus::Dikerjakan => ['Tiket mulai dikerjakan', 'Tim TI menangani permintaan Anda.', 'progress'],
            $history->to_status === TicketStatus::MenungguPersetujuan => ['Menunggu persetujuan', 'Permintaan diteruskan untuk mendapat keputusan.', 'attention'],
            $history->to_status === TicketStatus::MenungguPemohon => ['Tim TI meminta informasi', 'Balasan Anda dibutuhkan untuk melanjutkan.', 'attention'],
            $history->to_status === TicketStatus::MenungguPihakKetiga => ['Menunggu pihak ketiga', 'Penyelesaian menunggu pihak di luar Tim TI.', 'attention'],
            $history->to_status === TicketStatus::MenungguKonfirmasi => ['Hasil menunggu konfirmasi', 'Silakan tinjau hasil pekerjaan.', 'attention'],
            $history->to_status === TicketStatus::Ditolak => ['Permintaan ditolak', $reason ?? 'Tim TI tidak melanjutkan permintaan ini.', 'danger'],
            $history->to_status === TicketStatus::TidakDisetujui => ['Permintaan tidak disetujui', $reason ?? 'Permintaan tidak mendapat persetujuan.', 'danger'],
            $history->to_status === TicketStatus::Dibatalkan => ['Tiket dibatalkan', $reason ?? 'Tiket dibatalkan oleh Pemohon.', 'neutral'],
            default => [null, null, null],
        };

        if ($title === null) {
            return null;
        }

        return [
            'title' => $title,
            'description' => $description,
            'occurredAt' => $history->occurred_at,
            'tone' => $tone,
        ];
    }

    private function dateTime(?Carbon $value): string
    {
        return $value?->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i') ?? 'Belum tersedia';
    }

    private function dateOnly(Carbon $value): string
    {
        return $value->copy()->timezone(config('app.timezone'))->locale('id')->translatedFormat('d F Y');
    }
}
