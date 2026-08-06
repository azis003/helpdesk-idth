<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Services\AuditLogger;
use App\Services\DomainAuthorization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    public function __construct(
        private readonly DomainAuthorization $authorization,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function download(Request $request, Attachment $attachment): mixed
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'view', $attachment, 'attachment.download');

        $disk = Storage::disk($attachment->storage_disk);

        if (! $disk->exists($attachment->storage_path)) {
            $this->auditLogger->denied($actor, 'attachment.download', $attachment, 'Berkas lampiran tidak ditemukan.');
            abort(404, 'Lampiran tidak ditemukan.');
        }

        $this->auditLogger->succeeded($actor, 'attachment.download', $attachment, 'Lampiran diakses.');

        return $disk->download(
            $attachment->storage_path,
            $attachment->original_name,
            array_filter(['Content-Type' => $attachment->mime_type]),
        );
    }
}
