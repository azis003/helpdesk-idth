<?php

namespace App\Services;

use App\Models\Attachment;
use App\Models\AttachmentPolicy;
use App\Models\ServiceType;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class TicketAttachmentService
{
    public function policiesFor(ServiceType $serviceType, bool $includeInternal = false): Collection
    {
        return AttachmentPolicy::query()
            ->active()
            ->where(function ($query) use ($serviceType): void {
                $query->whereNull('service_type_id')
                    ->orWhere('service_type_id', $serviceType->getKey());
            })
            ->when(! $includeInternal, fn ($query) => $query->whereIn('visibility', ['requester', 'both']))
            ->with('serviceType')
            ->orderByRaw('service_type_id IS NOT NULL')
            ->orderBy('type_key')
            ->get();
    }

    /**
     * @param  array<int|string, mixed>  $fileGroups
     * @return list<array{policy:AttachmentPolicy,file:UploadedFile,mime:string,extension:string,size:int}>
     */
    public function validate(array $fileGroups, Collection $policies): array
    {
        $policyMap = $policies->keyBy(fn (AttachmentPolicy $policy): string => (string) $policy->getKey());
        $validated = [];

        foreach ($fileGroups as $policyId => $files) {
            if ($files instanceof UploadedFile) {
                $files = [$files];
            }

            if (! is_array($files)) {
                continue;
            }

            $files = array_values(array_filter($files, fn (mixed $file): bool => $file instanceof UploadedFile));

            if ($files === []) {
                continue;
            }

            $policy = $policyMap->get((string) $policyId);

            if ($policy === null) {
                throw ValidationException::withMessages([
                    'attachments' => 'Kebijakan lampiran yang dipilih tidak tersedia untuk layanan ini.',
                ]);
            }

            if (count($files) > $policy->max_file_count) {
                throw ValidationException::withMessages([
                    "attachments.{$policyId}" => "{$policy->label} hanya dapat memuat maksimal {$policy->max_file_count} berkas.",
                ]);
            }

            foreach ($files as $file) {
                if (! $file->isValid()) {
                    throw ValidationException::withMessages([
                        "attachments.{$policyId}" => "Berkas pada {$policy->label} gagal diunggah.",
                    ]);
                }

                $size = (int) ($file->getSize() ?: 0);
                $mime = strtolower((string) ($file->getMimeType() ?: $file->getClientMimeType()));
                $extension = strtolower($file->getClientOriginalExtension());

                if ($size > ($policy->max_file_size_kb * 1024)) {
                    throw ValidationException::withMessages([
                        "attachments.{$policyId}" => "Ukuran setiap berkas {$policy->label} maksimal {$policy->max_file_size_kb} KB.",
                    ]);
                }

                if (! $this->mimeAllowed($mime, $policy->allowed_mimes ?? [])) {
                    throw ValidationException::withMessages([
                        "attachments.{$policyId}" => "Tipe berkas pada {$policy->label} tidak diizinkan.",
                    ]);
                }

                if (($policy->allowed_extensions ?? []) !== []
                    && ! in_array($extension, array_map('strtolower', $policy->allowed_extensions), true)) {
                    throw ValidationException::withMessages([
                        "attachments.{$policyId}" => "Ekstensi berkas pada {$policy->label} tidak diizinkan.",
                    ]);
                }

                $validated[] = compact('policy', 'file', 'mime', 'extension', 'size');
            }
        }

        return $validated;
    }

    /**
     * @param  list<array{policy:AttachmentPolicy,file:UploadedFile,mime:string,extension:string,size:int}>  $files
     * @return list<Attachment>
     */
    public function store(Ticket $ticket, User $actor, array $files): array
    {
        $disk = 'local';
        $storedPaths = [];
        $attachments = [];

        try {
            foreach ($files as $item) {
                $policy = $item['policy'];
                $file = $item['file'];
                $extension = $item['extension'];
                $fileName = (string) Str::uuid().($extension !== '' ? ".{$extension}" : '');
                $path = $file->storeAs("tickets/{$ticket->getKey()}", $fileName, $disk);

                if ($path === false) {
                    throw new \RuntimeException('Penyimpanan lampiran gagal.');
                }

                $storedPaths[] = $path;
                $attachments[] = Attachment::query()->create([
                    'ticket_id' => $ticket->getKey(),
                    'attachment_policy_id' => $policy->getKey(),
                    'uploaded_by_id' => $actor->getKey(),
                    'type_key' => $policy->type_key,
                    'type_label_snapshot' => $policy->label,
                    'original_name' => $file->getClientOriginalName(),
                    'storage_disk' => $disk,
                    'storage_path' => $path,
                    'size_bytes' => $item['size'],
                    'mime_type' => $item['mime'],
                    'extension' => $extension !== '' ? $extension : null,
                    'sha256' => $file->getRealPath() ? hash_file('sha256', $file->getRealPath()) : null,
                    'visibility' => $policy->visibility,
                ]);
            }
        } catch (Throwable $exception) {
            Storage::disk($disk)->delete($storedPaths);

            throw $exception;
        }

        return $attachments;
    }

    private function mimeAllowed(string $mime, array $allowedMimes): bool
    {
        if ($allowedMimes === []) {
            return true;
        }

        foreach ($allowedMimes as $allowed) {
            $allowed = strtolower(trim((string) $allowed));

            if ($allowed === $mime || (str_ends_with($allowed, '/*') && str_starts_with($mime, rtrim($allowed, '*')))) {
                return true;
            }
        }

        return false;
    }
}
