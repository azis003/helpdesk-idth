<?php

namespace App\Services;

use App\Models\OperationalSetting;
use App\Models\ServiceCalendar;
use App\Models\ServiceType;
use App\Models\SlaPolicy;
use App\Models\User;
use Illuminate\Database\DatabaseManager;
use Illuminate\Validation\ValidationException;

class OperationalPolicyService
{
    public const TIMEZONE = 'Asia/Jakarta';

    /** @var list<int> */
    public const DEFAULT_WORKING_DAYS = [1, 2, 3, 4, 5];

    /** @var array<string, int> */
    public const DEFAULT_SETTINGS = [
        'sla_warning_percent' => 20,
        'requester_wait_working_days' => 3,
        'confirmation_wait_working_days' => 3,
        'reopen_window_working_days' => 7,
        'max_reopen_count' => 3,
    ];

    public function __construct(
        private readonly DatabaseManager $database,
        private readonly AuditLogger $auditLogger,
    ) {}

    /** @param list<array{service_type_id:int,uses_sla:bool,target_working_days:int|null}> $policies */
    public function updateSlaPolicies(User $actor, array $policies): void
    {
        $services = ServiceType::query()->orderBy('id')->get();
        $requested = collect($policies)->keyBy('service_type_id');

        if ($requested->keys()->sort()->values()->all() !== $services->pluck('id')->sort()->values()->all()) {
            throw ValidationException::withMessages([
                'policies' => 'Kebijakan SLA harus dikirim untuk seluruh layanan yang tersedia.',
            ]);
        }

        $this->database->transaction(function () use ($actor, $services, $requested): void {
            foreach ($services as $serviceType) {
                /** @var array{service_type_id:int,uses_sla:bool,target_working_days:int|null} $data */
                $data = $requested->get($serviceType->getKey());

                if ($data['uses_sla'] && ($data['target_working_days'] ?? 0) < 1) {
                    throw ValidationException::withMessages([
                        "policies.{$serviceType->getKey()}.target_working_days" => "Target SLA {$serviceType->code} harus minimal 1 hari kerja.",
                    ]);
                }

                $current = SlaPolicy::query()
                    ->with('serviceType')
                    ->where('service_type_id', $serviceType->getKey())
                    ->active()
                    ->lockForUpdate()
                    ->first();
                $before = $this->slaSnapshot($current);
                $nextValues = [
                    'target_working_days' => $data['uses_sla'] ? $data['target_working_days'] : null,
                    'uses_sla' => $data['uses_sla'],
                ];

                if ($current !== null
                    && $current->target_working_days === $nextValues['target_working_days']
                    && $current->uses_sla === $nextValues['uses_sla']) {
                    continue;
                }

                $version = ((int) SlaPolicy::query()
                    ->where('service_type_id', $serviceType->getKey())
                    ->lockForUpdate()
                    ->max('version')) + 1;

                if ($current !== null) {
                    $current->forceFill(['is_active' => false])->save();
                }

                $policy = SlaPolicy::query()->create([
                    'service_type_id' => $serviceType->getKey(),
                    'target_working_days' => $nextValues['target_working_days'],
                    'uses_sla' => $nextValues['uses_sla'],
                    'version' => $version,
                    'effective_from' => now(),
                    'is_active' => true,
                    'changed_by' => $actor->getKey(),
                ])->load('serviceType');

                $this->auditLogger->succeeded(
                    $actor,
                    'admin.sla_policy.updated',
                    $policy,
                    "Kebijakan SLA {$serviceType->code} diperbarui; versi sebelumnya dipertahankan.",
                    $before,
                    $this->slaSnapshot($policy),
                );
            }
        });
    }

    /** @param array{timezone:string,working_days:list<int>,opens_at:string,closes_at:string,holidays:list<array{holiday_date:string,name:string}>} $data */
    public function updateCalendar(User $actor, array $data): ServiceCalendar
    {
        return $this->database->transaction(function () use ($actor, $data): ServiceCalendar {
            $current = ServiceCalendar::query()
                ->with('holidays')
                ->active()
                ->lockForUpdate()
                ->first();
            $before = $this->calendarSnapshot($current);
            $nextSnapshot = [
                'timezone' => $data['timezone'],
                'working_days' => array_values($data['working_days']),
                'opens_at' => substr($data['opens_at'], 0, 5),
                'closes_at' => substr($data['closes_at'], 0, 5),
                'holidays' => collect($data['holidays'])
                    ->sortBy('holiday_date')
                    ->values()
                    ->all(),
            ];

            if ($before !== null
                && $before['timezone'] === $nextSnapshot['timezone']
                && $before['working_days'] === $nextSnapshot['working_days']
                && $before['opens_at'] === $nextSnapshot['opens_at']
                && $before['closes_at'] === $nextSnapshot['closes_at']
                && $before['holidays'] === $nextSnapshot['holidays']) {
                return $current;
            }

            $version = ((int) ServiceCalendar::query()->lockForUpdate()->max('version')) + 1;

            if ($current !== null) {
                $current->forceFill(['is_active' => false])->save();
            }

            $calendar = ServiceCalendar::query()->create([
                'timezone' => $data['timezone'],
                'working_days' => $data['working_days'],
                'opens_at' => $data['opens_at'],
                'closes_at' => $data['closes_at'],
                'version' => $version,
                'effective_from' => now(),
                'is_active' => true,
                'changed_by' => $actor->getKey(),
            ]);

            foreach ($data['holidays'] as $holiday) {
                $calendar->holidays()->create($holiday);
            }

            $calendar->load('holidays');

            $this->auditLogger->succeeded(
                $actor,
                'admin.service_calendar.updated',
                $calendar,
                'Kalender jam layanan diperbarui; versi sebelumnya dipertahankan.',
                $before,
                $this->calendarSnapshot($calendar),
            );

            return $calendar;
        });
    }

    /** @param array<string, int> $settings */
    public function updateSettings(User $actor, array $settings): void
    {
        $unknownKeys = array_diff(array_keys($settings), array_keys(self::DEFAULT_SETTINGS));

        if ($unknownKeys !== []) {
            throw ValidationException::withMessages([
                'settings' => 'Ada pengaturan operasional yang tidak dikenali.',
            ]);
        }

        $this->database->transaction(function () use ($actor, $settings): void {
            foreach (self::DEFAULT_SETTINGS as $key => $default) {
                if (! array_key_exists($key, $settings)) {
                    throw ValidationException::withMessages([
                        $key => 'Pengaturan ini wajib diisi.',
                    ]);
                }

                $current = OperationalSetting::query()
                    ->forKey($key)
                    ->active()
                    ->lockForUpdate()
                    ->first();
                $before = $current === null ? null : $this->settingSnapshot($current);
                $currentValue = $current === null ? null : (int) $current->value;
                $nextValue = (int) $settings[$key];

                if ($current !== null && $currentValue === $nextValue) {
                    continue;
                }

                $version = ((int) OperationalSetting::query()
                    ->forKey($key)
                    ->lockForUpdate()
                    ->max('version')) + 1;

                if ($current !== null) {
                    $current->forceFill(['is_active' => false])->save();
                }

                $setting = OperationalSetting::query()->create([
                    'key' => $key,
                    'value' => $nextValue,
                    'value_type' => 'integer',
                    'version' => $version,
                    'effective_from' => now(),
                    'is_active' => true,
                    'changed_by' => $actor->getKey(),
                ]);

                $this->auditLogger->succeeded(
                    $actor,
                    'admin.operational_setting.updated',
                    $setting,
                    "Pengaturan operasional {$key} diperbarui; versi sebelumnya dipertahankan.",
                    $before,
                    $this->settingSnapshot($setting),
                );
            }
        });
    }

    /** @return array<string, int> */
    public function settings(): array
    {
        $values = OperationalSetting::query()
            ->active()
            ->get()
            ->mapWithKeys(fn (OperationalSetting $setting): array => [$setting->key => (int) $setting->value])
            ->all();

        return array_merge(self::DEFAULT_SETTINGS, $values);
    }

    public function currentCalendar(): ?ServiceCalendar
    {
        return ServiceCalendar::query()->with('holidays')->active()->first();
    }

    public function currentSlaPolicy(ServiceType $serviceType): ?SlaPolicy
    {
        return $serviceType->activeSlaPolicy()->first();
    }

    /** @return array<string, mixed>|null */
    private function slaSnapshot(?SlaPolicy $policy): ?array
    {
        if ($policy === null) {
            return null;
        }

        return [
            'id' => $policy->getKey(),
            'service_type' => $policy->serviceType?->code,
            'target_working_days' => $policy->target_working_days,
            'uses_sla' => $policy->uses_sla,
            'version' => $policy->version,
            'effective_from' => $policy->effective_from?->toIso8601String(),
            'is_active' => $policy->is_active,
        ];
    }

    /** @return array<string, mixed>|null */
    private function calendarSnapshot(?ServiceCalendar $calendar): ?array
    {
        if ($calendar === null) {
            return null;
        }

        $calendar->loadMissing('holidays');

        return [
            'id' => $calendar->getKey(),
            'timezone' => $calendar->timezone,
            'working_days' => array_values($calendar->working_days ?? []),
            'opens_at' => substr((string) $calendar->opens_at, 0, 5),
            'closes_at' => substr((string) $calendar->closes_at, 0, 5),
            'version' => $calendar->version,
            'effective_from' => $calendar->effective_from?->toIso8601String(),
            'is_active' => $calendar->is_active,
            'holidays' => $calendar->holidays->map(fn ($holiday): array => [
                'holiday_date' => $holiday->holiday_date?->format('Y-m-d'),
                'name' => $holiday->name,
            ])->values()->all(),
        ];
    }

    /** @return array<string, mixed> */
    private function settingSnapshot(OperationalSetting $setting): array
    {
        return [
            'key' => $setting->key,
            'value' => (int) $setting->value,
            'value_type' => $setting->value_type,
            'version' => $setting->version,
            'effective_from' => $setting->effective_from?->toIso8601String(),
            'is_active' => $setting->is_active,
        ];
    }
}
