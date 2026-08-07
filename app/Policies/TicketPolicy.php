<?php

namespace App\Policies;

use App\Enums\Role;
use App\Enums\TicketStatus;
use App\Models\ApprovalRequest;
use App\Models\ApproverAssignment;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TeamScopeService;

class TicketPolicy
{
    public function __construct(private readonly TeamScopeService $teamScope) {}

    public function viewAny(User $actor): bool
    {
        return $actor->isActive()
            && $actor->hasAnyRole([
                Role::Pemohon,
                Role::AgenTier1,
                Role::AgenTier2,
                Role::KetuaTimKerja,
            ]);
    }

    public function create(User $actor): bool
    {
        return $actor->isActive()
            && ! $this->isReadOnlyTeamChair($actor)
            && $actor->hasAnyRole([Role::Pemohon, Role::AgenTier1]);
    }

    public function createSelf(User $actor): bool
    {
        return $actor->isActive()
            && ! $this->isReadOnlyTeamChair($actor)
            && $actor->hasRole(Role::Pemohon);
    }

    public function createForOther(User $actor, User $requester): bool
    {
        return $actor->isActive()
            && ! $this->isReadOnlyTeamChair($actor)
            && $actor->hasRole(Role::AgenTier1)
            && $requester->isActive();
    }

    public function view(User $actor, Ticket $ticket): bool
    {
        if (! $actor->isActive()) {
            return false;
        }

        if ($this->isReadOnlyTeamChair($actor)) {
            return $this->teamScope->canViewTicket($actor, $ticket);
        }

        if ((int) $ticket->requester_id === (int) $actor->getKey()
            || (int) $ticket->created_by_id === (int) $actor->getKey()) {
            return true;
        }

        if ($actor->hasAnyRole([Role::SuperAdmin, Role::AgenTier1])) {
            return true;
        }

        if ($this->isCurrentApprover($actor)) {
            return $this->hasPendingApproval($actor, $ticket);
        }

        if ($actor->hasRole(Role::KetuaTimKerja)) {
            return $this->teamScope->canViewTicket($actor, $ticket);
        }

        return $actor->hasRole(Role::AgenTier2)
            && (int) $ticket->assigned_to_id === (int) $actor->getKey();
    }

    public function requestApproval(User $actor, Ticket $ticket): bool
    {
        return $actor->isActive()
            && ! $this->isReadOnlyTeamChair($actor)
            && $actor->hasAnyRole([Role::AgenTier1, Role::AgenTier2])
            && (int) $ticket->assigned_to_id === (int) $actor->getKey()
            && in_array($ticket->status, [TicketStatus::Diproses, TicketStatus::Dikerjakan], true);
    }

    public function claim(User $actor, Ticket $ticket): bool
    {
        return $actor->isActive()
            && ! $this->isReadOnlyTeamChair($actor)
            && $actor->hasRole(Role::AgenTier1);
    }

    public function viewQueue(User $actor): bool
    {
        return $actor->isActive()
            && ! $this->isReadOnlyTeamChair($actor)
            && $actor->hasRole(Role::AgenTier1);
    }

    public function triage(User $actor, Ticket $ticket): bool
    {
        return $actor->isActive()
            && ! $this->isReadOnlyTeamChair($actor)
            && $actor->hasRole(Role::AgenTier1)
            && $ticket->assigned_tier === Role::AgenTier1->value
            && (int) $ticket->assigned_to_id === (int) $actor->getKey()
            && in_array($ticket->status, [TicketStatus::Diproses, TicketStatus::Dikerjakan], true);
    }

    public function assignTierTwo(User $actor, Ticket $ticket): bool
    {
        return $actor->isActive()
            && ! $this->isReadOnlyTeamChair($actor)
            && $actor->hasRole(Role::AgenTier1)
            && $ticket->status === TicketStatus::Dikerjakan
            && $ticket->assigned_tier === Role::AgenTier1->value
            && (int) $ticket->assigned_to_id === (int) $actor->getKey();
    }

    public function returnToTierOne(User $actor, Ticket $ticket): bool
    {
        return $actor->isActive()
            && ! $this->isReadOnlyTeamChair($actor)
            && $actor->hasRole(Role::AgenTier2)
            && $ticket->status === TicketStatus::Dikerjakan
            && $ticket->assigned_tier === Role::AgenTier2->value
            && (int) $ticket->assigned_to_id === (int) $actor->getKey()
            && $ticket->last_triaged_by_id !== null;
    }

    public function handle(User $actor, Ticket $ticket): bool
    {
        return $actor->isActive()
            && ! $this->isReadOnlyTeamChair($actor)
            && $actor->hasAnyRole([Role::AgenTier1, Role::AgenTier2])
            && (int) $ticket->assigned_to_id === (int) $actor->getKey();
    }

    public function cancel(User $actor, Ticket $ticket): bool
    {
        return $actor->isActive()
            && ! $this->isReadOnlyTeamChair($actor)
            && $actor->hasRole(Role::Pemohon)
            && (int) $ticket->requester_id === (int) $actor->getKey()
            && $ticket->status === TicketStatus::Baru;
    }

    public function commentPublic(User $actor, Ticket $ticket): bool
    {
        if (! $actor->isActive() || $this->isReadOnlyTeamChair($actor)) {
            return false;
        }

        if ($this->hasPendingApproval($actor, $ticket)) {
            return true;
        }

        if ($actor->hasRole(Role::Pemohon)
            && ! $actor->hasAnyRole([Role::AgenTier1, Role::AgenTier2])) {
            return (int) $ticket->requester_id === (int) $actor->getKey()
                && $ticket->status === TicketStatus::MenungguPemohon;
        }

        return $this->isAssignedAgent($actor, $ticket)
            && in_array($ticket->status, [
                TicketStatus::Diproses,
                TicketStatus::Dikerjakan,
                TicketStatus::MenungguPemohon,
                TicketStatus::MenungguPihakKetiga,
            ], true);
    }

    public function commentInternal(User $actor, Ticket $ticket): bool
    {
        if ($this->isReadOnlyTeamChair($actor)) {
            return false;
        }

        if ($this->hasPendingApproval($actor, $ticket)) {
            return true;
        }

        return $this->isAssignedAgent($actor, $ticket)
            && in_array($ticket->status, [
                TicketStatus::Diproses,
                TicketStatus::Dikerjakan,
                TicketStatus::MenungguPemohon,
                TicketStatus::MenungguPihakKetiga,
            ], true);
    }

    public function updateInternalFields(User $actor, Ticket $ticket): bool
    {
        return $this->isAssignedAgent($actor, $ticket)
            && $this->serviceCode($ticket) === 'SVC-07'
            && in_array($ticket->status, [
                TicketStatus::Diproses,
                TicketStatus::Dikerjakan,
                TicketStatus::MenungguPemohon,
                TicketStatus::MenungguPihakKetiga,
            ], true);
    }

    public function requestInformation(User $actor, Ticket $ticket): bool
    {
        return $this->isAssignedAgent($actor, $ticket)
            && in_array($ticket->status, [TicketStatus::Diproses, TicketStatus::Dikerjakan], true);
    }

    public function replyRequester(User $actor, Ticket $ticket): bool
    {
        return $actor->isActive()
            && ! $this->isReadOnlyTeamChair($actor)
            && $actor->hasRole(Role::Pemohon)
            && (int) $ticket->requester_id === (int) $actor->getKey()
            && $ticket->status === TicketStatus::MenungguPemohon;
    }

    public function startThirdPartyWait(User $actor, Ticket $ticket): bool
    {
        return $this->isAssignedAgent($actor, $ticket)
            && in_array($ticket->status, [TicketStatus::Diproses, TicketStatus::Dikerjakan], true);
    }

    public function resumeThirdPartyWait(User $actor, Ticket $ticket): bool
    {
        return $this->isAssignedAgent($actor, $ticket)
            && $ticket->status === TicketStatus::MenungguPihakKetiga;
    }

    public function complete(User $actor, Ticket $ticket): bool
    {
        return $this->isAssignedAgent($actor, $ticket)
            && $ticket->status === TicketStatus::Dikerjakan;
    }

    public function uploadAttachment(User $actor, Ticket $ticket): bool
    {
        return $this->isAssignedAgent($actor, $ticket)
            && in_array($ticket->status, [TicketStatus::Diproses, TicketStatus::Dikerjakan], true);
    }

    public function startDatabaseChange(User $actor, Ticket $ticket): bool
    {
        return $this->isAssignedAgent($actor, $ticket)
            && $this->serviceCode($ticket) === 'SVC-03'
            && $ticket->status === TicketStatus::Dikerjakan;
    }

    public function verifyDatabaseChange(User $actor, Ticket $ticket): bool
    {
        return $this->isAssignedAgent($actor, $ticket)
            && $this->serviceCode($ticket) === 'SVC-03'
            && $ticket->status === TicketStatus::Dikerjakan;
    }

    public function confirm(User $actor, Ticket $ticket): bool
    {
        return $actor->isActive()
            && ! $this->isReadOnlyTeamChair($actor)
            && $actor->hasRole(Role::Pemohon)
            && (int) $ticket->requester_id === (int) $actor->getKey()
            && $ticket->status === TicketStatus::MenungguKonfirmasi;
    }

    public function notSatisfied(User $actor, Ticket $ticket): bool
    {
        return $this->confirm($actor, $ticket);
    }

    public function reopen(User $actor, Ticket $ticket): bool
    {
        return $actor->isActive()
            && ! $this->isReadOnlyTeamChair($actor)
            && $actor->hasRole(Role::Pemohon)
            && (int) $ticket->requester_id === (int) $actor->getKey()
            && $ticket->status === TicketStatus::Ditutup;
    }

    private function isAssignedAgent(User $actor, Ticket $ticket): bool
    {
        return $actor->isActive()
            && ! $this->isReadOnlyTeamChair($actor)
            && $actor->hasAnyRole([Role::AgenTier1, Role::AgenTier2])
            && (int) $ticket->assigned_to_id === (int) $actor->getKey();
    }

    private function serviceCode(Ticket $ticket): ?string
    {
        return $ticket->serviceType?->code ?? $ticket->service_type_code_snapshot;
    }

    private function isCurrentApprover(User $actor): bool
    {
        return $actor->isActive()
            && ! $actor->requiresPasswordChange()
            && ! $this->isReadOnlyTeamChair($actor)
            && $actor->hasRole(Role::Approver)
            && ApproverAssignment::query()
                ->active()
                ->where('user_id', $actor->getKey())
                ->exists();
    }

    private function hasPendingApproval(User $actor, Ticket $ticket): bool
    {
        return $this->isCurrentApprover($actor)
            && ApprovalRequest::query()
                ->pending()
                ->where('ticket_id', $ticket->getKey())
                ->where('approver_id', $actor->getKey())
                ->exists();
    }

    private function isReadOnlyTeamChair(User $actor): bool
    {
        return $actor->hasRole(Role::KetuaTimKerja);
    }
}
