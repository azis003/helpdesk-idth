<?php

namespace App\ViewModels;

use App\Enums\Priority;
use App\Enums\TicketStatus;
use Illuminate\Support\Collection;

/**
 * Read-only ticket data approved for the Pemohon detail screen.
 *
 * The requester screen deliberately drops internal workflow vocabulary such as
 * tier routing, triage outcomes, SLA cycle numbers and compliance flags. Every
 * property below is an explicit projection decision, so widening what a
 * requester can see requires a conscious change here instead of leaking in
 * through an Eloquent passthrough.
 */
final class RequesterTicketView
{
    /**
     * @param  list<array{label: string, caption: string, state: string}>  $journey
     * @param  array{headline: string, detail: string, target: string}|null  $sla
     * @param  list<array{label: string, value: string}>  $facts
     * @param  list<array{label: string, value: string}>  $submittedFields
     * @param  list<array{name: string, meta: string, url: string}>  $attachments
     * @param  list<array{title: string, description: string, occurredAt: ?\Illuminate\Support\Carbon, tone: string}>  $timeline
     * @param  Collection<int, RequesterTicketMessage>  $messages
     */
    public function __construct(
        public readonly int $id,
        public readonly string $label,
        public readonly string $subject,
        public readonly string $description,
        public readonly ?TicketStatus $status,
        public readonly ?Priority $priority,
        public readonly string $statusHeadline,
        public readonly string $statusNarrative,
        public readonly ?string $noticeTitle,
        public readonly ?string $noticeBody,
        public readonly ?string $solution,
        public readonly array $journey,
        public readonly ?array $sla,
        public readonly array $facts,
        public readonly array $submittedFields,
        public readonly array $attachments,
        public readonly array $timeline,
        public readonly Collection $messages,
    ) {}

    public function hasJourney(): bool
    {
        return $this->journey !== [];
    }

    public function hasNotice(): bool
    {
        return $this->noticeTitle !== null && $this->noticeBody !== null;
    }

    public function hasSolution(): bool
    {
        return $this->solution !== null;
    }

    public function hasSubmittedFields(): bool
    {
        return $this->submittedFields !== [];
    }

    public function hasAttachments(): bool
    {
        return $this->attachments !== [];
    }

    public function hasTimeline(): bool
    {
        return $this->timeline !== [];
    }

    public function hasMessages(): bool
    {
        return $this->messages->isNotEmpty();
    }
}
