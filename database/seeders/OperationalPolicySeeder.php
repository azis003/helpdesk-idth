<?php

namespace Database\Seeders;

use App\Models\OperationalSetting;
use App\Models\ServiceCalendar;
use App\Models\ServiceType;
use App\Models\SlaPolicy;
use App\Services\OperationalPolicyService;
use Illuminate\Database\Seeder;

class OperationalPolicySeeder extends Seeder
{
    public function run(): void
    {
        $targets = [
            'SVC-01' => 1,
            'SVC-02' => 3,
            'SVC-03' => 5,
            'SVC-04' => 7,
            'SVC-05' => 3,
            'SVC-06' => 2,
            'SVC-07' => null,
        ];

        foreach ($targets as $code => $targetWorkingDays) {
            $serviceType = ServiceType::query()->where('code', $code)->first();

            if ($serviceType === null || $serviceType->slaPolicies()->active()->exists()) {
                continue;
            }

            SlaPolicy::query()->create([
                'service_type_id' => $serviceType->getKey(),
                'target_working_days' => $targetWorkingDays,
                'uses_sla' => $targetWorkingDays !== null,
                'version' => ((int) $serviceType->slaPolicies()->max('version')) + 1,
                'effective_from' => now(),
                'is_active' => true,
            ]);
        }

        if (! ServiceCalendar::query()->active()->exists()) {
            ServiceCalendar::query()->create([
                'timezone' => OperationalPolicyService::TIMEZONE,
                'working_days' => OperationalPolicyService::DEFAULT_WORKING_DAYS,
                'opens_at' => '08:00',
                'closes_at' => '16:00',
                'version' => ((int) ServiceCalendar::query()->max('version')) + 1,
                'effective_from' => now(),
                'is_active' => true,
            ]);
        }

        foreach (OperationalPolicyService::DEFAULT_SETTINGS as $key => $value) {
            if (OperationalSetting::query()->forKey($key)->active()->exists()) {
                continue;
            }

            OperationalSetting::query()->create([
                'key' => $key,
                'value' => $value,
                'value_type' => 'integer',
                'version' => ((int) OperationalSetting::query()->forKey($key)->max('version')) + 1,
                'effective_from' => now(),
                'is_active' => true,
            ]);
        }
    }
}
