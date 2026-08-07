<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Services\AuditLogger;
use App\Services\DomainAuthorization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class AttachmentController extends Controller
{
    public function __construct(
        private readonly DomainAuthorization $authorization,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function download(Request $request, Attachment $attachment): mixed
    {
        $actor = $request->user();

        if ($actor === null || $attachment->trashed()) {
            $this->auditLogger->denied(
                $actor,
                'attachment.download',
                $attachment,
                $attachment->trashed()
                    ? 'Lampiran sudah dihapus dari storage.'
                    : 'Aktor tidak terautentikasi.',
            );
            abort(404, 'Lampiran tidak ditemukan.');
        }

        $this->authorization->authorize($actor, 'view', $attachment, 'attachment.download');

        if ($attachment->storage_disk !== config('filesystems.attachment_disk', 'local')) {
            $this->auditLogger->denied(
                $actor,
                'attachment.download',
                $attachment,
                'Lampiran tidak berada pada private storage yang dikonfigurasi.',
            );
            abort(404, 'Lampiran tidak ditemukan.');
        }

        try {
            $disk = Storage::disk($attachment->storage_disk);
        } catch (Throwable) {
            $this->auditLogger->denied($actor, 'attachment.download', $attachment, 'Berkas lampiran gagal disiapkan.');
            abort(404, 'Lampiran tidak ditemukan.');
        }

        if (! $disk->exists($attachment->storage_path)) {
            $this->auditLogger->denied($actor, 'attachment.download', $attachment, 'Berkas lampiran tidak ditemukan.');
            abort(404, 'Lampiran tidak ditemukan.');
        }

        try {
            $response = $disk->download(
                $attachment->storage_path,
                $this->downloadName($attachment->original_name),
                [
                    'Cache-Control' => 'private, no-store',
                    'X-Content-Type-Options' => 'nosniff',
                    ...array_filter(['Content-Type' => $attachment->mime_type]),
                ],
            );
        } catch (Throwable) {
            $this->auditLogger->denied($actor, 'attachment.download', $attachment, 'Berkas lampiran gagal disiapkan.');
            abort(404, 'Lampiran tidak ditemukan.');
        }

        $this->auditLogger->succeeded($actor, 'attachment.download', $attachment, 'Lampiran diakses.');

        return $response;
    }

    private function downloadName(string $name): string
    {
        $name = str_replace(["\0", "\r", "\n", '/', '\\'], '-', $name);
        $name = trim(Str::limit($name, 255, ''));

        return $name !== '' ? $name : 'lampiran';
    }
}
