<?php

namespace Tests\Feature\Admin;

use App\Enums\Role;
use App\Models\Announcement;
use App\Models\AttachmentPolicy;
use App\Models\AuditLog;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Room;
use App\Models\ServiceFieldDefinition;
use App\Models\ServiceType;
use App\Models\Skill;
use Carbon\CarbonImmutable;
use Database\Seeders\ServiceCatalogSeeder;
use Tests\TestCase;

class CatalogManagementTest extends TestCase
{
    public function test_super_admin_can_open_catalog_and_non_admin_is_denied(): void
    {
        $this->seed(ServiceCatalogSeeder::class);

        $admin = $this->createUser([Role::SuperAdmin]);
        $pemohon = $this->createUser([Role::Pemohon]);

        $this->actingAs($admin)
            ->get(route('admin.catalog.index'))
            ->assertOk()
            ->assertSee('SVC-01')
            ->assertSee('SVC-07')
            ->assertSee('Field formulir aktif');

        $this->actingAs($pemohon)
            ->get(route('admin.catalog.index'))
            ->assertForbidden();

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $pemohon->id,
            'action' => 'auth.role_required',
            'outcome' => 'denied',
        ]);
    }

    public function test_catalog_contexts_are_separated_for_admin_tasks(): void
    {
        $this->seed(ServiceCatalogSeeder::class);

        $admin = $this->createUser([Role::SuperAdmin]);

        $this->actingAs($admin)
            ->get(route('admin.catalog.index', ['section' => 'services']))
            ->assertOk()
            ->assertSee('Daftar layanan')
            ->assertSee('Kode Layanan')
            ->assertSee('Preview formulir')
            ->assertDontSee('Manajemen Formulir')
            ->assertDontSee('Gedung, lantai, dan ruangan');

        $this->actingAs($admin)
            ->get(route('admin.catalog.index', ['section' => 'locations']))
            ->assertOk()
            ->assertSee('Lokasi')
            ->assertSee('Tambah gedung')
            ->assertDontSee('Daftar layanan');

        $this->actingAs($admin)
            ->get(route('admin.catalog.index', ['section' => 'attachments']))
            ->assertOk()
            ->assertSee('Kebijakan lampiran')
            ->assertDontSee('Daftar layanan');

        $service = ServiceType::query()->where('code', 'SVC-04')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.services.index'))
            ->assertOk()
            ->assertSee('Manajemen Layanan')
            ->assertSee('Buat layanan')
            ->assertSee('Kode Layanan')
            ->assertSee('Preview formulir');

        $this->actingAs($admin)
            ->get(route('admin.forms.index', ['service' => $service->id]))
            ->assertRedirect(route('admin.services.index', ['service' => $service->id]));
    }

    public function test_super_admin_can_create_service_with_skills_and_template(): void
    {
        $this->seed(ServiceCatalogSeeder::class);

        $admin = $this->createUser([Role::SuperAdmin]);
        $skill = Skill::factory()->create(['name' => 'Akses Aplikasi', 'slug' => 'akses-aplikasi']);

        $response = $this->actingAs($admin)->post(route('admin.services.store'), [
            'code' => 'SVC-08',
            'name' => 'Permintaan akses aplikasi',
            'ticket_class' => 'REQ',
            'skill_ids' => [$skill->id],
            'description' => 'Membutuhkan keahlian pengelolaan akses aplikasi.',
            'fields' => [
                [
                    'key' => 'application_name',
                    'label' => 'Nama aplikasi',
                    'field_type' => 'text',
                    'visibility' => 'requester',
                    'is_required' => '1',
                    'sort_order' => 1,
                ],
                [
                    'key' => 'access_type',
                    'label' => 'Jenis akses',
                    'field_type' => 'select',
                    'visibility' => 'both',
                    'options_text' => "read|Baca\nwrite|Tulis",
                    'sort_order' => 2,
                ],
            ],
        ]);

        $service = ServiceType::query()->where('code', 'SVC-08')->firstOrFail();

        $response
            ->assertRedirect(route('admin.services.index', ['service' => $service->id]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('service_types', [
            'id' => $service->id,
            'code' => 'SVC-08',
            'name' => 'Permintaan akses aplikasi',
            'ticket_class' => 'REQ',
        ]);
        $this->assertDatabaseHas('service_type_skill', [
            'service_type_id' => $service->id,
            'skill_id' => $skill->id,
        ]);

        $field = ServiceFieldDefinition::query()
            ->where('service_type_id', $service->id)
            ->where('key', 'access_type')
            ->firstOrFail();

        $this->assertTrue($field->is_active);
        $this->assertSame(2, $field->options()->count());
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'admin.service_type.created',
            'outcome' => 'succeeded',
        ]);
    }

    public function test_super_admin_can_update_service_and_version_dynamic_fields(): void
    {
        $this->seed(ServiceCatalogSeeder::class);

        $admin = $this->createUser([Role::SuperAdmin]);
        $service = ServiceType::query()->where('code', 'SVC-04')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.catalog.services.update', $service), [
                'name' => 'Perubahan pada aplikasi bisnis',
                'description' => 'Perubahan yang dikelola melalui katalog.',
                'ticket_class' => 'CHG',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('service_types', [
            'id' => $service->id,
            'name' => 'Perubahan pada aplikasi bisnis',
            'ticket_class' => 'CHG',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'admin.service_type.updated',
            'outcome' => 'succeeded',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.catalog.fields.store', $service), [
                'key' => 'deployment_environment',
                'label' => 'Lingkungan penerapan',
                'field_type' => 'select',
                'visibility' => 'requester',
                'sort_order' => 20,
                'is_required' => '1',
                'options_text' => "testing|Pengujian\nproduction|Produksi",
                'validation_rules_text' => "string\nmax:50",
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $field = ServiceFieldDefinition::query()
            ->where('service_type_id', $service->id)
            ->where('key', 'deployment_environment')
            ->firstOrFail();

        $this->assertTrue($field->is_active);
        $this->assertSame(2, $field->options()->count());

        $this->actingAs($admin)
            ->post(route('admin.catalog.fields.versions.store', $field), [
                'label' => 'Lingkungan penerapan aplikasi',
                'field_type' => 'select',
                'visibility' => 'both',
                'sort_order' => 21,
                'is_required' => '1',
                'options_text' => "testing|Pengujian\nstaging|Staging\nproduction|Produksi",
                'validation_rules_text' => "string\nmax:80",
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $field->refresh();
        $newVersion = ServiceFieldDefinition::query()
            ->where('service_type_id', $service->id)
            ->where('key', 'deployment_environment')
            ->where('version', 2)
            ->firstOrFail();

        $this->assertFalse($field->is_active);
        $this->assertTrue($newVersion->is_active);
        $this->assertSame($field->id, $newVersion->supersedes_id);
        $this->assertSame(3, $newVersion->options()->count());

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'admin.service_field.versioned',
            'outcome' => 'succeeded',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.catalog.fields.destroy', $newVersion))
            ->assertRedirect()
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', "Field Lingkungan penerapan aplikasi dihapus dari formulir. Histori tiket tetap aman.");

        $this->assertDatabaseHas('service_field_definitions', [
            'id' => $newVersion->id,
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'admin.service_field.deactivated',
            'outcome' => 'succeeded',
        ]);
    }

    public function test_hardware_subtypes_keep_inc_req_mapping(): void
    {
        $this->seed(ServiceCatalogSeeder::class);

        $admin = $this->createUser([Role::SuperAdmin]);
        $service = ServiceType::query()->where('code', 'SVC-05')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.catalog.services.update', $service), [
                'name' => 'Permintaan atau perbaikan perangkat',
                'ticket_class' => null,
                'variants' => [
                    ['code' => 'repair', 'label' => 'Perbaikan perangkat', 'sort_order' => 1],
                    ['code' => 'request', 'label' => 'Permintaan perangkat', 'sort_order' => 2],
                ],
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('service_type_variants', [
            'service_type_id' => $service->id,
            'code' => 'repair',
            'ticket_class' => 'INC',
            'label' => 'Perbaikan perangkat',
        ]);
        $this->assertDatabaseHas('service_type_variants', [
            'service_type_id' => $service->id,
            'code' => 'request',
            'ticket_class' => 'REQ',
            'label' => 'Permintaan perangkat',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.catalog.services.update', $service), [
                'name' => 'Permintaan atau perbaikan perangkat',
                'ticket_class' => null,
                'variants' => [
                    ['code' => 'repair', 'label' => 'Perbaikan perangkat', 'sort_order' => 1],
                ],
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('variants');
    }

    public function test_super_admin_can_manage_location_hierarchy_and_attachment_policy(): void
    {
        $this->seed(ServiceCatalogSeeder::class);

        $admin = $this->createUser([Role::SuperAdmin]);

        $this->actingAs($admin)
            ->post(route('admin.catalog.buildings.store'), ['name' => 'Gedung Pengujian'])
            ->assertRedirect()
            ->assertSessionHasNoErrors();
        $building = Building::query()->where('name', 'Gedung Pengujian')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.catalog.floors.store', $building), ['name' => 'Lantai 1', 'sort_order' => 1])
            ->assertRedirect()
            ->assertSessionHasNoErrors();
        $floor = Floor::query()->where('building_id', $building->id)->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.catalog.rooms.store', $floor), ['name' => 'Ruang Pengujian'])
            ->assertRedirect()
            ->assertSessionHasNoErrors();
        $room = Room::query()->where('floor_id', $floor->id)->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.catalog.buildings.status', [$building, 'deactivate']))
            ->assertRedirect()
            ->assertSessionHasErrors('building');
        $this->assertTrue($building->fresh()->is_active);

        $this->actingAs($admin)
            ->post(route('admin.catalog.rooms.status', [$room, 'deactivate']))
            ->assertRedirect()
            ->assertSessionHasNoErrors();
        $this->actingAs($admin)
            ->post(route('admin.catalog.floors.status', [$floor, 'deactivate']))
            ->assertRedirect()
            ->assertSessionHasNoErrors();
        $this->actingAs($admin)
            ->post(route('admin.catalog.buildings.status', [$building, 'deactivate']))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertFalse($building->fresh()->is_active);

        $this->actingAs($admin)
            ->post(route('admin.catalog.attachment-policies.store'), [
                'type_key' => 'supporting',
                'label' => 'Dokumen pendukung',
                'max_file_size_kb' => 10240,
                'max_file_count' => 5,
                'allowed_mimes_text' => 'application/pdf, image/png',
                'allowed_extensions_text' => 'pdf, png',
                'visibility' => 'both',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $policy = AttachmentPolicy::query()->where('type_key', 'supporting')->firstOrFail();
        $this->assertTrue($policy->is_active);
        $this->assertSame(['application/pdf', 'image/png'], $policy->allowed_mimes);
        $this->assertSame(['pdf', 'png'], $policy->allowed_extensions);
    }

    public function test_super_admin_and_tier_one_can_manage_announcements(): void
    {
        $admin = $this->createUser([Role::SuperAdmin]);
        $tierOne = $this->createUser([Role::AgenTier1]);
        $pemohon = $this->createUser([Role::Pemohon]);
        $start = CarbonImmutable::now(config('app.timezone'))->subMinutes(2);
        $end = $start->addHour();

        $payload = [
            'title' => 'Pemeliharaan layanan',
            'body' => 'Layanan akan dipelihara secara berkala.',
            'starts_at' => $start->format('Y-m-d\TH:i'),
            'ends_at' => $end->format('Y-m-d\TH:i'),
            'is_active' => '1',
        ];

        $this->actingAs($tierOne)
            ->get(route('admin.announcements.index'))
            ->assertOk()
            ->assertSee('Pengumuman layanan TI');

        $this->actingAs($tierOne)
            ->post(route('admin.announcements.store'), $payload)
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $announcement = Announcement::query()->where('title', $payload['title'])->firstOrFail();
        $this->assertSame($tierOne->id, $announcement->created_by);
        $this->assertTrue(Announcement::query()->activeAt(now())->whereKey($announcement)->exists());

        $this->actingAs($pemohon)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Pemeliharaan layanan')
            ->assertSee('Layanan akan dipelihara secara berkala.');

        $this->actingAs($admin)
            ->post(route('admin.announcements.store'), [
                ...$payload,
                'title' => 'Pengumuman dari Super Admin',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->actingAs($admin)
            ->post(route('admin.announcements.status', [$announcement, 'deactivate']))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertFalse($announcement->fresh()->is_active);
        $this->assertFalse(Announcement::query()->activeAt(now())->whereKey($announcement)->exists());

        $this->actingAs($pemohon)
            ->get(route('admin.announcements.index'))
            ->assertForbidden();

        $this->assertTrue(AuditLog::query()
            ->where('user_id', $pemohon->id)
            ->where('action', 'auth.role_required')
            ->where('outcome', 'denied')
            ->exists());
    }
}
