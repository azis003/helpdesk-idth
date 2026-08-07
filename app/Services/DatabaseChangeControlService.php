<?php

namespace App\Services;

use App\Models\Attachment;
use App\Models\DatabaseChangeControl;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DatabaseChangeControlService
{
    public const SVC_DATA_EXPORT = 'SVC-02';

    public const SVC_DATABASE_CHANGE = 'SVC-03';

    /** @var list<string> */
    public const REQUIRED_EVIDENCE_TYPES = [
        'change_script',
        'rollback_script',
        'backup_evidence',
    ];

    public function __construct(
        private readonly DatabaseManager $database,
        private readonly DomainAuthorization $authorization,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function startExecution(User $actor, Ticket $ticket): DatabaseChangeControl
    {
        $this->authorization->authorize(
            $actor,
            'startDatabaseChange',
            $ticket,
            'ticket.database_change.execution',
        );

        $now = Carbon::now(config('app.timezone'));
        $failure = null;

        $control = $this->database->transaction(function () use ($actor, $ticket, $now, &$failure): ?DatabaseChangeControl {
            $lockedTicket = Ticket::query()
                ->whereKey($ticket->getKey())
                ->lockForUpdate()
                ->first();

            if ($lockedTicket === null) {
                $failure = 'Tiket tidak ditemukan.';
                $this->auditLogger->denied($actor, 'ticket.database_change.execution_denied', $ticket, $failure);

                return null;
            }

            if (! Gate::forUser($actor)->allows('startDatabaseChange', $lockedTicket)) {
                $failure = 'Mulai Eksekusi hanya tersedia bagi pelaksana SVC-03 pada status Dikerjakan.';
                $control = $this->controlFor($lockedTicket, true);
                $this->recordDenied($actor, $lockedTicket, $control, 'execution_denied', $failure, $now);

                return null;
            }

            $control = $this->controlFor($lockedTicket, true);

            if ($control->executionStarted()) {
                $failure = 'Eksekusi SVC-03 sudah dimulai sebelumnya.';
                $this->recordDenied($actor, $lockedTicket, $control, 'execution_denied', $failure, $now);

                return null;
            }

            $failure = $this->executionEvidenceFailure($lockedTicket);

            if ($failure !== null) {
                $this->recordDenied($actor, $lockedTicket, $control, 'execution_denied', $failure, $now);

                return null;
            }

            $evidence = $this->evidenceAttachments($lockedTicket);
            $control->forceFill([
                'execution_started_by_id' => $actor->getKey(),
                'execution_started_at' => $now,
            ])->save();
            $control->histories()->create([
                'ticket_id' => $lockedTicket->getKey(),
                'action' => 'execution_started',
                'actor_id' => $actor->getKey(),
                'metadata' => [
                    'execution_started_at' => $now->toIso8601String(),
                    'evidence_attachment_ids' => $evidence->pluck('id')->values()->all(),
                    'evidence_types' => self::REQUIRED_EVIDENCE_TYPES,
                ],
                'occurred_at' => $now,
            ]);
            $this->auditLogger->succeeded(
                $actor,
                'ticket.database_change.execution_started',
                $lockedTicket,
                'Eksekusi SVC-03 dimulai setelah seluruh bukti perubahan tersedia.',
                null,
                [
                    'control_id' => $control->getKey(),
                    'execution_started_by_id' => $actor->getKey(),
                    'execution_started_at' => $now->toIso8601String(),
                    'evidence_attachment_ids' => $evidence->pluck('id')->values()->all(),
                ],
            );

            return $control->fresh(['executionStartedBy', 'verifier']);
        });

        if ($control === null) {
            throw ValidationException::withMessages([
                'database_change' => $failure ?: 'Kontrol perubahan database belum dapat dimulai.',
            ]);
        }

        return $control;
    }

    public function verify(User $actor, Ticket $ticket, ?string $result, ?string $notes): DatabaseChangeControl
    {
        $this->authorization->authorize(
            $actor,
            'verifyDatabaseChange',
            $ticket,
            'ticket.database_change.verify',
        );

        $result = trim((string) $result);
        $notes = trim((string) $notes);
        $now = Carbon::now(config('app.timezone'));
        $failure = null;
        $failureField = 'verification_result';

        $control = $this->database->transaction(function () use ($actor, $ticket, $result, $notes, $now, &$failure, &$failureField): ?DatabaseChangeControl {
            $lockedTicket = Ticket::query()
                ->whereKey($ticket->getKey())
                ->lockForUpdate()
                ->first();

            if ($lockedTicket === null) {
                $failure = 'Tiket tidak ditemukan.';
                $this->auditLogger->denied($actor, 'ticket.database_change.verification_denied', $ticket, $failure);

                return null;
            }

            $control = $this->controlFor($lockedTicket, true);

            if (! Gate::forUser($actor)->allows('verifyDatabaseChange', $lockedTicket)) {
                $failure = 'Verifikasi hanya tersedia bagi pelaksana SVC-03 pada status Dikerjakan.';
                $this->recordDenied($actor, $lockedTicket, $control, 'verification_denied', $failure, $now);

                return null;
            }

            if (! $control->executionStarted()) {
                $failure = 'Mulai Eksekusi harus berhasil sebelum hasil diverifikasi.';
                $this->recordDenied($actor, $lockedTicket, $control, 'verification_denied', $failure, $now);

                return null;
            }

            if ($result === '') {
                $failure = 'Hasil verifikasi wajib diisi.';
                $this->recordDenied($actor, $lockedTicket, $control, 'verification_denied', $failure, $now);

                return null;
            }

            if ($notes === '') {
                $failure = 'Catatan verifikasi wajib diisi.';
                $failureField = 'verification_notes';
                $this->recordDenied($actor, $lockedTicket, $control, 'verification_denied', $failure, $now);

                return null;
            }

            $control->forceFill([
                'verified_by_id' => $actor->getKey(),
                'verified_at' => $now,
                'verification_result' => $result,
                'verification_notes' => $notes,
            ])->save();
            $control->histories()->create([
                'ticket_id' => $lockedTicket->getKey(),
                'action' => 'verification_completed',
                'actor_id' => $actor->getKey(),
                'metadata' => [
                    'verified_at' => $now->toIso8601String(),
                    'verification_result' => $result,
                ],
                'reason' => $notes,
                'occurred_at' => $now,
            ]);
            $this->auditLogger->succeeded(
                $actor,
                'ticket.database_change.verification_completed',
                $lockedTicket,
                $notes,
                null,
                [
                    'control_id' => $control->getKey(),
                    'verified_by_id' => $actor->getKey(),
                    'verified_at' => $now->toIso8601String(),
                    'verification_result' => $result,
                ],
            );

            return $control->fresh(['executionStartedBy', 'verifier']);
        });

        if ($control === null) {
            throw ValidationException::withMessages([
                $failureField => $failure ?: 'Verifikasi hasil belum dapat disimpan.',
            ]);
        }

        return $control;
    }

    /**
     * Return the blocking reason before a ticket can enter Menunggu Konfirmasi.
     */
    public function completionFailure(Ticket $ticket): ?string
    {
        $serviceCode = $this->serviceCode($ticket);

        if ($serviceCode === self::SVC_DATABASE_CHANGE) {
            $control = $this->controlFor($ticket);

            return $this->executionEvidenceFailure($ticket)
                ?? (! $control->executionStarted()
                    ? 'SVC-03 harus berhasil Mulai Eksekusi sebelum tiket menunggu konfirmasi.'
                    : null)
                ?? (! $control->verified()
                    ? 'SVC-03 harus memiliki verifikasi hasil dengan pelaku, waktu, hasil, dan catatan sebelum tiket menunggu konfirmasi.'
                    : null);
        }

        if ($serviceCode === self::SVC_DATA_EXPORT && ! $this->hasRequesterAccessibleExport($ticket)) {
            return 'SVC-02 harus memiliki minimal satu hasil data export yang dapat diakses Pemohon sebelum tiket menunggu konfirmasi.';
        }

        return null;
    }

    /** @return array<string, mixed> */
    public function readiness(Ticket $ticket): array
    {
        $serviceCode = $this->serviceCode($ticket);

        if ($serviceCode === self::SVC_DATABASE_CHANGE) {
            $control = $this->controlFor($ticket);
            $requirements = [];
            $evidence = $this->evidenceAttachments($ticket);

            foreach (self::REQUIRED_EVIDENCE_TYPES as $type) {
                $attachment = $evidence->first(fn (Attachment $item): bool => $item->type_key === $type);
                $requirements[$type] = [
                    'available' => $attachment !== null,
                    'valid' => $attachment !== null && $this->attachmentIsUsable($attachment),
                    'attachment' => $attachment,
                ];
            }

            $evidenceReady = collect($requirements)->every(
                fn (array $requirement): bool => $requirement['available'] && $requirement['valid'],
            );

            return [
                'service_code' => $serviceCode,
                'kind' => 'database_change',
                'requirements' => $requirements,
                'evidence_ready' => $evidenceReady,
                'execution_started' => $control->executionStarted(),
                'verified' => $control->verified(),
                'ready' => $evidenceReady && $control->executionStarted() && $control->verified(),
                'control' => $control,
            ];
        }

        if ($serviceCode === self::SVC_DATA_EXPORT) {
            $exports = $this->exportAttachments($ticket);

            return [
                'service_code' => $serviceCode,
                'kind' => 'data_export',
                'attachments' => $exports,
                'available' => $exports->isNotEmpty(),
                'ready' => $this->hasRequesterAccessibleExport($ticket),
            ];
        }

        return [
            'service_code' => $serviceCode,
            'kind' => null,
            'ready' => true,
        ];
    }

    public function hasRequesterAccessibleExport(Ticket $ticket): bool
    {
        return $this->exportAttachments($ticket)
            ->contains(fn (Attachment $attachment): bool => $attachment->isRequesterAccessible() && $this->attachmentIsUsable($attachment));
    }

    public function serviceCode(Ticket $ticket): ?string
    {
        return $ticket->serviceType?->code ?? $ticket->service_type_code_snapshot;
    }

    /** @return Collection<int, Attachment> */
    private function evidenceAttachments(Ticket $ticket): Collection
    {
        return Attachment::query()
            ->where('ticket_id', $ticket->getKey())
            ->whereIn('type_key', self::REQUIRED_EVIDENCE_TYPES)
            ->get()
            ->groupBy('type_key')
            ->map(fn ($items) => $items->first(fn (Attachment $item): bool => $this->attachmentIsUsable($item)) ?? $items->first())
            ->values();
    }

    /** @return Collection<int, Attachment> */
    private function exportAttachments(Ticket $ticket): Collection
    {
        return Attachment::query()
            ->where('ticket_id', $ticket->getKey())
            ->where('type_key', 'data_export_result')
            ->get();
    }

    private function executionEvidenceFailure(Ticket $ticket): ?string
    {
        if ($this->serviceCode($ticket) !== self::SVC_DATABASE_CHANGE) {
            return 'Kontrol perubahan database hanya berlaku untuk SVC-03.';
        }

        $evidence = $this->evidenceAttachments($ticket);
        $missing = collect(self::REQUIRED_EVIDENCE_TYPES)
            ->reject(fn (string $type): bool => $evidence->contains('type_key', $type))
            ->values();

        if ($missing->isNotEmpty()) {
            return 'Bukti SVC-03 belum lengkap. Wajib tersedia: '.$missing->implode(', ').'.';
        }

        $invalid = $evidence->filter(fn (Attachment $attachment): bool => ! $this->attachmentIsUsable($attachment));

        if ($invalid->isNotEmpty()) {
            return 'Salah satu bukti SVC-03 tidak valid atau tidak dapat diakses.';
        }

        $ids = $evidence->pluck('id');

        $storageIdentities = $evidence->map(fn (Attachment $attachment): string => $attachment->storage_disk.'|'.$attachment->storage_path);

        if ($ids->count() !== $ids->unique()->count()
            || $storageIdentities->count() !== $storageIdentities->unique()->count()
            || $evidence->count() !== count(self::REQUIRED_EVIDENCE_TYPES)) {
            return 'change_script, rollback_script, dan backup_evidence harus berasal dari tiga berkas berbeda.';
        }

        return null;
    }

    private function attachmentIsUsable(Attachment $attachment): bool
    {
        if ($attachment->trashed()
            || ! filled($attachment->storage_disk)
            || ! filled($attachment->storage_path)
            || (int) $attachment->size_bytes <= 0
            || $attachment->storage_disk !== config('filesystems.attachment_disk', 'local')) {
            return false;
        }

        return Storage::disk($attachment->storage_disk)->exists($attachment->storage_path);
    }

    private function controlFor(Ticket $ticket, bool $create = false): DatabaseChangeControl
    {
        if ($ticket->relationLoaded('databaseChangeControl') && $ticket->databaseChangeControl !== null) {
            return $ticket->databaseChangeControl;
        }

        $query = DatabaseChangeControl::query()->where('ticket_id', $ticket->getKey());
        $control = $query->first();

        if ($control !== null || ! $create) {
            return $control ?? new DatabaseChangeControl(['ticket_id' => $ticket->getKey()]);
        }

        return DatabaseChangeControl::query()->create(['ticket_id' => $ticket->getKey()]);
    }

    private function recordDenied(
        User $actor,
        Ticket $ticket,
        DatabaseChangeControl $control,
        string $historyAction,
        string $reason,
        Carbon $occurredAt,
    ): void {
        $control->histories()->create([
            'ticket_id' => $ticket->getKey(),
            'action' => $historyAction,
            'actor_id' => $actor->getKey(),
            'reason' => $reason,
            'occurred_at' => $occurredAt,
        ]);

        $auditAction = $historyAction === 'execution_denied'
            ? 'ticket.database_change.execution_denied'
            : 'ticket.database_change.verification_denied';
        $this->auditLogger->denied($actor, $auditAction, $ticket, $reason);
    }
}
