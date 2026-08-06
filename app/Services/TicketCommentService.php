<?php

namespace App\Services;

use App\Enums\TicketCommentVisibility;
use App\Models\ServiceType;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class TicketCommentService
{
    public function __construct(private readonly TicketAttachmentService $attachments) {}

    /**
     * @param  array<int|string, mixed>  $fileGroups
     */
    public function create(
        Ticket $ticket,
        User $author,
        TicketCommentVisibility $visibility,
        string $body,
        array $fileGroups = [],
    ): TicketComment {
        $serviceType = $ticket->relationLoaded('serviceType')
            ? $ticket->serviceType
            : $ticket->load('serviceType')->serviceType;
        $policies = $this->policiesFor($serviceType, $visibility);
        $files = $this->attachments->validate($fileGroups, $policies);
        $comment = $ticket->comments()->create([
            'author_id' => $author->getKey(),
            'visibility' => $visibility,
            'body' => trim($body),
        ]);

        $this->attachments->storeForComment($comment, $author, $files);

        return $comment->fresh(['author', 'attachments']);
    }

    private function policiesFor(?ServiceType $serviceType, TicketCommentVisibility $visibility): Collection
    {
        if ($serviceType === null) {
            return new Collection;
        }

        return $this->attachments->policiesFor(
            $serviceType,
            true,
            $visibility === TicketCommentVisibility::Public ? 'public' : 'internal',
        );
    }
}
