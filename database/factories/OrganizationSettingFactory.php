<?php

namespace Database\Factories;

use App\Models\OrganizationSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<OrganizationSetting> */
class OrganizationSettingFactory extends Factory
{
    protected $model = OrganizationSetting::class;

    public function definition(): array
    {
        return [
            'organization_name' => 'SIHATI',
            'application_name' => 'SIHATI',
            'tagline' => 'Portal Layanan TI',
            'footer_text' => 'Portal Layanan TI internal',
            'logo_path' => null,
            'logo_disk' => 'local',
            'version' => 1,
            'effective_from' => now(),
            'is_active' => true,
            'changed_by' => null,
        ];
    }
}
