<?php

namespace App\Services;

use App\Enums\Role;
use App\Models\ApprovalRequest;
use App\Models\ApproverAssignment;
use App\Models\Role as RoleModel;
use App\Models\User;
use Illuminate\Database\DatabaseManager;
use Illuminate\Validation\ValidationException;

class ApproverAssignmentService
{
    public function __construct(
        private readonly DatabaseManager $database,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function current(bool $lockForUpdate = false): ?ApproverAssignment
    {
        $query = ApproverAssignment::query()
            ->with('user.roles')
            ->active();

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    public function currentEligible(bool $lockForUpdate = false): ?ApproverAssignment
    {
        $assignment = $this->current($lockForUpdate);
        $user = $assignment?->user;

        if ($user === null
            || ! $user->isActive()
            || ! $user->hasRole(Role::Approver)
            || $user->requiresPasswordChange()) {
            return null;
        }

        return $assignment;
    }

    public function isCurrentApprover(User $user): bool
    {
        return (int) ($this->currentEligible()?->user_id ?? 0) === (int) $user->getKey();
    }

    public function pendingCount(?int $userId): int
    {
        if ($userId === null) {
            return 0;
        }

        return ApprovalRequest::query()
            ->pending()
            ->where('approver_id', $userId)
            ->count();
    }

    public function replace(
        User $actor,
        int $replacementUserId,
        bool $transferPendingApprovals,
        string $reason,
    ): ApproverAssignment {
        $candidate = User::query()->with('roles')->find($replacementUserId);
        $this->validateCandidate($actor, $candidate);

        $current = $this->current();

        if ($current?->user_id === $replacementUserId) {
            $this->deny(
                $actor,
                'admin.approver.replacement',
                $candidate,
                'Pengguna tersebut sudah menjadi Manajer TI aktif.',
            );
        }

        $pendingCount = $this->pendingCount($current?->user_id);

        if ($pendingCount > 0 && ! $transferPendingApprovals) {
            $this->deny(
                $actor,
                'admin.approver.replacement',
                $current?->user,
                "Penggantian ditolak karena {$pendingCount} approval tertunda belum dikonfirmasi untuk dipindahkan.",
            );
        }

        return $this->database->transaction(function () use (
            $actor,
            $replacementUserId,
            $transferPendingApprovals,
            $reason,
        ): ApproverAssignment {
            $candidate = User::query()->with('roles')->lockForUpdate()->findOrFail($replacementUserId);
            $current = ApproverAssignment::query()
                ->with('user')
                ->active()
                ->lockForUpdate()
                ->first();
            $pending = $current === null
                ? collect()
                : ApprovalRequest::query()
                    ->pending()
                    ->where('approver_id', $current->user_id)
                    ->lockForUpdate()
                    ->get();

            if ($pending->isNotEmpty() && ! $transferPendingApprovals) {
                throw ValidationException::withMessages([
                    'transfer_pending_approvals' => 'Approval tertunda harus dipindahkan secara eksplisit sebelum approver diganti.',
                ]);
            }

            $now = now();
            $before = $this->assignmentSnapshot($current, $pending->count());

            if ($current !== null) {
                $current->forceFill([
                    'ended_at' => $now,
                    'is_active' => false,
                    'replacement_reason' => $reason,
                ])->save();
            }

            if ($pending->isNotEmpty()) {
                ApprovalRequest::query()
                    ->whereKey($pending->modelKeys())
                    ->update([
                        'approver_id' => $candidate->getKey(),
                        'transferred_from_id' => $current?->user_id,
                        'transferred_at' => $now,
                        'updated_at' => $now,
                    ]);
            }

            $assignment = ApproverAssignment::query()->create([
                'user_id' => $candidate->getKey(),
                'assigned_by' => $actor->getKey(),
                'started_at' => $now,
                'is_active' => true,
                'replacement_reason' => $reason,
            ]);
            $assignment->load('user');

            $this->auditLogger->succeeded(
                $actor,
                $current === null ? 'admin.approver.assigned' : 'admin.approver.replaced',
                $assignment,
                $current === null
                    ? 'Manajer TI/Approver aktif ditetapkan.'
                    : 'Manajer TI/Approver aktif diganti dan approval tertunda dipindahkan.',
                $before,
                $this->assignmentSnapshot($assignment, $pending->count()),
            );

            return $assignment;
        });
    }

    public function assertCanDeactivate(User $actor, User $target): void
    {
        $current = $this->current();

        if ($current?->user_id !== $target->getKey()) {
            return;
        }

        $this->deny(
            $actor,
            'admin.approver.deactivate',
            $target,
            'Manajer TI/Approver aktif tidak dapat dinonaktifkan sebelum pengganti ditetapkan.',
            'user_id',
        );
    }

    /** @param iterable<int|string> $requestedRoleIds */
    public function assertCanRevokeApproverRole(User $actor, User $target, iterable $requestedRoleIds): void
    {
        $current = $this->current();

        if ($current?->user_id !== $target->getKey()) {
            return;
        }

        $approverRoleId = RoleModel::query()->where('slug', Role::Approver->value)->value('id');
        $requestedRoleIds = collect($requestedRoleIds)->map(fn ($id): int => (int) $id)->all();

        if ($approverRoleId === null || in_array((int) $approverRoleId, $requestedRoleIds, true)) {
            return;
        }

        $this->deny(
            $actor,
            'admin.approver.role.revoke',
            $target,
            'Role Approver pada Manajer TI aktif tidak dapat dicabut sebelum pengganti ditetapkan.',
            'role_ids',
        );
    }

    private function validateCandidate(User $actor, ?User $candidate): void
    {
        if ($candidate === null) {
            $this->deny($actor, 'admin.approver.replacement', null, 'Pengguna pengganti tidak tersedia.');
        }

        if (! $candidate->isActive()) {
            $this->deny($actor, 'admin.approver.replacement', $candidate, 'Pengguna pengganti harus aktif.');
        }

        if (! $candidate->hasRole(Role::Approver)) {
            $this->deny($actor, 'admin.approver.replacement', $candidate, 'Pengguna pengganti wajib memiliki role Approver.');
        }

        if ($candidate->requiresPasswordChange()) {
            $this->deny($actor, 'admin.approver.replacement', $candidate, 'Pengguna pengganti harus mengganti password awal terlebih dahulu.');
        }
    }

    private function deny(User $actor, string $action, ?User $subject, string $reason, string $field = 'replacement_user_id'): never
    {
        $this->auditLogger->denied($actor, $action, $subject, $reason);

        throw ValidationException::withMessages([
            $field => $reason,
        ]);
    }

    /** @return array<string, mixed>|null */
    private function assignmentSnapshot(?ApproverAssignment $assignment, int $pendingCount): ?array
    {
        if ($assignment === null) {
            return null;
        }

        return [
            'assignment_id' => $assignment->getKey(),
            'user_id' => $assignment->user_id,
            'username' => $assignment->user?->username,
            'name' => $assignment->user?->name,
            'started_at' => $assignment->started_at?->toIso8601String(),
            'ended_at' => $assignment->ended_at?->toIso8601String(),
            'replacement_reason' => $assignment->replacement_reason,
            'is_active' => $assignment->is_active,
            'pending_approval_count' => $pendingCount,
        ];
    }
}
