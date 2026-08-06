<?php

namespace App\Services;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\DatabaseManager;
use Illuminate\Validation\ValidationException;

class TicketCancellationService
{
    public function __construct(
        private readonly DatabaseManager $database,
        private readonly DomainAuthorization $authorization,
        private readonly AuditLogger $auditLogger,
        private readonly TicketSlaService $sla,
    ) {}

    public function cancel(User $actor, Ticket $ticket): void
    {
        $this->authorization->authorize($actor, 'cancel', $ticket, 'ticket.cancel');

        $this->database->transaction(function () use ($actor, $ticket): void {
            $lockedTicket = Ticket::query()->whereKey($ticket->getKey())->lockForUpdate()->firstOrFail();

            if ($lockedTicket->status !== TicketStatus::Baru
                || (int) $lockedTicket->requester_id !== (int) $actor->getKey()) {
                $this->auditLogger->denied(
                    $actor,
                    'ticket.cancel',
                    $lockedTicket,
                    'Tiket hanya dapat dibatalkan oleh Pemohon saat status Baru.',
                );

                throw ValidationException::withMessages([
                    'ticket' => 'Tiket hanya dapat dibatalkan saat status Baru.',
                ]);
            }

            $before = ['status' => $lockedTicket->status->value];
            $occurredAt = now();
            $lockedTicket->forceFill(['status' => TicketStatus::Dibatalkan])->save();
            $lockedTicket->statusHistories()->create([
                'from_status' => TicketStatus::Baru->value,
                'to_status' => TicketStatus::Dibatalkan->value,
                'action' => 'ticket.cancelled',
                'actor_id' => $actor->getKey(),
                'occurred_at' => $occurredAt,
            ]);
            $this->sla->stop($lockedTicket, $occurredAt);

            $this->auditLogger->succeeded(
                $actor,
                'ticket.cancelled',
                $lockedTicket,
                'Tiket dibatalkan oleh Pemohon.',
                $before,
                ['status' => TicketStatus::Dibatalkan->value],
            );
        });
    }
}
