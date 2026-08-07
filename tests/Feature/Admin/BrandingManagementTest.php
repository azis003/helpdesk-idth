<?php

namespace Tests\Feature\Admin;

use App\Enums\Role;
use App\Models\AuditLog;
use App\Models\OrganizationSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BrandingManagementTest extends TestCase
{
    public function test_super_admin_can_open_and_update_versioned_branding(): void
    {
        Storage::fake('local');
        $admin = $this->createUser([Role::SuperAdmin], ['username' => 'branding-admin']);

        $this->actingAs($admin)
            ->get(route('admin.branding.index'))
            ->assertOk()
            ->assertSee('Identitas aplikasi')
            ->assertSee('Nama instansi')
            ->assertSee('Riwayat identitas');

        $this->actingAs($admin)
            ->put(route('admin.branding.update'), [
                'organization_name' => 'Adira Finance',
                'application_name' => 'Service Desk',
                'tagline' => 'Portal Dukungan Internal',
                'footer_text' => 'Layanan TI untuk seluruh tim.',
                'logo' => UploadedFile::fake()->image('adira.png', 300, 100),
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $setting = OrganizationSetting::query()->active()->firstOrFail();

        $this->assertSame(1, $setting->version);
        $this->assertSame('Adira Finance', $setting->organization_name);
        $this->assertSame('Service Desk', $setting->application_name);
        Storage::disk('local')->assertExists($setting->logo_path);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'auditable_id' => $setting->id,
            'action' => 'admin.branding.updated',
            'outcome' => 'succeeded',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.branding.update'), [
                'organization_name' => 'Adira Finance Group',
                'application_name' => 'Service Desk Pro',
                'tagline' => 'Portal Dukungan Internal',
                'footer_text' => 'Layanan TI untuk seluruh tim.',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('organization_settings', [
            'version' => 1,
            'organization_name' => 'Adira Finance',
            'is_active' => 0,
        ]);
        $this->assertDatabaseHas('organization_settings', [
            'version' => 2,
            'organization_name' => 'Adira Finance Group',
            'application_name' => 'Service Desk Pro',
            'is_active' => 1,
        ]);

        $audit = AuditLog::query()
            ->where('action', 'admin.branding.updated')
            ->latest('id')
            ->firstOrFail();
        $this->assertSame('Adira Finance', $audit->before['organization_name']);
        $this->assertSame('Adira Finance Group', $audit->after['organization_name']);
        $this->assertSame($admin->id, $audit->user_id);
    }

    public function test_branding_fallback_and_rendering_are_applied_to_guest_and_authenticated_shells(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Masuk ke SIHATI')
            ->assertSee('Portal Layanan TI');

        $admin = $this->createUser([Role::SuperAdmin], ['username' => 'branding-renderer']);
        $this->actingAs($admin)->put(route('admin.branding.update'), [
            'organization_name' => 'Instansi Contoh',
            'application_name' => 'Ruang Bantuan',
            'tagline' => 'Satu pintu dukungan TI',
            'footer_text' => 'Kontak layanan TI internal.',
        ])->assertRedirect();

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Ruang Bantuan')
            ->assertSee('Instansi Contoh')
            ->assertSee('Satu pintu dukungan TI');

        auth()->logout();

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Ruang Bantuan')
            ->assertSee('Instansi Contoh')
            ->assertSee('Satu pintu dukungan TI');
    }

    public function test_logo_is_served_through_the_controlled_branding_endpoint(): void
    {
        Storage::fake('local');
        $admin = $this->createUser([Role::SuperAdmin], ['username' => 'branding-asset-owner']);

        $this->actingAs($admin)->put(route('admin.branding.update'), [
            'organization_name' => 'Instansi Logo',
            'application_name' => 'Portal Logo',
            'tagline' => 'Portal layanan',
            'footer_text' => 'Footer layanan',
            'logo' => UploadedFile::fake()->image('logo.png', 240, 80),
        ])->assertRedirect();

        $setting = OrganizationSetting::query()->active()->firstOrFail();

        $this->get(route('branding.logo', ['v' => $setting->version]))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_non_super_admin_cannot_view_or_update_branding(): void
    {
        $user = $this->createUser([Role::Pemohon], ['username' => 'branding-viewer']);

        $this->actingAs($user)
            ->get(route('admin.branding.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->put(route('admin.branding.update'), [
                'organization_name' => 'Tidak boleh',
                'application_name' => 'Tidak boleh',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('organization_settings', [
            'organization_name' => 'Tidak boleh',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'auth.role_required',
            'outcome' => 'denied',
        ]);
    }

    public function test_branding_values_are_validated_in_indonesian(): void
    {
        $admin = $this->createUser([Role::SuperAdmin], ['username' => 'branding-validator']);

        $this->actingAs($admin)
            ->put(route('admin.branding.update'), [
                'organization_name' => '',
                'application_name' => str_repeat('x', 101),
                'logo' => UploadedFile::fake()->create('logo.pdf', 10, 'application/pdf'),
            ])
            ->assertRedirect()
            ->assertSessionHasErrors(['organization_name', 'application_name', 'logo']);

        $this->assertEquals('Nama instansi wajib diisi.', session('errors')->get('organization_name')[0]);
    }
}
