<?php

namespace App\Services;

use App\Models\Attachment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class AttachmentRetentionService
{
    public const DATA_EXPORT_RETENTION_DAYS = 90;

    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function purgeDataExportResults(?Carbon $now = null): int
    {
        $now ??= Carbon::now(config('app.timezone'));
        $cutoff = $now->copy()->subDays((int) config('retention.data_export_days', self::DATA_EXPORT_RETENTION_DAYS));
        $deleted = 0;

        Attachment::withTrashed()
            ->where('type_key', 'data_export_result')
            ->whereHas('ticket', function ($query) use ($cutoff): void {
                $query
                    ->where(function ($ticketQuery): void {
                        $ticketQuery
                            ->where('service_type_code_snapshot', 'SVC-02')
                            ->orWhereHas('serviceType', fn ($serviceQuery) => $serviceQuery->where('code', 'SVC-02'));
                    })
                    ->whereNotNull('closed_at')
                    ->where('closed_at', '<=', $cutoff);
            })
            ->with('ticket')
            ->orderBy('id')
            ->each(function (Attachment $attachment) use ($now, &$deleted): void {
                $storage = Storage::disk($attachment->storage_disk);
                $before = $this->metadata($attachment);
                $storageExisted = $storage->exists($attachment->storage_path);

                // A previously completed purge is already soft-deleted and has
                // no physical file left. Skipping it keeps the job idempotent.
                if ($attachment->trashed() && ! $storageExisted) {
                    return;
                }

                $storageDeleted = ! $storageExisted || $storage->delete($attachment->storage_path);

                if (! $storageDeleted) {
                    $this->auditLogger->denied(
                        null,
                        'attachment.retention_delete_failed',
                        $attachment,
                        'Berkas hasil tarik data belum dapat dihapus dari storage; job akan mencoba kembali.',
                    );

                    return;
                }

                if (! $attachment->trashed()) {
                    $attachment->delete();
                }

                $this->auditLogger->succeeded(
                    null,
                    'attachment.retention_deleted',
                    $attachment,
                    'Hasil tarik data dihapus setelah masa retensi 90 hari.',
                    $before,
                    [
                        ...$before,
                        'deleted_at' => $now->toIso8601String(),
                        'storage_deleted' => $storageDeleted,
                        'storage_existed' => $storageExisted,
                    ],
                );
                $deleted++;
            });

        return $deleted;
    }

    /** @return array<string, mixed> */
    private function metadata(Attachment $attachment): array
    {
        return [
            'attachment_id' => $attachment->getKey(),
            'ticket_id' => $attachment->ticket_id,
            'type_key' => $attachment->type_key,
            'type_label' => $attachment->type_label_snapshot,
            'original_name' => $attachment->original_name,
            'size_bytes' => $attachment->size_bytes,
            'mime_type' => $attachment->mime_type,
            'extension' => $attachment->extension,
            'sha256' => $attachment->sha256,
            'visibility' => $attachment->visibility,
            'uploaded_by_id' => $attachment->uploaded_by_id,
        ];
    }
}
