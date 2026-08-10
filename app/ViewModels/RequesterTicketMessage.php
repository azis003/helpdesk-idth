<?php

namespace App\ViewModels;

use Illuminate\Support\Carbon;

/**
 * One public conversation entry as shown to the Pemohon.
 *
 * Internal notes never reach this view model: TicketController already strips
 * them before projection, so nothing here can re-expose them by accident.
 */
final class RequesterTicketMessage
{
    /**
     * @param  list<array{name: string, url: string}>  $attachments
     */
    public function __construct(
        public readonly string $authorName,
        public readonly string $body,
        public readonly ?Carbon $createdAt,
        public readonly bool $fromRequester,
        public readonly array $attachments,
    ) {}

    public function initial(): string
    {
        $initial = mb_strtoupper(mb_substr(trim($this->authorName), 0, 1));

        return $initial !== '' ? $initial : 'T';
    }

    public function roleLabel(): string
    {
        return $this->fromRequester ? 'Anda' : 'Tim TI';
    }

    public function hasAttachments(): bool
    {
        return $this->attachments !== [];
    }
}
