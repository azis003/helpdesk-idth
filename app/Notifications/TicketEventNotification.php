<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TicketEventNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $event,
        private readonly string $title,
        private readonly string $message,
        private readonly Ticket $ticket,
    ) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toDatabase(object $notifiable): array
    {
        return $this->payload();
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return $this->payload();
    }

    /** @return array<string, mixed> */
    private function payload(): array
    {
        return [
            'event' => $this->event,
            'title' => $this->title,
            'message' => $this->message,
            'ticket_id' => $this->ticket->getKey(),
            'ticket_number' => $this->ticket->ticket_number,
            'url' => route('tickets.show', $this->ticket),
        ];
    }
}
