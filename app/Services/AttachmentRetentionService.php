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
        $cutoff = $now->copy()->subDays(self::DATA_EXPORT_RETENTION_DAYS);
        $deleted = 0;

        Attachment::query()
            ->where('type_key', 'data_export_result')
            ->whereHas('ticket', fn ($query) => $query
                ->whereNotNull('closed_at')
                ->where('closed_at', '<=', $cutoff))
            ->with('ticket')
            ->orderBy('id')
            ->each(function (Attachment $attachment) use ($now, &$deleted): void {
                $before = $this->metadata($attachment);
                $storageDeleted = Storage::disk($attachment->storage_disk)->delete($attachment->storage_path);
                $attachment->delete();

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
