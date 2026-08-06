<?php

namespace App\Services;

use App\Models\AttachmentPolicy;
use App\Models\User;
use Illuminate\Database\DatabaseManager;
use Illuminate\Validation\ValidationException;

class AttachmentPolicyService
{
    public function __construct(
        private readonly DatabaseManager $database,
        private readonly AuditLogger $auditLogger,
    ) {}

    /** @param array<string, mixed> $data */
    public function create(User $actor, array $data): AttachmentPolicy
    {
        return $this->database->transaction(function () use ($actor, $data): AttachmentPolicy {
            $this->validate($data);
            $this->ensureUnique($data['service_type_id'] ?? null, $data['type_key']);

            $policy = AttachmentPolicy::query()->create($this->attributes($data));
            $policy->load('serviceType');
            $this->auditLogger->succeeded($actor, 'admin.attachment_policy.created', $policy, 'Kebijakan lampiran dibuat.', null, $this->snapshot($policy));

            return $policy;
        });
    }

    /** @param array<string, mixed> $data */
    public function update(User $actor, AttachmentPolicy $policy, array $data): AttachmentPolicy
    {
        return $this->database->transaction(function () use ($actor, $policy, $data): AttachmentPolicy {
            $this->validate($data);
            $this->ensureUnique($data['service_type_id'] ?? null, $data['type_key'], $policy);
            $before = $this->snapshot($policy->load('serviceType'));
            $policy->forceFill($this->attributes($data))->save();
            $policy = $policy->fresh('serviceType');
            $this->auditLogger->succeeded($actor, 'admin.attachment_policy.updated', $policy, 'Kebijakan lampiran diperbarui.', $before, $this->snapshot($policy));

            return $policy;
        });
    }

    public function setStatus(User $actor, AttachmentPolicy $policy, bool $active): AttachmentPolicy
    {
        return $this->database->transaction(function () use ($actor, $policy, $active): AttachmentPolicy {
            $before = $this->snapshot($policy->load('serviceType'));
            $policy->forceFill(['is_active' => $active])->save();
            $policy = $policy->fresh('serviceType');
            $this->auditLogger->succeeded($actor, $active ? 'admin.attachment_policy.activated' : 'admin.attachment_policy.deactivated', $policy, $active ? 'Kebijakan lampiran diaktifkan.' : 'Kebijakan lampiran dinonaktifkan.', $before, $this->snapshot($policy));

            return $policy;
        });
    }

    /** @param array<string, mixed> $data */
    private function attributes(array $data): array
    {
        return [
            'service_type_id' => ($data['service_type_id'] ?? null) ?: null,
            'type_key' => trim($data['type_key']),
            'label' => trim($data['label']),
            'max_file_size_kb' => (int) $data['max_file_size_kb'],
            'max_file_count' => (int) $data['max_file_count'],
            'allowed_mimes' => array_values($data['allowed_mimes'] ?? []),
            'allowed_extensions' => array_values($data['allowed_extensions'] ?? []),
            'visibility' => $data['visibility'] ?? 'both',
            'is_active' => (bool) ($data['is_active'] ?? true),
        ];
    }

    /** @param array<string, mixed> $data */
    private function validate(array $data): void
    {
        if (! preg_match('/^[A-Za-z][A-Za-z0-9_-]*$/', trim((string) ($data['type_key'] ?? '')))) {
            throw ValidationException::withMessages(['type_key' => 'Kunci tipe lampiran hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.']);
        }

        if (empty($data['allowed_mimes']) && empty($data['allowed_extensions'])) {
            throw ValidationException::withMessages(['allowed_mimes' => 'Isi minimal satu MIME atau ekstensi yang diizinkan.']);
        }

        foreach ($data['allowed_mimes'] ?? [] as $mime) {
            if (! preg_match('/^[A-Za-z0-9.+-]+\/[A-Za-z0-9.+*-]+$/', $mime)) {
                throw ValidationException::withMessages(['allowed_mimes' => 'Format MIME tidak valid.']);
            }
        }

        foreach ($data['allowed_extensions'] ?? [] as $extension) {
            if (! preg_match('/^[A-Za-z0-9]+$/', $extension)) {
                throw ValidationException::withMessages(['allowed_extensions' => 'Ekstensi hanya boleh berisi huruf dan angka tanpa titik.']);
            }
        }
    }

    private function ensureUnique(?int $serviceTypeId, string $typeKey, ?AttachmentPolicy $ignore = null): void
    {
        $query = AttachmentPolicy::withTrashed()
            ->where('type_key', trim($typeKey));

        $serviceTypeId === null
            ? $query->whereNull('service_type_id')
            : $query->where('service_type_id', $serviceTypeId);

        if ($ignore !== null) {
            $query->where($ignore->getKeyName(), '!=', $ignore->getKey());
        }

        if ($query->exists()) {
            throw ValidationException::withMessages(['type_key' => 'Tipe lampiran tersebut sudah digunakan pada cakupan yang sama.']);
        }
    }

    /** @return array<string, mixed> */
    private function snapshot(AttachmentPolicy $policy): array
    {
        return [
            'id' => $policy->id,
            'service_type' => $policy->serviceType?->code,
            'type_key' => $policy->type_key,
            'label' => $policy->label,
            'max_file_size_kb' => $policy->max_file_size_kb,
            'max_file_count' => $policy->max_file_count,
            'allowed_mimes' => $policy->allowed_mimes ?? [],
            'allowed_extensions' => $policy->allowed_extensions ?? [],
            'visibility' => $policy->visibility,
            'is_active' => $policy->is_active,
        ];
    }
}
