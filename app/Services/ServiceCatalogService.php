<?php

namespace App\Services;

use App\Models\ServiceFieldDefinition;
use App\Models\ServiceType;
use App\Models\ServiceTypeVariant;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\DatabaseManager;
use Illuminate\Validation\ValidationException;

class ServiceCatalogService
{
    public const TICKET_CLASSES = ['INC', 'REQ', 'CHG'];

    /** @var array<string, string> */
    public const FIELD_TYPES = [
        'text' => 'Teks singkat',
        'textarea' => 'Teks panjang',
        'number' => 'Angka',
        'date' => 'Tanggal',
        'datetime' => 'Tanggal dan waktu',
        'select' => 'Pilihan tunggal',
        'multiselect' => 'Pilihan ganda',
        'boolean' => 'Ya/Tidak',
    ];

    /** @var array<string, string> */
    public const VISIBILITIES = [
        'requester' => 'Pemohon',
        'internal' => 'Tim TI',
        'both' => 'Pemohon dan Tim TI',
    ];

    public function __construct(
        private readonly DatabaseManager $database,
        private readonly AuditLogger $auditLogger,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateServiceType(
        User $actor,
        ServiceType $serviceType,
        array $data,
    ): ServiceType {
        return $this->database->transaction(function () use ($actor, $serviceType, $data): ServiceType {
            $before = $this->serviceTypeSnapshot($serviceType->load('variants'));
            $ticketClass = $data['ticket_class'] ?? null;

            if ($ticketClass !== null && ! in_array($ticketClass, self::TICKET_CLASSES, true)) {
                throw ValidationException::withMessages([
                    'ticket_class' => 'Kelas nomor harus INC, REQ, atau CHG.',
                ]);
            }

            if ($serviceType->code === 'SVC-05') {
                $this->syncHardwareVariants($serviceType, $data['variants'] ?? []);
                $ticketClass = null;
            } elseif ($ticketClass === null) {
                throw ValidationException::withMessages([
                    'ticket_class' => 'Kelas nomor wajib dipilih untuk layanan ini.',
                ]);
            }

            $serviceType->forceFill([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'ticket_class' => $ticketClass,
            ])->save();

            $fresh = $serviceType->fresh(['variants', 'activeFieldDefinitions.options']);

            $this->auditLogger->succeeded(
                $actor,
                'admin.service_type.updated',
                $fresh,
                'Konfigurasi layanan diperbarui.',
                $before,
                $this->serviceTypeSnapshot($fresh),
            );

            return $fresh;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createServiceType(User $actor, array $data): ServiceType
    {
        return $this->database->transaction(function () use ($actor, $data): ServiceType {
            $code = strtoupper(trim((string) $data['code']));

            if (ServiceType::withTrashed()->where('code', $code)->exists()) {
                throw ValidationException::withMessages([
                    'code' => 'Kode layanan sudah digunakan.',
                ]);
            }

            $serviceType = ServiceType::query()->create([
                'code' => $code,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'ticket_class' => $data['ticket_class'],
                'sort_order' => ((int) ServiceType::withTrashed()->max('sort_order')) + 1,
                'is_active' => true,
            ]);

            $this->syncServiceSkillsWithinTransaction($actor, $serviceType, $data['skill_ids'] ?? []);

            foreach ($data['fields'] ?? [] as $fieldData) {
                $this->createFieldRecord($actor, $serviceType, $fieldData);
            }

            $fresh = $serviceType->fresh(['variants', 'skills', 'activeFieldDefinitions.options']);

            $this->auditLogger->succeeded(
                $actor,
                'admin.service_type.created',
                $fresh,
                'Layanan baru dibuat.',
                null,
                $this->serviceTypeSnapshot($fresh),
            );

            return $fresh;
        });
    }

    public function setServiceStatus(
        User $actor,
        ServiceType $serviceType,
        bool $active,
    ): ServiceType {
        return $this->database->transaction(function () use ($actor, $serviceType, $active): ServiceType {
            $before = $this->serviceTypeSnapshot($serviceType->load('variants'));
            $serviceType->forceFill(['is_active' => $active])->save();
            $serviceType = $serviceType->fresh(['variants', 'activeFieldDefinitions.options']);

            $this->auditLogger->succeeded(
                $actor,
                $active ? 'admin.service_type.activated' : 'admin.service_type.deactivated',
                $serviceType,
                $active ? 'Layanan diaktifkan.' : 'Layanan dinonaktifkan.',
                $before,
                $this->serviceTypeSnapshot($serviceType),
            );

            return $serviceType;
        });
    }

    /**
     * @param  iterable<int|string>  $skillIds
     */
    public function syncServiceSkills(User $actor, ServiceType $serviceType, iterable $skillIds): ServiceType
    {
        return $this->database->transaction(fn (): ServiceType => $this->syncServiceSkillsWithinTransaction($actor, $serviceType, $skillIds));
    }

    /**
     * @param  iterable<int|string>  $skillIds
     */
    private function syncServiceSkillsWithinTransaction(User $actor, ServiceType $serviceType, iterable $skillIds): ServiceType
    {
        $requestedIds = $this->normalizeIds($skillIds);
        $skills = Skill::query()->active()->whereIn('id', $requestedIds)->get()->keyBy('id');

        if ($skills->count() !== count($requestedIds)) {
            throw ValidationException::withMessages([
                'skill_ids' => 'Salah satu keahlian yang dipetakan tidak aktif atau tidak tersedia.',
            ]);
        }

        $currentIds = $serviceType->skills()
            ->pluck('skills.id')
            ->map(fn ($id): int => (int) $id)
            ->all();
        $toAttach = array_values(array_diff($requestedIds, $currentIds));
        $toDetach = array_values(array_diff($currentIds, $requestedIds));
        $now = now();

        if ($toDetach !== []) {
            $serviceType->skills()->detach($toDetach);
        }

        foreach ($toAttach as $skillId) {
            $serviceType->skills()->attach($skillId, [
                'assigned_by' => $actor->getKey(),
                'assigned_at' => $now,
            ]);
        }

        $fresh = $serviceType->fresh(['variants', 'skills']);
        $this->auditLogger->succeeded(
            $actor,
            'admin.service_type.skills.updated',
            $fresh,
            'Pemetaan keahlian layanan diperbarui.',
            ['skill_ids' => $this->skillSnapshot($currentIds)],
            ['skill_ids' => $this->skillSnapshot($requestedIds)],
        );

        return $fresh;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createField(
        User $actor,
        ServiceType $serviceType,
        array $data,
    ): ServiceFieldDefinition {
        return $this->database->transaction(fn (): ServiceFieldDefinition => $this->createFieldRecord($actor, $serviceType, $data));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function createFieldRecord(User $actor, ServiceType $serviceType, array $data): ServiceFieldDefinition
    {
        $this->validateFieldData($data);

        if ($serviceType->fieldDefinitions()->where('key', $data['key'])->exists()) {
            throw ValidationException::withMessages([
                'key' => 'Kunci field tersebut sudah pernah digunakan pada layanan ini. Buat versi baru dari field yang ada.',
            ]);
        }

        $field = $serviceType->fieldDefinitions()->create([
            'key' => $data['key'],
            'label' => $data['label'],
            'help_text' => $data['help_text'] ?? null,
            'field_type' => $data['field_type'],
            'visibility' => $data['visibility'] ?? 'requester',
            'is_required' => (bool) ($data['is_required'] ?? false),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'version' => 1,
            'validation_rules' => array_values($data['validation_rules'] ?? []),
            'visibility_rules' => $data['visibility_rules'] ?? null,
            'is_active' => true,
        ]);

        $this->replaceOptions($field, $data['options'] ?? []);
        $field->load('options', 'serviceType');

        $this->auditLogger->succeeded(
            $actor,
            'admin.service_field.created',
            $field,
            'Field formulir dinamis dibuat.',
            null,
            $this->fieldSnapshot($field),
        );

        return $field;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createFieldVersion(
        User $actor,
        ServiceFieldDefinition $current,
        array $data,
    ): ServiceFieldDefinition {
        return $this->database->transaction(function () use ($actor, $current, $data): ServiceFieldDefinition {
            $this->validateFieldData($data);

            $current = ServiceFieldDefinition::query()
                ->whereKey($current->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $latestVersion = ServiceFieldDefinition::query()
                ->where('service_type_id', $current->service_type_id)
                ->where('key', $current->key)
                ->lockForUpdate()
                ->max('version');

            $version = ((int) $latestVersion) + 1;

            ServiceFieldDefinition::query()
                ->where('service_type_id', $current->service_type_id)
                ->where('key', $current->key)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            $field = ServiceFieldDefinition::query()->create([
                'service_type_id' => $current->service_type_id,
                'key' => $current->key,
                'label' => $data['label'],
                'help_text' => $data['help_text'] ?? null,
                'field_type' => $data['field_type'],
                'visibility' => $data['visibility'] ?? 'requester',
                'is_required' => (bool) ($data['is_required'] ?? false),
                'sort_order' => (int) ($data['sort_order'] ?? 0),
                'version' => $version,
                'validation_rules' => array_values($data['validation_rules'] ?? []),
                'visibility_rules' => $data['visibility_rules'] ?? null,
                'is_active' => true,
                'supersedes_id' => $current->getKey(),
            ]);

            $this->replaceOptions($field, $data['options'] ?? []);
            $field->load('options', 'serviceType');

            $this->auditLogger->succeeded(
                $actor,
                'admin.service_field.versioned',
                $field,
                'Versi baru field formulir dibuat; definisi lama dipertahankan.',
                $this->fieldSnapshot($current->load('options', 'serviceType')),
                $this->fieldSnapshot($field),
            );

            return $field;
        });
    }

    public function setFieldStatus(
        User $actor,
        ServiceFieldDefinition $field,
        bool $active,
    ): ServiceFieldDefinition {
        return $this->database->transaction(function () use ($actor, $field, $active): ServiceFieldDefinition {
            $before = $this->fieldSnapshot($field->load('options', 'serviceType'));

            if ($active) {
                ServiceFieldDefinition::query()
                    ->where('service_type_id', $field->service_type_id)
                    ->where('key', $field->key)
                    ->where($field->getKeyName(), '!=', $field->getKey())
                    ->update(['is_active' => false]);
            }

            $field->forceFill(['is_active' => $active])->save();
            $field = $field->fresh(['options', 'serviceType']);

            $this->auditLogger->succeeded(
                $actor,
                $active ? 'admin.service_field.activated' : 'admin.service_field.deactivated',
                $field,
                $active ? 'Field formulir diaktifkan.' : 'Field formulir dinonaktifkan.',
                $before,
                $this->fieldSnapshot($field),
            );

            return $field;
        });
    }

    /**
     * @param  array<int, mixed>  $variants
     */
    private function syncHardwareVariants(ServiceType $serviceType, array $variants): void
    {
        $byCode = collect($variants)
            ->filter(fn ($variant): bool => is_array($variant) && isset($variant['code']))
            ->keyBy('code');

        foreach (['repair', 'request'] as $requiredCode) {
            if (! $byCode->has($requiredCode)) {
                throw ValidationException::withMessages([
                    'variants' => 'SVC-05 wajib memiliki subjenis Perbaikan dan Permintaan.',
                ]);
            }
        }

        $mapping = ['repair' => 'INC', 'request' => 'REQ'];

        foreach ($mapping as $code => $ticketClass) {
            $variant = $byCode->get($code);

            if (trim((string) ($variant['label'] ?? '')) === '') {
                throw ValidationException::withMessages([
                    'variants' => 'Label setiap subjenis SVC-05 wajib diisi.',
                ]);
            }

            ServiceTypeVariant::query()->updateOrCreate(
                ['service_type_id' => $serviceType->getKey(), 'code' => $code],
                [
                    'label' => trim((string) $variant['label']),
                    'ticket_class' => $ticketClass,
                    'sort_order' => (int) ($variant['sort_order'] ?? ($code === 'repair' ? 1 : 2)),
                    'is_active' => true,
                ],
            );
        }
    }

    /**
     * @param  array<int, mixed>  $options
     */
    private function replaceOptions(ServiceFieldDefinition $field, array $options): void
    {
        $seen = [];

        foreach (array_values($options) as $index => $option) {
            if (! is_array($option)) {
                throw ValidationException::withMessages(['options' => 'Format pilihan field tidak valid.']);
            }

            $value = trim((string) ($option['value'] ?? ''));
            $label = trim((string) ($option['label'] ?? ''));

            if ($value === '' || ! preg_match('/^[A-Za-z0-9_-]+$/', $value)) {
                throw ValidationException::withMessages([
                    'options' => 'Nilai pilihan hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
                ]);
            }

            if ($label === '' || mb_strlen($label) > 150) {
                throw ValidationException::withMessages(['options' => 'Label pilihan wajib diisi dan maksimal 150 karakter.']);
            }

            if (isset($seen[$value])) {
                throw ValidationException::withMessages(['options' => 'Nilai pilihan tidak boleh duplikat.']);
            }

            $seen[$value] = true;
            $field->options()->create([
                'value' => $value,
                'label' => $label,
                'sort_order' => (int) ($option['sort_order'] ?? $index),
                'is_active' => (bool) ($option['is_active'] ?? true),
            ]);
        }

        if (in_array($field->field_type, ['select', 'multiselect'], true) && $field->options()->count() === 0) {
            throw ValidationException::withMessages([
                'options' => 'Field pilihan wajib memiliki minimal satu pilihan.',
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function validateFieldData(array $data): void
    {
        $key = trim((string) ($data['key'] ?? ''));
        $label = trim((string) ($data['label'] ?? ''));
        $fieldType = (string) ($data['field_type'] ?? '');
        $visibility = (string) ($data['visibility'] ?? 'requester');

        if ($key === '' || ! preg_match('/^[A-Za-z][A-Za-z0-9_-]*$/', $key)) {
            throw ValidationException::withMessages(['key' => 'Kunci field harus diawali huruf dan hanya boleh berisi huruf, angka, tanda hubung, atau garis bawah.']);
        }

        if ($label === '' || mb_strlen($label) > 150) {
            throw ValidationException::withMessages(['label' => 'Label field wajib diisi dan maksimal 150 karakter.']);
        }

        if (! array_key_exists($fieldType, self::FIELD_TYPES)) {
            throw ValidationException::withMessages(['field_type' => 'Tipe field tidak didukung.']);
        }

        if (! array_key_exists($visibility, self::VISIBILITIES)) {
            throw ValidationException::withMessages(['visibility' => 'Visibilitas field tidak didukung.']);
        }

        $rules = $data['validation_rules'] ?? [];

        if (! is_array($rules)) {
            throw ValidationException::withMessages(['validation_rules' => 'Aturan validasi field harus berupa daftar.']);
        }

        foreach ($rules as $rule) {
            if (! is_string($rule) || ! $this->isAllowedValidationRule($rule)) {
                throw ValidationException::withMessages([
                    'validation_rules' => 'Salah satu aturan validasi tidak diizinkan. Gunakan aturan sederhana yang tersedia pada petunjuk formulir.',
                ]);
            }
        }

        if (! in_array($fieldType, ['select', 'multiselect'], true) && ! empty($data['options'])) {
            throw ValidationException::withMessages([
                'options' => 'Pilihan hanya dapat digunakan pada field pilihan tunggal atau pilihan ganda.',
            ]);
        }
    }

    private function isAllowedValidationRule(string $rule): bool
    {
        $rule = trim($rule);

        if (in_array($rule, ['nullable', 'string', 'integer', 'numeric', 'boolean', 'date', 'email', 'url', 'array'], true)) {
            return true;
        }

        return preg_match('/^(min|max):\d+(?:\.\d+)?$/', $rule) === 1
            || preg_match('/^between:\d+(?:\.\d+)?,\d+(?:\.\d+)?$/', $rule) === 1
            || preg_match('/^date_format:[A-Za-z0-9_\\\-: ]+$/', $rule) === 1
            || preg_match('/^required_if:[A-Za-z][A-Za-z0-9_-]*,[A-Za-z0-9_-]+$/', $rule) === 1;
    }

    /**
     * @return array<string, mixed>
     */
    private function serviceTypeSnapshot(ServiceType $serviceType): array
    {
        return [
            'id' => $serviceType->id,
            'code' => $serviceType->code,
            'name' => $serviceType->name,
            'description' => $serviceType->description,
            'ticket_class' => $serviceType->ticket_class,
            'is_active' => $serviceType->is_active,
            'skills' => $serviceType->skills->map(fn (Skill $skill): array => [
                'id' => $skill->id,
                'slug' => $skill->slug,
                'name' => $skill->name,
                'is_active' => $skill->is_active,
            ])->values()->all(),
            'variants' => $serviceType->variants->map(fn (ServiceTypeVariant $variant): array => [
                'code' => $variant->code,
                'label' => $variant->label,
                'ticket_class' => $variant->ticket_class,
                'is_active' => $variant->is_active,
            ])->values()->all(),
        ];
    }

    /**
     * @param  iterable<int|string>  $ids
     * @return list<int>
     */
    private function normalizeIds(iterable $ids): array
    {
        return collect($ids)
            ->filter(fn ($id): bool => $id !== null && $id !== '')
            ->map(fn ($id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  list<int>  $ids
     * @return list<array{id:int,slug:string,name:string}>
     */
    private function skillSnapshot(array $ids): array
    {
        return Skill::withTrashed()
            ->whereIn('id', $ids)
            ->orderBy('id')
            ->get(['id', 'slug', 'name'])
            ->map(fn (Skill $skill): array => [
                'id' => $skill->id,
                'slug' => $skill->slug,
                'name' => $skill->name,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function fieldSnapshot(ServiceFieldDefinition $field): array
    {
        return [
            'id' => $field->id,
            'service_type' => $field->serviceType?->code,
            'key' => $field->key,
            'label' => $field->label,
            'field_type' => $field->field_type,
            'visibility' => $field->visibility,
            'is_required' => $field->is_required,
            'sort_order' => $field->sort_order,
            'version' => $field->version,
            'validation_rules' => $field->validation_rules ?? [],
            'options' => $field->options->map(fn ($option): array => [
                'value' => $option->value,
                'label' => $option->label,
                'sort_order' => $option->sort_order,
                'is_active' => $option->is_active,
            ])->values()->all(),
            'is_active' => $field->is_active,
        ];
    }
}
