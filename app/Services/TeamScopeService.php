<?php

namespace App\Services;

use App\Models\TeamChairAssignment;
use App\Models\TeamMembership;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class TeamScopeService
{
    /**
     * Return the teams and members a Ketua Tim Kerja is allowed to monitor.
     *
     * @return array{team_ids:list<int>,team_names:list<string>,member_ids:list<int>}
     */
    public function scopeFor(User $chair): array
    {
        $assignments = TeamChairAssignment::query()
            ->with('workTeam')
            ->where('user_id', $chair->getKey())
            ->where('is_active', true)
            ->get();

        $teamIds = $assignments
            ->pluck('work_team_id')
            ->map(fn ($id): int => (int) $id)
            ->values()
            ->all();
        $teamNames = $assignments
            ->map(fn (TeamChairAssignment $assignment): ?string => $assignment->workTeam?->name)
            ->filter()
            ->values()
            ->all();
        $memberIds = $teamIds === []
            ? []
            : TeamMembership::query()
                ->whereIn('work_team_id', $teamIds)
                ->where('is_active', true)
                ->pluck('user_id')
                ->map(fn ($id): int => (int) $id)
                ->values()
                ->all();

        // A chair may be assigned before a membership row is created. The
        // chair remains part of the monitored team in that transition. With
        // no active chair assignment at all, the scope must stay empty.
        if ($teamIds !== []) {
            $memberIds[] = (int) $chair->getKey();
        }

        return [
            'team_ids' => $teamIds,
            'team_names' => $teamNames,
            'member_ids' => array_values(array_unique($memberIds)),
        ];
    }

    public function canViewTicket(User $chair, Ticket $ticket): bool
    {
        $scope = $this->scopeFor($chair);

        return in_array((int) $ticket->requester_id, $scope['member_ids'], true)
            || ($ticket->requester_team_snapshot !== null
                && in_array($ticket->requester_team_snapshot, $scope['team_names'], true));
    }

    public function constrain(Builder $query, User $chair): Builder
    {
        $scope = $this->scopeFor($chair);

        if ($scope['member_ids'] === [] && $scope['team_names'] === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $query) use ($scope): void {
            if ($scope['member_ids'] !== []) {
                $query->whereIn('requester_id', $scope['member_ids']);
            }

            if ($scope['team_names'] !== []) {
                $query->orWhereIn('requester_team_snapshot', $scope['team_names']);
            }
        });
    }
}
