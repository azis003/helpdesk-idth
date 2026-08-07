<?php

namespace App\ViewModels;

use App\Enums\Priority;
use App\Enums\Role;
use App\Enums\TicketStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Read-only ticket data approved for a Ketua Tim Kerja.
 *
 * This view model intentionally has no Eloquent model or relation inside it.
 * Adding a new field here is therefore an explicit projection decision.
 */
final class TeamChairTicketView
{
    /**
     * @param  Collection<int, TeamChairCommentView>  $publicComments
     * @param  array<string, mixed>|null  $sla
     */
    public function __construct(
        public readonly int $id,
        public readonly ?string $ticketNumber,
        public readonly string $subject,
        public readonly ?string $requesterName,
        public readonly ?string $teamName,
        public readonly ?string $serviceCode,
        public readonly ?string $serviceName,
        public readonly ?string $serviceSkills,
        public readonly ?string $categoryName,
        public readonly ?Priority $priority,
        public readonly ?TicketStatus $status,
        public readonly ?Carbon $submittedAt,
        public readonly ?Carbon $updatedAt,
        public readonly ?string $assigneeName,
        public readonly ?string $assignedTier,
        public readonly ?string $solution,
        public readonly ?array $sla,
        public readonly Collection $publicComments,
    ) {}

    public function ticketLabel(): string
    {
        return $this->ticketNumber ?: 'Tiket #'.$this->id;
    }

    public function serviceLabel(): string
    {
        return $this->serviceName ?: $this->serviceCode ?: 'Layanan belum tersedia';
    }

    public function assigneeLabel(): string
    {
        return $this->assigneeName ?: 'Belum ditugaskan';
    }

    public function assignedTierLabel(): ?string
    {
        return $this->assignedTier === null
            ? null
            : (Role::tryFrom($this->assignedTier)?->label() ?? $this->assignedTier);
    }

    public function latestPublicReply(): ?TeamChairCommentView
    {
        return $this->publicComments->last();
    }
}
