<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AssignTeamRequest;
use App\Http\Requests\Admin\ResetPasswordRequest;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Http\Requests\Admin\UpdateUserRolesRequest;
use App\Http\Requests\Admin\UpdateUserSkillsRequest;
use App\Models\Role;
use App\Models\Skill;
use App\Models\User;
use App\Models\WorkTeam;
use App\Services\AuditLogger;
use App\Services\DomainAuthorization;
use App\Services\OrganizationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    public function __construct(
        private readonly DomainAuthorization $authorization,
        private readonly AuditLogger $auditLogger,
        private readonly OrganizationService $organization,
    ) {}

    public function index(Request $request): mixed
    {
        $this->authorization->authorize($request->user(), 'viewAny', User::class, 'admin.users.view');

        return view('admin.users.index', [
            'users' => User::query()
                ->with(['roles', 'skills', 'currentTeamMembership.workTeam'])
                ->orderBy('name')
                ->paginate(15),
            'userStats' => [
                'total' => User::query()->count(),
                'active' => User::query()->where('is_active', true)->count(),
                'without_team' => User::query()->whereDoesntHave('currentTeamMembership')->count(),
                'password_pending' => User::query()->where('must_change_password', true)->count(),
            ],
        ]);
    }

    public function create(Request $request): mixed
    {
        $this->authorization->authorize($request->user(), 'create', User::class, 'admin.user.create');

        return view('admin.users.create', [
            'roles' => Role::query()->orderBy('id')->get(),
            'teams' => WorkTeam::query()->active()->orderBy('name')->get(),
            'skills' => Skill::query()->active()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'create', User::class, 'admin.user.create');
        $user = $this->organization->createUser($actor, $request->validated());

        return redirect()
            ->route('admin.users.edit', $user)
            ->with('success', "Pengguna {$user->name} berhasil dibuat. Sampaikan password awal melalui prosedur aman.");
    }

    public function edit(Request $request, User $user): mixed
    {
        $this->authorization->authorize($request->user(), 'update', $user, 'admin.user.view');

        $user->load([
            'roles',
            'skills',
            'currentTeamMembership.workTeam',
            'teamMemberships.workTeam',
            'teamChairAssignments.workTeam',
        ]);

        return view('admin.users.edit', [
            'user' => $user,
            'roles' => Role::query()->orderBy('id')->get(),
            'teams' => WorkTeam::query()->active()->orderBy('name')->get(),
            'skills' => Skill::query()->active()->orderBy('name')->get(),
            'roleHistories' => $user->roleAssignmentHistories()->with('role')->latest('occurred_at')->get(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'update', $user, 'admin.user.update');
        $data = $request->validated();

        $this->authorization->authorize($actor, 'manageRoles', $user, 'admin.user.roles.update');
        $this->authorization->authorize($actor, 'manageTeam', $user, 'admin.user.team.update');
        $this->authorization->authorize($actor, 'manageSkills', $user, 'admin.user.skills.update');
        $this->organization->updateUser($actor, $user, $data);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Perubahan pengguna berhasil disimpan.');
    }

    public function activate(Request $request, User $user): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'activate', $user, 'admin.user.activate');
        $this->organization->setUserStatus($actor, $user, true);

        return back()->with('success', "Akun {$user->name} berhasil diaktifkan.");
    }

    public function deactivate(Request $request, User $user): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'deactivate', $user, 'admin.user.deactivate');
        $this->organization->setUserStatus($actor, $user, false);

        return back()->with('success', "Akun {$user->name} berhasil dinonaktifkan.");
    }

    public function updateRoles(UpdateUserRolesRequest $request, User $user): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'manageRoles', $user, 'admin.user.roles.update');
        $this->organization->syncRoles($actor, $user, $request->validated()['role_ids'] ?? []);

        return back()->with('success', 'Role pengguna berhasil diperbarui.');
    }

    public function updateTeam(AssignTeamRequest $request, User $user): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'manageTeam', $user, 'admin.user.team.update');
        $this->organization->assignTeam($actor, $user, (int) $request->validated('team_id'));

        return back()->with('success', 'Tim utama pengguna berhasil diperbarui.');
    }

    public function removeTeam(Request $request, User $user): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'manageTeam', $user, 'admin.user.team.remove');
        $this->organization->removeTeam($actor, $user);

        return back()->with('success', 'Pengguna berhasil dikeluarkan dari tim utama.');
    }

    public function updateSkills(UpdateUserSkillsRequest $request, User $user): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'manageSkills', $user, 'admin.user.skills.update');
        $this->organization->syncSkills($actor, $user, $request->validated()['skill_ids'] ?? []);

        return back()->with('success', 'Keahlian pengguna berhasil diperbarui.');
    }

    public function editReset(Request $request, User $user): mixed
    {
        $this->authorization->authorize($request->user(), 'resetPassword', $user, 'user.password_reset.view');

        return view('admin.users.reset-password', ['user' => $user->load('roles')]);
    }

    public function resetPassword(ResetPasswordRequest $request, User $user): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'resetPassword', $user, 'user.password_reset');

        $before = [
            'must_change_password' => $user->must_change_password,
            'password_changed_at' => $user->password_changed_at?->toIso8601String(),
        ];

        $user->forceFill([
            'password' => Hash::make($request->string('temporary_password')->toString()),
            'must_change_password' => true,
            'password_changed_at' => null,
            'remember_token' => null,
        ])->save();

        $this->auditLogger->succeeded(
            $actor,
            'user.password_reset',
            $user,
            'Password sementara dibuat; distribusikan melalui prosedur aman.',
            $before,
            ['must_change_password' => true, 'password_changed_at' => null],
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Password {$user->name} berhasil direset. Sampaikan password sementara melalui prosedur aman.");
    }
}
