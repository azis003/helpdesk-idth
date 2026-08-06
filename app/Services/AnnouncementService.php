<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Database\DatabaseManager;

class AnnouncementService
{
    public function __construct(
        private readonly DatabaseManager $database,
        private readonly AuditLogger $auditLogger,
    ) {}

    /** @param array<string, mixed> $data */
    public function create(User $actor, array $data): Announcement
    {
        return $this->database->transaction(function () use ($actor, $data): Announcement {
            $announcement = Announcement::query()->create([
                'title' => trim($data['title']),
                'body' => trim($data['body']),
                'starts_at' => $data['starts_at'],
                'ends_at' => $data['ends_at'] ?? null,
                'is_active' => (bool) ($data['is_active'] ?? true),
                'created_by' => $actor->getKey(),
            ]);
            $announcement->load('creator');
            $this->auditLogger->succeeded($actor, 'admin.announcement.created', $announcement, 'Pengumuman global dibuat.', null, $this->snapshot($announcement));

            return $announcement;
        });
    }

    /** @param array<string, mixed> $data */
    public function update(User $actor, Announcement $announcement, array $data): Announcement
    {
        return $this->database->transaction(function () use ($actor, $announcement, $data): Announcement {
            $before = $this->snapshot($announcement->load('creator'));
            $announcement->forceFill([
                'title' => trim($data['title']),
                'body' => trim($data['body']),
                'starts_at' => $data['starts_at'],
                'ends_at' => $data['ends_at'] ?? null,
                'is_active' => (bool) ($data['is_active'] ?? false),
            ])->save();
            $announcement = $announcement->fresh('creator');
            $this->auditLogger->succeeded($actor, 'admin.announcement.updated', $announcement, 'Pengumuman global diperbarui.', $before, $this->snapshot($announcement));

            return $announcement;
        });
    }

    public function setStatus(User $actor, Announcement $announcement, bool $active): Announcement
    {
        return $this->database->transaction(function () use ($actor, $announcement, $active): Announcement {
            $before = $this->snapshot($announcement->load('creator'));
            $announcement->forceFill(['is_active' => $active])->save();
            $announcement = $announcement->fresh('creator');
            $this->auditLogger->succeeded($actor, $active ? 'admin.announcement.activated' : 'admin.announcement.deactivated', $announcement, $active ? 'Pengumuman diaktifkan.' : 'Pengumuman dinonaktifkan.', $before, $this->snapshot($announcement));

            return $announcement;
        });
    }

    /** @return array<string, mixed> */
    private function snapshot(Announcement $announcement): array
    {
        return [
            'id' => $announcement->id,
            'title' => $announcement->title,
            'body' => $announcement->body,
            'starts_at' => $announcement->starts_at?->toIso8601String(),
            'ends_at' => $announcement->ends_at?->toIso8601String(),
            'is_active' => $announcement->is_active,
            'created_by' => $announcement->creator?->username,
        ];
    }
}
