<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketEventNotification;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\QueryException;
use Ramsey\Uuid\Uuid;

class TicketNotificationService
{
    public function __construct(private readonly DatabaseManager $database) {}

    /**
     * Send a ticket notification after the current transaction commits.
     *
     * The database channel is deliberately used directly through Laravel's
     * notification system. The callback is registered while the business
     * transaction is open, so a rollback discards the notification entirely.
     *
     * @param  iterable<int|string>  $recipientIds
     */
    public function send(
        Ticket $ticket,
        string $event,
        string $title,
        string $message,
        iterable $recipientIds,
        ?string $eventKey = null,
    ): void {
        $recipientIds = collect($recipientIds)
            ->filter(fn (mixed $id): bool => is_numeric($id) && (int) $id > 0)
            ->map(fn (mixed $id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        if ($recipientIds === []) {
            return;
        }

        $eventKey ??= "ticket:{$ticket->getKey()}:{$event}";
        $callback = function () use ($ticket, $event, $title, $message, $recipientIds, $eventKey): void {
            User::query()
                ->whereIn('id', $recipientIds)
                ->where('is_active', true)
                ->get()
                ->each(function (User $recipient) use ($ticket, $event, $title, $message, $eventKey): void {
                    $notificationId = $this->notificationId($eventKey, (int) $recipient->getKey());

                    if ($recipient->notifications()->whereKey($notificationId)->exists()) {
                        return;
                    }

                    $notification = new TicketEventNotification(
                        $event,
                        $title,
                        $message,
                        $ticket,
                    );
                    $notification->id = $notificationId;

                    try {
                        $recipient->notify($notification);
                    } catch (QueryException $exception) {
                        // Two safe retries may race between the existence check
                        // and the insert. The deterministic primary key makes
                        // that race harmless while unrelated database failures
                        // still surface to the caller.
                        if (! $this->isUniqueViolation($exception)) {
                            throw $exception;
                        }
                    }
                });
        };

        $connection = $this->database->connection();

        if ($connection->transactionLevel() > 0) {
            $connection->afterCommit($callback);

            return;
        }

        $callback();
    }

    private function notificationId(string $eventKey, int $recipientId): string
    {
        return Uuid::uuid5(
            Uuid::NAMESPACE_URL,
            "sihati:ticket-notification:{$eventKey}:user:{$recipientId}",
        )->toString();
    }

    private function isUniqueViolation(QueryException $exception): bool
    {
        return in_array((string) $exception->getCode(), ['23000', '23505'], true)
            || in_array((string) ($exception->errorInfo[0] ?? ''), ['23000', '23505'], true);
    }
}
