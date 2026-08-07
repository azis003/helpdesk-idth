<?php

namespace App\Services;

use App\Enums\Role;
use App\Enums\TicketCommentVisibility;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use App\Notifications\TicketEventNotification;
use Illuminate\Database\DatabaseManager;
use Illuminate\Validation\ValidationException;

class TicketCommunicationService
{
    public function __construct(
        private readonly DatabaseManager $database,
        private readonly DomainAuthorization $authorization,
        private readonly AuditLogger $auditLogger,
        private readonly TicketCommentService $comments,
    ) {}

    /**
     * @param  array<int|string, mixed>  $fileGroups
     */
    public function publicReply(User $actor, Ticket $ticket, string $body, array $fileGroups = []): TicketComment
    {
        $this->authorization->authorize($actor, 'commentPublic', $ticket, 'ticket.comment.public');

        if ($actor->hasRole(Role::Pemohon)
            && ! $actor->hasAnyRole([Role::AgenTier1, Role::AgenTier2])) {
            $this->deny(
                $actor,
                $ticket,
                'ticket.comment.public',
                'Balasan Pemohon harus dikirim melalui alur Menunggu Pemohon.',
            );
        }

        $comment = $this->database->transaction(function () use ($actor, $ticket, $body, $fileGroups): TicketComment {
            $lockedTicket = Ticket::query()
                ->with('serviceType')
                ->whereKey($ticket->getKey())
                ->lockForUpdate()
                ->first();

            if ($lockedTicket === null) {
                $this->deny($actor, $ticket, 'ticket.comment.public', 'Tiket tidak ditemukan.');
            }

            // Re-check authorization after acquiring the row lock so a status
            // or assignee change cannot make a stale request write a comment.
            $this->authorization->authorize($actor, 'commentPublic', $lockedTicket, 'ticket.comment.public');

            try {
                $comment = $this->comments->create(
                    $lockedTicket,
                    $actor,
                    TicketCommentVisibility::Public,
                    $body,
                    $fileGroups,
                );
            } catch (ValidationException $exception) {
                $this->auditLogger->denied(
                    $actor,
                    'ticket.comment.public',
                    $lockedTicket,
                    $this->validationReason($exception),
                );

                throw $exception;
            }

            $this->auditLogger->succeeded(
                $actor,
                'ticket.comment.public',
                $lockedTicket,
                'Balasan ke Pemohon disimpan.',
                null,
                ['comment_id' => $comment->getKey(), 'visibility' => TicketCommentVisibility::Public->value],
            );

            return $comment;
        });

        $notificationTicket = $ticket->fresh(['requester']);
        if ($notificationTicket?->requester !== null
            && (int) $notificationTicket->requester->getKey() !== (int) $actor->getKey()) {
            $notificationTicket->requester->notify(new TicketEventNotification(
                'public_comment',
                'Balasan baru pada tiket',
                "Ada balasan baru dari {$actor->name} pada tiket {$notificationTicket->ticket_number}.",
                $notificationTicket,
            ));
        }

        return $comment;
    }

    /**
     * @param  array<int|string, mixed>  $fileGroups
     */
    public function internalNote(User $actor, Ticket $ticket, string $body, array $fileGroups = []): TicketComment
    {
        $this->authorization->authorize($actor, 'commentInternal', $ticket, 'ticket.comment.internal');

        $comment = $this->database->transaction(function () use ($actor, $ticket, $body, $fileGroups): TicketComment {
            $lockedTicket = Ticket::query()
                ->with('serviceType')
                ->whereKey($ticket->getKey())
                ->lockForUpdate()
                ->first();

            if ($lockedTicket === null) {
                $this->deny($actor, $ticket, 'ticket.comment.internal', 'Tiket tidak ditemukan.');
            }

            $this->authorization->authorize($actor, 'commentInternal', $lockedTicket, 'ticket.comment.internal');

            try {
                $comment = $this->comments->create(
                    $lockedTicket,
                    $actor,
                    TicketCommentVisibility::Internal,
                    $body,
                    $fileGroups,
                );
            } catch (ValidationException $exception) {
                $this->auditLogger->denied(
                    $actor,
                    'ticket.comment.internal',
                    $lockedTicket,
                    $this->validationReason($exception),
                );

                throw $exception;
            }

            $this->auditLogger->succeeded(
                $actor,
                'ticket.comment.internal',
                $lockedTicket,
                'Catatan internal disimpan.',
                null,
                ['comment_id' => $comment->getKey(), 'visibility' => TicketCommentVisibility::Internal->value],
            );

            return $comment;
        });

        $notificationTicket = $ticket->fresh(['assignee']);
        if ($notificationTicket?->assignee !== null
            && (int) $notificationTicket->assignee->getKey() !== (int) $actor->getKey()) {
            $notificationTicket->assignee->notify(new TicketEventNotification(
                'internal_note',
                'Catatan internal baru',
                "Ada catatan internal baru pada tiket {$notificationTicket->ticket_number} dari {$actor->name}.",
                $notificationTicket,
            ));
        }

        return $comment;
    }

    private function deny(User $actor, Ticket $ticket, string $action, string $reason): never
    {
        $this->auditLogger->denied($actor, $action, $ticket, $reason);

        throw ValidationException::withMessages(['ticket' => $reason]);
    }

    private function validationReason(ValidationException $exception): string
    {
        return (string) (collect($exception->errors())->flatten()->first() ?: 'Validasi aksi komunikasi gagal.');
    }
}
