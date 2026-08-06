<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AnnouncementRequest;
use App\Models\Announcement;
use App\Services\AnnouncementService;
use App\Services\DomainAuthorization;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function __construct(
        private readonly DomainAuthorization $authorization,
        private readonly AnnouncementService $announcements,
    ) {}

    public function index(Request $request): mixed
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'viewAny', Announcement::class, 'admin.announcements.view');

        return view('admin.announcements.index', [
            'announcements' => Announcement::query()->with('creator')->latest('starts_at')->latest('id')->get(),
        ]);
    }

    public function store(AnnouncementRequest $request): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'create', Announcement::class, 'admin.announcement.create');
        $this->announcements->create($actor, $this->payload($request));

        return back()->with('success', 'Pengumuman global berhasil dibuat.');
    }

    public function update(AnnouncementRequest $request, Announcement $announcement): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'update', $announcement, 'admin.announcement.update');
        $this->announcements->update($actor, $announcement, $this->payload($request));

        return back()->with('success', 'Pengumuman global berhasil diperbarui.');
    }

    public function setStatus(Request $request, Announcement $announcement, string $status): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'update', $announcement, 'admin.announcement.status');
        $this->announcements->setStatus($actor, $announcement, $status === 'activate');

        return back()->with('success', $status === 'activate' ? 'Pengumuman diaktifkan.' : 'Pengumuman dinonaktifkan.');
    }

    /** @return array<string, mixed> */
    private function payload(AnnouncementRequest $request): array
    {
        $data = $request->validated();
        $timezone = config('app.timezone', 'Asia/Jakarta');

        return [
            ...$data,
            'starts_at' => CarbonImmutable::createFromFormat('Y-m-d\\TH:i', $data['starts_at'], $timezone),
            'ends_at' => empty($data['ends_at']) ? null : CarbonImmutable::createFromFormat('Y-m-d\\TH:i', $data['ends_at'], $timezone),
            'is_active' => (bool) ($data['is_active'] ?? false),
        ];
    }
}
