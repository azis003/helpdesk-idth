<?php

namespace App\Services;

use App\Enums\TicketStatus;
use App\Models\Attachment;
use App\Models\Ticket;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class TicketRetentionService
{
    public function __construct(
        private readonly DatabaseManager $database,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function purgeExpiredTickets(?Carbon $now = null): int
    {
        $now ??= Carbon::now((string) config('app.timezone', 'Asia/Jakarta'));
        $cutoff = $now->copy()->subYears((int) config('retention.ticket_years', 5));
        $deleted = 0;

        Ticket::query()
            ->whereIn('status', [
                TicketStatus::Ditutup->value,
                TicketStatus::Ditolak->value,
                TicketStatus::Dibatalkan->value,
            ])
            ->where(function ($query) use ($cutoff): void {
                $query
                    ->where('closed_at', '<=', $cutoff)
                    ->orWhere(function ($fallback) use ($cutoff): void {
                        $fallback->whereNull('closed_at')->where('updated_at', '<=', $cutoff);
                    });
            })
            ->orderBy('id')
            ->chunkById(100, function ($tickets) use ($now, &$deleted): void {
                foreach ($tickets as $ticket) {
                    if ($this->purgeTicket($ticket, $now)) {
                        $deleted++;
                    }
                }
            });

        return $deleted;
    }

    private function purgeTicket(Ticket $ticket, Carbon $now): bool
    {
        return $this->database->transaction(function () use ($ticket, $now): bool {
            $lockedTicket = Ticket::query()
                ->whereKey($ticket->getKey())
                ->lockForUpdate()
                ->first();

            if ($lockedTicket === null || ! $this->isEligible($lockedTicket, $now)) {
                return false;
            }

            $before = [
                'ticket_id' => $lockedTicket->getKey(),
                'ticket_number' => $lockedTicket->ticket_number,
                'status' => $lockedTicket->status?->value,
                'closed_at' => $lockedTicket->closed_at?->toIso8601String(),
                'updated_at' => $lockedTicket->updated_at?->toIso8601String(),
                'retention_years' => (int) config('retention.ticket_years', 5),
            ];

            $storageDeleted = 0;
            $storageFailed = false;
            Attachment::query()
                ->where('ticket_id', $lockedTicket->getKey())
                ->withTrashed()
                ->get()
                ->each(function (Attachment $attachment) use (&$storageDeleted, &$storageFailed): void {
                    try {
                        $disk = Storage::disk($attachment->storage_disk);

                        if (! $disk->exists($attachment->storage_path) || $disk->delete($attachment->storage_path)) {
                            $storageDeleted++;

                            return;
                        }

                        $storageFailed = true;
                    } catch (\Throwable) {
                        $storageFailed = true;
                    }
                });

            if ($storageFailed) {
                $this->auditLogger->denied(
                    null,
                    'ticket.retention_delete_failed',
                    $lockedTicket,
                    'Berkas tiket belum dapat dihapus dari storage; tiket dipertahankan untuk percobaan berikutnya.',
                    ['attachments_storage_deleted' => $storageDeleted],
                );

                return false;
            }

            $lockedTicket->delete();

            $this->auditLogger->succeeded(
                null,
                'ticket.retention_deleted',
                $lockedTicket,
                'Tiket dan histori bisnis dihapus setelah melewati retensi lima tahun.',
                $before,
                [
                    ...$before,
                    'deleted_at' => $now->toIso8601String(),
                    'attachments_storage_deleted' => $storageDeleted,
                ],
            );

            return true;
        });
    }

    private function isEligible(Ticket $ticket, Carbon $now): bool
    {
        if (! in_array($ticket->status?->value, [
            TicketStatus::Ditutup->value,
            TicketStatus::Ditolak->value,
            TicketStatus::Dibatalkan->value,
        ], true)) {
            return false;
        }

        $reference = $ticket->closed_at ?? $ticket->updated_at;

        return $reference !== null
            && $reference->lessThanOrEqualTo($now->copy()->subYears((int) config('retention.ticket_years', 5)));
    }
}
