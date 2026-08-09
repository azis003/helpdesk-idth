<?php

namespace App\Services;

use App\Enums\Role as RoleEnum;
use App\Enums\TeamPosition;
use App\Models\ProblemCategory;
use App\Models\Role;
use App\Models\RoleAssignmentHistory;
use App\Models\Skill;
use App\Models\TeamChairAssignment;
use App\Models\TeamMembership;
use App\Models\User;
use App\Models\WorkTeam;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrganizationService
{
    public function __construct(
        private readonly DatabaseManager $database,
        private readonly AuditLogger $auditLogger,
        private readonly ApproverAssignmentService $approverAssignments,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function createUser(User $actor, array $data): User
    {
        return $this->database->transaction(function () use ($actor, $data): User {
            $user = User::query()->create([
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'] ?? null,
                'nip' => $data['nip'] ?? null,
                'password' => Hash::make($data['temporary_password']),
                'is_active' => true,
                'must_change_password' => false,
                'password_changed_at' => now(),
            ]);

            $teamPosition = TeamPosition::from($data['team_position']);
            $this->syncRoles($actor, $user, $this->roleIdsForTeamPosition($data['role_ids'] ?? [], $teamPosition));
            $this->assignTeamPosition($actor, $user, (int) $data['team_id'], $teamPosition);

            if (array_key_exists('skill_ids', $data)) {
                $this->syncSkills($actor, $user, $data['skill_ids'] ?? []);
            }

            $freshUser = $user->fresh(['roles', 'skills', 'currentTeamMembership.workTeam', 'teamChairAssignments']);

            $this->auditLogger->succeeded(
                $actor,
                'admin.user.created',
                $freshUser,
                'Pengguna baru dibuat dengan password awal.',
                null,
                $this->userSnapshot($freshUser),
            );

            return $freshUser;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateUser(User $actor, User $user, array $data): User
    {
        $teamPosition = TeamPosition::from($data['team_position']);
        $roleIds = $this->roleIdsForTeamPosition($data['role_ids'] ?? [], $teamPosition);
        $desiredActive = array_key_exists('is_active', $data)
            ? (bool) $data['is_active']
            : $user->is_active;

        if (array_key_exists('role_ids', $data)) {
            $this->approverAssignments->assertCanRevokeApproverRole($actor, $user, $roleIds);
        }

        if (! $desiredActive) {
            $this->approverAssignments->assertCanDeactivate($actor, $user);
        }

        return $this->database->transaction(function () use ($actor, $user, $data, $roleIds, $teamPosition, $desiredActive): User {
            $before = $this->userSnapshot($user);

            $user->forceFill([
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'] ?? null,
                'nip' => $data['nip'] ?? null,
            ])->save();

            // Team assignment validates that the account is active. Keep the
            // transition inside this transaction so an inactive account can
            // still be edited with a mandatory team, then restore its target
            // status before the transaction completes.
            if (! $user->is_active) {
                $user->forceFill(['is_active' => true])->save();
            }

            $freshUser = $user->fresh();

            if (array_key_exists('role_ids', $data)) {
                $this->syncRoles($actor, $freshUser, $roleIds);
            }

            $this->assignTeamPosition($actor, $freshUser, (int) $data['team_id'], $teamPosition);

            if (array_key_exists('skill_ids', $data)) {
                $this->syncSkills($actor, $freshUser, $data['skill_ids'] ?? []);
            }

            $freshUser->forceFill(['is_active' => $desiredActive])->save();

            $freshUser = $freshUser->fresh(['roles', 'skills', 'currentTeamMembership.workTeam', 'teamChairAssignments']);

            $this->auditLogger->succeeded(
                $actor,
                'admin.user.updated',
                $freshUser,
                'Profil pengguna diperbarui.',
                $before,
                $this->userSnapshot($freshUser),
            );

            return $freshUser;
        });
    }

    public function deleteUser(User $actor, User $user): void
    {
        $this->approverAssignments->assertCanDeactivate($actor, $user);

        $this->database->transaction(function () use ($actor, $user): void {
            $before = $this->userSnapshot($user);

            $this->removeTeam($actor, $user);
            $this->clearUserChairAssignments($actor, $user);

            $user->forceFill(['is_active' => false])->save();
            $user->delete();

            $this->auditLogger->succeeded(
                $actor,
                'admin.user.deleted',
                $user,
                'Pengguna dihapus dari daftar aktif; histori pengguna dipertahankan.',
                $before,
                $this->userSnapshot($user),
            );
        });
    }

    public function setUserStatus(User $actor, User $user, bool $active): User
    {
        if (! $active) {
            $this->approverAssignments->assertCanDeactivate($actor, $user);
        }

        return $this->database->transaction(function () use ($actor, $user, $active): User {
            $before = $this->userSnapshot($user);

            if ($user->is_active !== $active) {
                $user->forceFill(['is_active' => $active])->save();
            }

            $freshUser = $user->fresh(['roles', 'skills', 'currentTeamMembership.workTeam']);

            $this->auditLogger->succeeded(
                $actor,
                $active ? 'admin.user.activated' : 'admin.user.deactivated',
                $freshUser,
                $active ? 'Akun pengguna diaktifkan.' : 'Akun pengguna dinonaktifkan.',
                $before,
                $this->userSnapshot($freshUser),
            );

            return $freshUser;
        });
    }

    /**
     * @param  iterable<int|string>  $roleIds
     */
    public function syncRoles(User $actor, User $user, iterable $roleIds): User
    {
        $roleIds = collect($roleIds)->values()->all();
        $this->approverAssignments->assertCanRevokeApproverRole($actor, $user, $roleIds);

        return $this->database->transaction(fn (): User => $this->syncRolesInTransaction($actor, $user, $roleIds));
    }

    private function syncRolesInTransaction(User $actor, User $user, iterable $roleIds): User
    {
        $requestedIds = $this->normalizeIds($roleIds);
        $roles = Role::query()->whereIn('id', $requestedIds)->get()->keyBy('id');

        if ($roles->count() !== count($requestedIds)) {
            throw ValidationException::withMessages([
                'role_ids' => 'Salah satu role yang dipilih tidak tersedia.',
            ]);
        }

        $currentIds = $user->roles()->pluck('roles.id')->map(fn ($id): int => (int) $id)->all();
        $toAttach = array_values(array_diff($requestedIds, $currentIds));
        $toDetach = array_values(array_diff($currentIds, $requestedIds));
        $now = now();

        foreach ($toAttach as $roleId) {
            $user->roles()->attach($roleId, [
                'assigned_by' => $actor->getKey(),
                'assigned_at' => $now,
            ]);

            RoleAssignmentHistory::query()->create([
                'user_id' => $user->getKey(),
                'role_id' => $roleId,
                'acted_by' => $actor->getKey(),
                'action' => 'assigned',
                'occurred_at' => $now,
            ]);
        }

        if ($toDetach !== []) {
            $user->roles()->detach($toDetach);

            foreach ($toDetach as $roleId) {
                RoleAssignmentHistory::query()->create([
                    'user_id' => $user->getKey(),
                    'role_id' => $roleId,
                    'acted_by' => $actor->getKey(),
                    'action' => 'revoked',
                    'occurred_at' => $now,
                ]);
            }
        }

        $this->auditLogger->succeeded(
            $actor,
            'admin.user.roles.updated',
            $user,
            'Pemberian role pengguna diperbarui.',
            ['role_ids' => $this->roleSnapshot($currentIds)],
            ['role_ids' => $this->roleSnapshot($requestedIds)],
        );

        return $user->fresh('roles');
    }

    /**
     * @param  iterable<int|string>  $skillIds
     */
    public function syncSkills(User $actor, User $user, iterable $skillIds): User
    {
        return $this->database->transaction(fn (): User => $this->syncSkillsInTransaction($actor, $user, $skillIds));
    }

    private function syncSkillsInTransaction(User $actor, User $user, iterable $skillIds): User
    {
        $requestedIds = $this->normalizeIds($skillIds);
        $skills = Skill::query()->active()->whereIn('id', $requestedIds)->get()->keyBy('id');

        if ($skills->count() !== count($requestedIds)) {
            throw ValidationException::withMessages([
                'skill_ids' => 'Salah satu keahlian yang dipilih tidak aktif atau tidak tersedia.',
            ]);
        }

        $currentIds = $user->skills()->pluck('skills.id')->map(fn ($id): int => (int) $id)->all();
        $toAttach = array_values(array_diff($requestedIds, $currentIds));
        $toDetach = array_values(array_diff($currentIds, $requestedIds));
        $now = now();

        foreach ($toAttach as $skillId) {
            $user->skills()->attach($skillId, [
                'assigned_by' => $actor->getKey(),
                'assigned_at' => $now,
            ]);
        }

        if ($toDetach !== []) {
            $user->skills()->detach($toDetach);
        }

        $this->auditLogger->succeeded(
            $actor,
            'admin.user.skills.updated',
            $user,
            'Keahlian pengguna diperbarui.',
            ['skill_ids' => $this->skillSnapshot($currentIds)],
            ['skill_ids' => $this->skillSnapshot($requestedIds)],
        );

        return $user->fresh('skills');
    }

    public function assignTeam(User $actor, User $user, int $teamId): TeamMembership
    {
        return $this->database->transaction(function () use ($actor, $user, $teamId): TeamMembership {
            if (! $user->is_active) {
                throw ValidationException::withMessages([
                    'user_id' => 'Pengguna nonaktif tidak dapat ditetapkan ke tim aktif.',
                ]);
            }

            $team = WorkTeam::query()->active()->find($teamId);

            if ($team === null) {
                throw ValidationException::withMessages([
                    'team_id' => 'Tim kerja tidak aktif atau tidak tersedia.',
                ]);
            }

            $current = TeamMembership::query()
                ->with('workTeam')
                ->where('user_id', $user->getKey())
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();

            if ($current?->work_team_id === $team->getKey()) {
                return $current;
            }

            $before = $current === null ? null : [
                'team_id' => $current->work_team_id,
                'team_name' => $current->workTeam?->name,
                'started_at' => $current->started_at?->toIso8601String(),
            ];

            $now = now();

            if ($current !== null) {
                $current->forceFill([
                    'is_active' => false,
                    'ended_at' => $now,
                ])->save();
            }

            $membership = TeamMembership::query()->create([
                'work_team_id' => $team->getKey(),
                'user_id' => $user->getKey(),
                'assigned_by' => $actor->getKey(),
                'started_at' => $now,
                'is_active' => true,
            ]);

            $membership->load('workTeam');

            $this->auditLogger->succeeded(
                $actor,
                'admin.team.membership.changed',
                $user,
                'Tim utama pengguna diperbarui; histori perpindahan disimpan.',
                $before,
                [
                    'team_id' => $team->getKey(),
                    'team_name' => $team->name,
                    'started_at' => $membership->started_at?->toIso8601String(),
                ],
            );

            return $membership;
        });
    }

    public function assignTeamPosition(
        User $actor,
        User $user,
        int $teamId,
        TeamPosition|string $position,
    ): TeamMembership {
        $position = $position instanceof TeamPosition ? $position : TeamPosition::from($position);

        return $this->database->transaction(function () use ($actor, $user, $teamId, $position): TeamMembership {
            $team = WorkTeam::query()->active()->find($teamId);

            if ($team === null) {
                throw ValidationException::withMessages([
                    'team_id' => 'Tim kerja tidak aktif atau tidak tersedia.',
                ]);
            }

            $membership = $this->assignTeam($actor, $user, $teamId);
            $this->clearUserChairAssignments(
                $actor,
                $user,
                $position->isChair() ? $team->getKey() : null,
                ! $position->isChair(),
            );

            if ($position->isChair()) {
                $this->assignChair($actor, $team, $user->getKey());
            }

            return $membership->fresh('workTeam');
        });
    }

    public function removeTeam(User $actor, User $user): ?TeamMembership
    {
        return $this->database->transaction(function () use ($actor, $user): ?TeamMembership {
            $membership = TeamMembership::query()
                ->with('workTeam')
                ->where('user_id', $user->getKey())
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();

            if ($membership === null) {
                return null;
            }

            $before = [
                'team_id' => $membership->work_team_id,
                'team_name' => $membership->workTeam?->name,
                'started_at' => $membership->started_at?->toIso8601String(),
            ];
            $membership->forceFill(['is_active' => false, 'ended_at' => now()])->save();
            $this->clearUserChairAssignments($actor, $user);

            $this->auditLogger->succeeded(
                $actor,
                'admin.team.membership.removed',
                $user,
                'Pengguna dikeluarkan dari tim utama; histori tetap dipertahankan.',
                $before,
                null,
            );

            return $membership->fresh('workTeam');
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createTeam(User $actor, array $data): WorkTeam
    {
        $team = $this->database->transaction(function () use ($actor, $data): WorkTeam {
            $team = WorkTeam::query()->create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'is_active' => true,
            ]);

            $this->auditLogger->succeeded($actor, 'admin.team.created', $team, 'Tim kerja dibuat.', null, $this->teamSnapshot($team));

            return $team;
        });

        return $team;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateTeam(User $actor, WorkTeam $team, array $data): WorkTeam
    {
        return $this->database->transaction(function () use ($actor, $team, $data): WorkTeam {
            $before = $this->teamSnapshot($team);
            $team->forceFill([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
            ])->save();
            $team = $team->fresh();

            $this->auditLogger->succeeded($actor, 'admin.team.updated', $team, 'Tim kerja diperbarui.', $before, $this->teamSnapshot($team));

            return $team;
        });
    }

    public function setTeamStatus(User $actor, WorkTeam $team, bool $active): WorkTeam
    {
        return $this->database->transaction(function () use ($actor, $team, $active): WorkTeam {
            if (! $active && $team->currentMemberships()->exists()) {
                throw ValidationException::withMessages([
                    'team' => 'Tim yang masih memiliki anggota aktif tidak dapat dinonaktifkan. Pindahkan anggotanya terlebih dahulu.',
                ]);
            }

            $before = $this->teamSnapshot($team);
            $team->forceFill(['is_active' => $active])->save();
            $team = $team->fresh();

            $this->auditLogger->succeeded(
                $actor,
                $active ? 'admin.team.activated' : 'admin.team.deactivated',
                $team,
                $active ? 'Tim kerja diaktifkan.' : 'Tim kerja dinonaktifkan.',
                $before,
                $this->teamSnapshot($team),
            );

            return $team;
        });
    }

    public function deleteTeam(User $actor, WorkTeam $team): void
    {
        $this->database->transaction(function () use ($actor, $team): void {
            if ($team->currentMemberships()->exists()) {
                throw ValidationException::withMessages([
                    'team' => 'Tim yang masih memiliki anggota aktif tidak dapat dihapus. Pindahkan anggotanya terlebih dahulu.',
                ]);
            }

            $before = $this->teamSnapshot($team);
            $team->forceFill(['is_active' => false])->save();
            $team->delete();

            $this->auditLogger->succeeded(
                $actor,
                'admin.team.deleted',
                $team,
                'Tim kerja dihapus secara lunak agar histori tetap tersedia.',
                $before,
                ['deleted_at' => $team->deleted_at?->toIso8601String()],
            );
        });
    }

    public function assignChair(User $actor, WorkTeam $team, ?int $userId): ?TeamChairAssignment
    {
        return $this->database->transaction(function () use ($actor, $team, $userId): ?TeamChairAssignment {
            if (! $team->is_active) {
                throw ValidationException::withMessages(['user_id' => 'Ketua hanya dapat ditetapkan pada tim aktif.']);
            }

            $current = TeamChairAssignment::query()
                ->with('user')
                ->where('work_team_id', $team->getKey())
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();

            if ($current?->user_id === $userId) {
                if ($current->user !== null) {
                    $this->syncChairRole($actor, $current->user, true);
                }

                return $current;
            }

            $before = $current === null ? null : [
                'user_id' => $current->user_id,
                'user_name' => $current->user?->name,
                'started_at' => $current->started_at?->toIso8601String(),
            ];
            $now = now();

            if ($current !== null) {
                $current->forceFill(['is_active' => false, 'ended_at' => $now])->save();
                $previousChair = User::query()->find($current->user_id);

                if ($previousChair !== null && ! TeamChairAssignment::query()
                    ->where('user_id', $previousChair->getKey())
                    ->where('is_active', true)
                    ->exists()) {
                    $this->syncChairRole($actor, $previousChair, false);
                }
            }

            if ($userId === null) {
                $this->auditLogger->succeeded(
                    $actor,
                    'admin.team.chair.removed',
                    $team,
                    'Ketua tim dilepas; histori penetapan tetap tersedia.',
                    $before,
                    null,
                );

                return null;
            }

            $user = User::query()->whereKey($userId)->where('is_active', true)->first();
            $isMember = TeamMembership::query()
                ->where('work_team_id', $team->getKey())
                ->where('user_id', $userId)
                ->where('is_active', true)
                ->exists();

            if ($user === null || ! $isMember) {
                throw ValidationException::withMessages([
                    'user_id' => 'Ketua tim harus merupakan pengguna aktif yang menjadi anggota tim tersebut.',
                ]);
            }

            $chair = TeamChairAssignment::query()->create([
                'work_team_id' => $team->getKey(),
                'user_id' => $userId,
                'assigned_by' => $actor->getKey(),
                'started_at' => $now,
                'is_active' => true,
            ])->load('user');

            $this->syncChairRole($actor, $user, true);

            $this->auditLogger->succeeded(
                $actor,
                'admin.team.chair.changed',
                $team,
                'Ketua tim diperbarui; histori penetapan disimpan.',
                $before,
                [
                    'user_id' => $userId,
                    'user_name' => $user->name,
                    'started_at' => $chair->started_at?->toIso8601String(),
                ],
            );

            return $chair;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createSkill(User $actor, array $data): Skill
    {
        return $this->database->transaction(function () use ($actor, $data): Skill {
            $skill = Skill::query()->create([
                'name' => $data['name'],
                'slug' => $this->resolveSlug($data['slug'] ?? null, $data['name']),
                'description' => $data['description'] ?? null,
                'is_active' => true,
            ]);

            $this->auditLogger->succeeded($actor, 'admin.skill.created', $skill, 'Keahlian dibuat.', null, $this->masterSnapshot($skill));

            return $skill;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateSkill(User $actor, Skill $skill, array $data): Skill
    {
        return $this->database->transaction(function () use ($actor, $skill, $data): Skill {
            $before = $this->masterSnapshot($skill);
            $skill->forceFill([
                'name' => $data['name'],
                'slug' => $this->resolveSlug($data['slug'] ?? null, $data['name']),
                'description' => $data['description'] ?? null,
            ])->save();
            $skill = $skill->fresh();

            $this->auditLogger->succeeded($actor, 'admin.skill.updated', $skill, 'Keahlian diperbarui.', $before, $this->masterSnapshot($skill));

            return $skill;
        });
    }

    public function setSkillStatus(User $actor, Skill $skill, bool $active): Skill
    {
        return $this->database->transaction(function () use ($actor, $skill, $active): Skill {
            $before = $this->masterSnapshot($skill);
            $skill->forceFill(['is_active' => $active])->save();
            $skill = $skill->fresh();

            $this->auditLogger->succeeded(
                $actor,
                $active ? 'admin.skill.activated' : 'admin.skill.deactivated',
                $skill,
                $active ? 'Keahlian diaktifkan.' : 'Keahlian dinonaktifkan.',
                $before,
                $this->masterSnapshot($skill),
            );

            return $skill;
        });
    }

    public function deleteSkill(User $actor, Skill $skill): void
    {
        $this->database->transaction(function () use ($actor, $skill): void {
            $before = $this->masterSnapshot($skill);
            $skill->forceFill(['is_active' => false])->save();
            $skill->delete();
            $this->auditLogger->succeeded($actor, 'admin.skill.deleted', $skill, 'Keahlian dihapus secara lunak agar histori tetap tersedia.', $before, ['deleted_at' => $skill->deleted_at?->toIso8601String()]);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createCategory(User $actor, array $data): ProblemCategory
    {
        return $this->database->transaction(function () use ($actor, $data): ProblemCategory {
            $category = ProblemCategory::query()->create([
                'name' => $data['name'],
                'slug' => $this->resolveSlug($data['slug'] ?? null, $data['name']),
                'description' => $data['description'] ?? null,
                'is_active' => true,
            ]);

            $this->auditLogger->succeeded($actor, 'admin.category.created', $category, 'Kategori masalah dibuat.', null, $this->masterSnapshot($category));

            return $category;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateCategory(User $actor, ProblemCategory $category, array $data): ProblemCategory
    {
        return $this->database->transaction(function () use ($actor, $category, $data): ProblemCategory {
            $before = $this->masterSnapshot($category);
            $category->forceFill([
                'name' => $data['name'],
                'slug' => $this->resolveSlug($data['slug'] ?? null, $data['name']),
                'description' => $data['description'] ?? null,
            ])->save();
            $category = $category->fresh();

            $this->auditLogger->succeeded($actor, 'admin.category.updated', $category, 'Kategori masalah diperbarui.', $before, $this->masterSnapshot($category));

            return $category;
        });
    }

    public function setCategoryStatus(User $actor, ProblemCategory $category, bool $active): ProblemCategory
    {
        return $this->database->transaction(function () use ($actor, $category, $active): ProblemCategory {
            $before = $this->masterSnapshot($category);
            $category->forceFill(['is_active' => $active])->save();
            $category = $category->fresh();

            $this->auditLogger->succeeded(
                $actor,
                $active ? 'admin.category.activated' : 'admin.category.deactivated',
                $category,
                $active ? 'Kategori masalah diaktifkan.' : 'Kategori masalah dinonaktifkan.',
                $before,
                $this->masterSnapshot($category),
            );

            return $category;
        });
    }

    public function deleteCategory(User $actor, ProblemCategory $category): void
    {
        $this->database->transaction(function () use ($actor, $category): void {
            $before = $this->masterSnapshot($category);
            $category->forceFill(['is_active' => false])->save();
            $category->delete();
            $this->auditLogger->succeeded($actor, 'admin.category.deleted', $category, 'Kategori masalah dihapus secara lunak agar histori tetap tersedia.', $before, ['deleted_at' => $category->deleted_at?->toIso8601String()]);
        });
    }

    /**
     * @param  iterable<int|string>  $skillIds
     */
    public function syncCategorySkills(User $actor, ProblemCategory $category, iterable $skillIds): ProblemCategory
    {
        return $this->database->transaction(fn (): ProblemCategory => $this->syncCategorySkillsInTransaction($actor, $category, $skillIds));
    }

    private function syncCategorySkillsInTransaction(User $actor, ProblemCategory $category, iterable $skillIds): ProblemCategory
    {
        $requestedIds = $this->normalizeIds($skillIds);
        $skills = Skill::query()->active()->whereIn('id', $requestedIds)->get()->keyBy('id');

        if ($skills->count() !== count($requestedIds)) {
            throw ValidationException::withMessages([
                'skill_ids' => 'Salah satu keahlian yang dipetakan tidak aktif atau tidak tersedia.',
            ]);
        }

        $currentIds = $category->skills()->pluck('skills.id')->map(fn ($id): int => (int) $id)->all();
        $toAttach = array_values(array_diff($requestedIds, $currentIds));
        $toDetach = array_values(array_diff($currentIds, $requestedIds));
        $now = now();

        if ($toDetach !== []) {
            $category->skills()->detach($toDetach);
        }

        foreach ($toAttach as $skillId) {
            $category->skills()->attach($skillId, [
                'assigned_by' => $actor->getKey(),
                'assigned_at' => $now,
            ]);
        }

        $this->auditLogger->succeeded(
            $actor,
            'admin.category.skills.updated',
            $category,
            'Pemetaan kategori masalah dan keahlian diperbarui.',
            ['skill_ids' => $this->skillSnapshot($currentIds)],
            ['skill_ids' => $this->skillSnapshot($requestedIds)],
        );

        return $category->fresh('skills');
    }

    /**
     * @param  iterable<int|string>  $ids
     * @return list<int>
     */
    private function normalizeIds(iterable $ids): array
    {
        return collect($ids)
            ->filter(fn ($id): bool => $id !== null && $id !== '')
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  iterable<int|string>  $roleIds
     * @return list<int>
     */
    private function roleIdsForTeamPosition(iterable $roleIds, TeamPosition $position): array
    {
        $requestedIds = $this->normalizeIds($roleIds);
        $chairRoleId = Role::query()
            ->where('slug', RoleEnum::KetuaTimKerja->value)
            ->value('id');

        if ($chairRoleId === null) {
            return $requestedIds;
        }

        $chairRoleId = (int) $chairRoleId;

        if ($position->isChair()) {
            $requestedIds[] = $chairRoleId;
        } else {
            $requestedIds = array_values(array_diff($requestedIds, [$chairRoleId]));
        }

        return array_values(array_unique($requestedIds));
    }

    private function syncChairRole(User $actor, User $user, bool $shouldHaveRole): void
    {
        $chairRoleId = Role::query()
            ->where('slug', RoleEnum::KetuaTimKerja->value)
            ->value('id');

        if ($chairRoleId === null) {
            return;
        }

        $chairRoleId = (int) $chairRoleId;
        $currentIds = $user->roles()->pluck('roles.id')->map(fn ($id): int => (int) $id)->all();
        $hasChairRole = in_array($chairRoleId, $currentIds, true);

        if ($hasChairRole === $shouldHaveRole) {
            return;
        }

        $requestedIds = $shouldHaveRole
            ? [...$currentIds, $chairRoleId]
            : array_values(array_diff($currentIds, [$chairRoleId]));

        $this->syncRolesInTransaction($actor, $user, $requestedIds);
    }

    private function clearUserChairAssignments(
        User $actor,
        User $user,
        ?int $keepTeamId = null,
        bool $removeChairRole = true,
    ): void
    {
        $activeChairAssignments = TeamChairAssignment::query()
            ->with('workTeam')
            ->where('user_id', $user->getKey())
            ->where('is_active', true)
            ->lockForUpdate()
            ->get();

        foreach ($activeChairAssignments as $assignment) {
            if ($keepTeamId !== null && (int) $assignment->work_team_id === $keepTeamId) {
                continue;
            }

            if ($assignment->workTeam !== null && $assignment->workTeam->is_active) {
                $this->assignChair($actor, $assignment->workTeam, null);
                continue;
            }

            $assignment->forceFill([
                'is_active' => false,
                'ended_at' => now(),
            ])->save();
        }

        if ($removeChairRole && ! TeamChairAssignment::query()
            ->where('user_id', $user->getKey())
            ->where('is_active', true)
            ->exists()) {
            $this->syncChairRole($actor, $user, false);
        }
    }

    /**
     * @param  list<int>  $ids
     * @return list<array{id:int,slug:string,name:string}>
     */
    private function roleSnapshot(array $ids): array
    {
        return Role::query()
            ->whereIn('id', $ids)
            ->orderBy('id')
            ->get(['id', 'slug', 'name'])
            ->map(fn (Role $role): array => ['id' => $role->id, 'slug' => $role->slug, 'name' => $role->name])
            ->values()
            ->all();
    }

    /**
     * @param  list<int>  $ids
     * @return list<array{id:int,slug:string,name:string}>
     */
    private function skillSnapshot(array $ids): array
    {
        return Skill::withTrashed()
            ->whereIn('id', $ids)
            ->orderBy('id')
            ->get(['id', 'slug', 'name'])
            ->map(fn (Skill $skill): array => ['id' => $skill->id, 'slug' => $skill->slug, 'name' => $skill->name])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function userSnapshot(User $user): array
    {
        $user = User::withTrashed()
            ->with(['roles', 'skills', 'currentTeamMembership.workTeam', 'teamChairAssignments'])
            ->find($user->getKey()) ?? $user;
        $teamId = $user->currentTeamMembership?->work_team_id;
        $isChair = $teamId !== null && $user->teamChairAssignments->contains(
            fn (TeamChairAssignment $assignment): bool => $assignment->is_active
                && (int) $assignment->work_team_id === (int) $teamId,
        );

        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'nip' => $user->nip,
            'is_active' => $user->is_active,
            'deleted_at' => $user->deleted_at?->toIso8601String(),
            'roles' => $user->roles->pluck('slug')->values()->all(),
            'skills' => $user->skills->pluck('slug')->values()->all(),
            'team_id' => $user->currentTeamMembership?->work_team_id,
            'team_name' => $user->currentTeamMembership?->workTeam?->name,
            'team_position' => $teamId === null ? null : ($isChair ? TeamPosition::Chair->value : TeamPosition::Member->value),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function teamSnapshot(WorkTeam $team): array
    {
        return [
            'id' => $team->id,
            'name' => $team->name,
            'description' => $team->description,
            'is_active' => $team->is_active,
            'deleted_at' => $team->deleted_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function masterSnapshot(Skill|ProblemCategory $master): array
    {
        return [
            'id' => $master->id,
            'name' => $master->name,
            'slug' => $master->slug,
            'description' => $master->description,
            'is_active' => $master->is_active,
            'deleted_at' => $master->deleted_at?->toIso8601String(),
        ];
    }

    private function resolveSlug(?string $slug, string $name): string
    {
        $resolved = trim((string) ($slug ?: Str::slug($name)));

        if ($resolved === '') {
            throw ValidationException::withMessages(['slug' => 'Kode tidak dapat dibuat dari nama yang diberikan.']);
        }

        return $resolved;
    }
}
