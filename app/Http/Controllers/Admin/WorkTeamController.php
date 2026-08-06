<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AssignTeamChairRequest;
use App\Http\Requests\Admin\AssignTeamMemberRequest;
use App\Http\Requests\Admin\WorkTeamRequest;
use App\Models\User;
use App\Models\WorkTeam;
use App\Services\DomainAuthorization;
use App\Services\OrganizationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WorkTeamController extends Controller
{
    public function __construct(
        private readonly DomainAuthorization $authorization,
        private readonly OrganizationService $organization,
    ) {}

    public function index(Request $request): mixed
    {
        $this->authorization->authorize($request->user(), 'viewAny', WorkTeam::class, 'admin.teams.view');

        return view('admin.teams.index', [
            'teams' => WorkTeam::query()
                ->with(['currentMembers' => fn ($query) => $query->orderBy('name'), 'currentChair.user'])
                ->orderBy('name')
                ->get(),
            'users' => User::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(WorkTeamRequest $request): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'create', WorkTeam::class, 'admin.team.create');
        $this->organization->createTeam($actor, $request->validated());

        return back()->with('success', 'Tim kerja berhasil dibuat.');
    }

    public function update(WorkTeamRequest $request, WorkTeam $workTeam): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'update', $workTeam, 'admin.team.update');
        $this->organization->updateTeam($actor, $workTeam, $request->validated());

        return back()->with('success', 'Tim kerja berhasil diperbarui.');
    }

    public function activate(Request $request, WorkTeam $workTeam): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'update', $workTeam, 'admin.team.activate');
        $this->organization->setTeamStatus($actor, $workTeam, true);

        return back()->with('success', 'Tim kerja berhasil diaktifkan.');
    }

    public function deactivate(Request $request, WorkTeam $workTeam): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'update', $workTeam, 'admin.team.deactivate');
        $this->organization->setTeamStatus($actor, $workTeam, false);

        return back()->with('success', 'Tim kerja berhasil dinonaktifkan.');
    }

    public function destroy(Request $request, WorkTeam $workTeam): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'delete', $workTeam, 'admin.team.delete');
        $this->organization->deleteTeam($actor, $workTeam);

        return back()->with('success', 'Tim kerja dihapus secara lunak; histori tetap tersedia.');
    }

    public function assignMember(AssignTeamMemberRequest $request, WorkTeam $workTeam): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'assignMember', $workTeam, 'admin.team.member.assign');
        $user = User::query()->findOrFail((int) $request->validated('user_id'));
        $this->organization->assignTeam($actor, $user, $workTeam->getKey());

        return back()->with('success', "{$user->name} berhasil ditetapkan sebagai anggota tim.");
    }

    public function removeMember(Request $request, WorkTeam $workTeam, User $user): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'assignMember', $workTeam, 'admin.team.member.remove');

        $isMember = $user->currentTeamMembership()
            ->where('work_team_id', $workTeam->getKey())
            ->exists();

        if (! $isMember) {
            return back()->withErrors(['team' => 'Pengguna tersebut bukan anggota aktif tim ini.']);
        }

        $this->organization->removeTeam($actor, $user);

        return back()->with('success', "{$user->name} berhasil dikeluarkan dari tim.");
    }

    public function assignChair(AssignTeamChairRequest $request, WorkTeam $workTeam): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'assignChair', $workTeam, 'admin.team.chair.update');
        $userId = $request->validated('user_id');
        $this->organization->assignChair($actor, $workTeam, $userId === null ? null : (int) $userId);

        return back()->with('success', $userId === null ? 'Ketua tim berhasil dilepas.' : 'Ketua tim berhasil diperbarui.');
    }
}
