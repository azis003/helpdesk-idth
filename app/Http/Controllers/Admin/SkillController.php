<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SkillRequest;
use App\Models\Skill;
use App\Services\DomainAuthorization;
use App\Services\OrganizationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SkillController extends Controller
{
    public function __construct(
        private readonly DomainAuthorization $authorization,
        private readonly OrganizationService $organization,
    ) {}

    public function index(Request $request): mixed
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'viewAny', Skill::class, 'admin.skills.view');

        return view('admin.skills.index', [
            'skills' => Skill::query()->with('serviceTypes')->orderBy('name')->get(),
        ]);
    }

    public function store(SkillRequest $request): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'create', Skill::class, 'admin.skill.create');
        $this->organization->createSkill($actor, $request->validated());

        return back()->with('success', 'Keahlian berhasil dibuat.');
    }

    public function update(SkillRequest $request, Skill $skill): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'update', $skill, 'admin.skill.update');
        $this->organization->updateSkill($actor, $skill, $request->validated());

        return back()->with('success', 'Keahlian berhasil diperbarui.');
    }

    public function activate(Request $request, Skill $skill): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'update', $skill, 'admin.skill.activate');
        $this->organization->setSkillStatus($actor, $skill, true);

        return back()->with('success', 'Keahlian berhasil diaktifkan.');
    }

    public function deactivate(Request $request, Skill $skill): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'update', $skill, 'admin.skill.deactivate');
        $this->organization->setSkillStatus($actor, $skill, false);

        return back()->with('success', 'Keahlian berhasil dinonaktifkan.');
    }

    public function destroy(Request $request, Skill $skill): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'delete', $skill, 'admin.skill.delete');
        $this->organization->deleteSkill($actor, $skill);

        return back()->with('success', 'Keahlian dihapus secara lunak; histori mapping tetap tersedia.');
    }
}
