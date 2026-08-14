<?php

namespace Tests\Feature\Admin;

use App\Enums\Role;
use App\Enums\TeamPosition;
use App\Models\Role as RoleModel;
use App\Models\ServiceType;
use App\Models\Skill;
use App\Models\TeamMembership;
use App\Models\User;
use App\Models\WorkTeam;
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class OrganizationManagementTest extends TestCase
{
    public function test_super_admin_can_create_user_with_roles_team_and_skills(): void
    {
        $admin = $this->createUser([Role::SuperAdmin], ['username' => 'super-admin']);
        $team = WorkTeam::factory()->create(['name' => 'Tim Layanan']);
        $skill = Skill::factory()->create(['name' => 'Jaringan Kantor', 'slug' => 'jaringan-kantor']);
        $role = RoleModel::query()->where('slug', Role::AgenTier2->value)->firstOrFail();

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Teknisi Baru',
            'username' => 'teknisi-baru',
            'email' => 'teknisi@example.test',
            'nip' => '1234567890',
            'temporary_password' => 'Initial-Password-123!',
            'temporary_password_confirmation' => 'Initial-Password-123!',
            'role_ids' => [$role->id],
            'team_id' => $team->id,
            'team_position' => TeamPosition::Member->value,
            'skill_ids' => [$skill->id],
        ]);

        $response->assertRedirectToRoute('admin.users.index');
        $user = User::query()->where('username', 'teknisi-baru')->firstOrFail();

        $this->assertTrue(Hash::check('Initial-Password-123!', $user->password));
        $this->assertFalse($user->must_change_password);
        $this->assertTrue($user->hasRole(Role::AgenTier2));
        $this->assertTrue($user->skills()->whereKey($skill->id)->exists());
        $this->assertDatabaseHas('team_memberships', [
            'user_id' => $user->id,
            'work_team_id' => $team->id,
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'auditable_id' => $user->id,
            'action' => 'admin.user.created',
            'outcome' => 'succeeded',
        ]);
    }

    public function test_new_user_rejects_duplicate_username_and_nip(): void
    {
        $admin = $this->createUser([Role::SuperAdmin], ['username' => 'unique-admin']);
        $existing = $this->createUser([Role::Pemohon], [
            'username' => 'username-terpakai',
            'nip' => '1987654321',
        ]);
        $team = WorkTeam::factory()->create(['name' => 'Tim Unik']);
        $role = RoleModel::query()->where('slug', Role::Pemohon->value)->firstOrFail();

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Pengguna Duplikat',
            'username' => $existing->username,
            'email' => 'duplikat@example.test',
            'nip' => $existing->nip,
            'temporary_password' => 'Initial-Password-123!',
            'temporary_password_confirmation' => 'Initial-Password-123!',
            'role_ids' => [$role->id],
            'team_id' => $team->id,
            'team_position' => TeamPosition::Member->value,
            'skill_ids' => [],
        ]);

        $response->assertSessionHasErrors(['username', 'nip']);
        $this->assertDatabaseMissing('users', ['email' => 'duplikat@example.test']);
    }

    public function test_technician_requires_at_least_one_skill_when_created(): void
    {
        $admin = $this->createUser([Role::SuperAdmin], ['username' => 'skill-required-admin']);
        $team = WorkTeam::factory()->create(['name' => 'Tim Teknisi']);
        $technicianRole = RoleModel::query()->where('slug', Role::AgenTier2->value)->firstOrFail();

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Teknisi Tanpa Keahlian',
            'username' => 'teknisi-tanpa-keahlian',
            'email' => 'tanpa-keahlian@example.test',
            'nip' => '1234567891',
            'temporary_password' => 'Initial-Password-123!',
            'temporary_password_confirmation' => 'Initial-Password-123!',
            'role_ids' => [$technicianRole->id],
            'team_id' => $team->id,
            'team_position' => TeamPosition::Member->value,
        ]);

        $response->assertSessionHasErrors('skill_ids');
        $this->assertDatabaseMissing('users', ['username' => 'teknisi-tanpa-keahlian']);
    }

    public function test_user_requires_a_team_when_created(): void
    {
        $admin = $this->createUser([Role::SuperAdmin], ['username' => 'team-required-admin']);
        $role = RoleModel::query()->where('slug', Role::Pemohon->value)->firstOrFail();

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Pengguna Tanpa Tim',
            'username' => 'pengguna-tanpa-tim',
            'email' => 'tanpa-tim@example.test',
            'nip' => '1234567892',
            'temporary_password' => 'Initial-Password-123!',
            'temporary_password_confirmation' => 'Initial-Password-123!',
            'role_ids' => [$role->id],
            'team_position' => TeamPosition::Member->value,
        ]);

        $response->assertSessionHasErrors('team_id');
        $this->assertDatabaseMissing('users', ['username' => 'pengguna-tanpa-tim']);
    }

    public function test_user_requires_a_team_when_updated(): void
    {
        $admin = $this->createUser([Role::SuperAdmin], ['username' => 'team-update-admin']);
        $target = $this->createUser([Role::Pemohon], ['username' => 'team-update-target']);
        $team = WorkTeam::factory()->create(['name' => 'Tim Awal']);
        $target->teamMemberships()->create([
            'work_team_id' => $team->id,
            'assigned_by' => $admin->id,
            'started_at' => now(),
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.users.update', $target), [
            'name' => $target->name,
            'username' => $target->username,
            'email' => $target->email,
            'nip' => $target->nip,
            'role_ids' => $target->roles->pluck('id')->all(),
            'team_position' => TeamPosition::Member->value,
            'skill_ids' => [],
        ]);

        $response->assertSessionHasErrors('team_id');
        $this->assertDatabaseHas('team_memberships', [
            'user_id' => $target->id,
            'work_team_id' => $team->id,
            'is_active' => true,
        ]);
    }

    public function test_edit_modal_shows_every_role_currently_assigned_to_the_user(): void
    {
        $admin = $this->createUser([Role::SuperAdmin], ['username' => 'multi-role-admin']);
        $target = $this->createUser([Role::Pemohon, Role::AgenTier1], ['username' => 'multi-role-user']);
        $assignedRoleIds = $target->roles->pluck('id');

        $response = $this->actingAs($admin)->get(route('admin.users.index'));
        $response->assertOk()->assertDontSee('data-user-secondary-role', false);

        foreach ($assignedRoleIds as $roleId) {
            $this->assertMatchesRegularExpression(
                '/id="user-edit-'.$target->id.'-role-'.$roleId.'"[^>]*checked/s',
                $response->getContent(),
            );
        }
    }

    public function test_user_edit_modal_is_rendered_outside_the_animated_main_content(): void
    {
        $admin = $this->createUser([Role::SuperAdmin], ['username' => 'modal-admin']);
        $target = $this->createUser([Role::Pemohon], ['username' => 'modal-user']);

        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $response->assertOk()->assertSeeInOrder([
            '</main>',
            'id="user-edit-modal-'.$target->id.'"',
        ], false);
    }

    public function test_user_form_renders_skills_as_selectable_checkboxes(): void
    {
        $admin = $this->createUser([Role::SuperAdmin], ['username' => 'skill-checkbox-admin']);
        $target = $this->createUser([Role::AgenTier2], ['username' => 'skill-checkbox-user']);
        $skill = Skill::factory()->create(['name' => 'Jaringan', 'slug' => 'jaringan']);
        $target->skills()->attach($skill->id, [
            'assigned_by' => $admin->id,
            'assigned_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $response->assertOk()
            ->assertDontSee('data-user-skills-input', false)
            ->assertSee('data-user-skill', false)
            ->assertSee('name="skill_ids[]"', false);
        $this->assertMatchesRegularExpression(
            '/id="user-edit-'.$target->id.'-skill-'.$skill->id.'"[^>]*checked/s',
            $response->getContent(),
        );
    }

    public function test_team_position_in_user_management_controls_chair_assignment_and_internal_role(): void
    {
        $admin = $this->createUser([Role::SuperAdmin], ['username' => 'position-admin']);
        $target = $this->createUser([Role::Pemohon], ['username' => 'position-target']);
        $team = WorkTeam::factory()->create(['name' => 'Tim Posisi']);
        $pemohonRole = RoleModel::query()->where('slug', Role::Pemohon->value)->firstOrFail();

        $target->teamMemberships()->create([
            'work_team_id' => $team->id,
            'assigned_by' => $admin->id,
            'started_at' => now(),
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.users.update', $target), [
            'name' => $target->name,
            'username' => $target->username,
            'email' => $target->email,
            'nip' => $target->nip,
            'role_ids' => [$pemohonRole->id],
            'team_id' => $team->id,
            'team_position' => TeamPosition::Chair->value,
            'skill_ids' => [],
            'is_active' => true,
        ]);
        $response->assertRedirect();

        $target->refresh();
        $this->assertTrue($target->hasRole(Role::KetuaTimKerja));
        $this->assertDatabaseHas('team_chair_assignments', [
            'work_team_id' => $team->id,
            'user_id' => $target->id,
            'is_active' => true,
        ]);

        $this->actingAs($admin)->put(route('admin.users.update', $target), [
            'name' => $target->name,
            'username' => $target->username,
            'email' => $target->email,
            'nip' => $target->nip,
            'role_ids' => [$pemohonRole->id],
            'team_id' => $team->id,
            'team_position' => TeamPosition::Member->value,
            'skill_ids' => [],
            'is_active' => true,
        ])->assertRedirect();

        $target->refresh();
        $this->assertFalse($target->hasRole(Role::KetuaTimKerja));
        $this->assertDatabaseHas('team_chair_assignments', [
            'work_team_id' => $team->id,
            'user_id' => $target->id,
            'is_active' => false,
        ]);
    }

    public function test_super_admin_can_revoke_and_grant_roles_with_history(): void
    {
        $admin = $this->createUser([Role::SuperAdmin], ['username' => 'role-admin']);
        $target = $this->createUser([Role::Pemohon], ['username' => 'role-target']);
        $agentRole = RoleModel::query()->where('slug', Role::AgenTier1->value)->firstOrFail();

        $response = $this->actingAs($admin)->put(route('admin.users.roles.update', $target), [
            'role_ids' => [$agentRole->id],
        ]);

        $response->assertRedirect();
        $target->refresh();
        $this->assertFalse($target->hasRole(Role::Pemohon));
        $this->assertTrue($target->hasRole(Role::AgenTier1));
        $this->assertDatabaseHas('role_assignment_histories', [
            'user_id' => $target->id,
            'role_id' => RoleModel::query()->where('slug', Role::Pemohon->value)->value('id'),
            'acted_by' => $admin->id,
            'action' => 'revoked',
        ]);
        $this->assertDatabaseHas('role_assignment_histories', [
            'user_id' => $target->id,
            'role_id' => $agentRole->id,
            'acted_by' => $admin->id,
            'action' => 'assigned',
        ]);
    }

    public function test_super_admin_can_toggle_user_status_but_cannot_disable_self(): void
    {
        $admin = $this->createUser([Role::SuperAdmin], ['username' => 'status-admin']);
        $target = $this->createUser([Role::Pemohon], ['username' => 'status-target']);

        $this->actingAs($admin)->post(route('admin.users.deactivate', $target))
            ->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $target->id, 'is_active' => false]);

        $this->actingAs($admin)->post(route('admin.users.activate', $target))
            ->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $target->id, 'is_active' => true]);

        $this->actingAs($admin)->post(route('admin.users.deactivate', $admin))
            ->assertForbidden();
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'auditable_id' => $admin->id,
            'action' => 'admin.user.deactivate',
            'outcome' => 'denied',
        ]);
    }

    public function test_user_edit_can_change_status(): void
    {
        $admin = $this->createUser([Role::SuperAdmin], ['username' => 'status-edit-admin']);
        $target = $this->createUser([Role::Pemohon], ['username' => 'status-edit-target']);
        $team = WorkTeam::factory()->create(['name' => 'Tim Status']);

        $target->teamMemberships()->create([
            'work_team_id' => $team->id,
            'assigned_by' => $admin->id,
            'started_at' => now(),
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.users.update', $target), [
            'name' => $target->name,
            'username' => $target->username,
            'email' => $target->email,
            'nip' => $target->nip,
            'role_ids' => $target->roles->pluck('id')->all(),
            'team_id' => $team->id,
            'team_position' => TeamPosition::Member->value,
            'skill_ids' => [],
            'is_active' => '0',
        ]);
        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'is_active' => false,
        ]);
    }

    public function test_super_admin_can_soft_delete_user_but_not_self(): void
    {
        $admin = $this->createUser([Role::SuperAdmin], ['username' => 'delete-admin']);
        $target = $this->createUser([Role::Pemohon], ['username' => 'delete-target']);

        $this->actingAs($admin)->delete(route('admin.users.destroy', $target))
            ->assertRedirect();

        $this->assertSoftDeleted('users', ['id' => $target->id]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'auditable_id' => $target->id,
            'action' => 'admin.user.deleted',
            'outcome' => 'succeeded',
        ]);
        $this->assertFalse(User::query()->whereKey($target->id)->exists());

        $this->actingAs($admin)->delete(route('admin.users.destroy', $admin))
            ->assertForbidden();
        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'deleted_at' => null,
        ]);
    }

    public function test_soft_deleted_user_identifiers_can_be_reused_but_active_duplicates_are_rejected(): void
    {
        $admin = $this->createUser([Role::SuperAdmin], ['username' => 'reuse-admin']);
        $target = $this->createUser([Role::Pemohon], [
            'username' => 'reuse-target',
            'email' => 'reuse-target@example.test',
            'nip' => '9876543210',
        ]);
        $role = RoleModel::query()->where('slug', Role::Pemohon->value)->firstOrFail();
        $team = WorkTeam::factory()->create(['name' => 'Tim Reuse']);

        $this->actingAs($admin)->delete(route('admin.users.destroy', $target))
            ->assertRedirect();

        $payload = [
            'name' => 'Pengguna Pengganti',
            'username' => 'reuse-target',
            'email' => 'reuse-target@example.test',
            'nip' => '9876543210',
            'temporary_password' => 'Initial-Password-123!',
            'temporary_password_confirmation' => 'Initial-Password-123!',
            'role_ids' => [$role->id],
            'team_id' => $team->id,
            'team_position' => TeamPosition::Member->value,
        ];

        $this->actingAs($admin)->post(route('admin.users.store'), $payload)
            ->assertRedirectToRoute('admin.users.index');

        $this->assertDatabaseHas('users', [
            'name' => 'Pengguna Pengganti',
            'username' => 'reuse-target',
            'email' => 'reuse-target@example.test',
            'nip' => '9876543210',
            'deleted_at' => null,
        ]);
        $this->assertSame(2, User::withTrashed()->where('username', 'reuse-target')->count());

        $this->actingAs($admin)->post(route('admin.users.store'), $payload)
            ->assertSessionHasErrors(['username', 'email', 'nip']);
    }

    public function test_user_transfer_keeps_one_active_team_and_records_history(): void
    {
        $admin = $this->createUser([Role::SuperAdmin], ['username' => 'team-admin']);
        $target = $this->createUser([Role::AgenTier1], ['username' => 'team-target']);
        $firstTeam = WorkTeam::factory()->create(['name' => 'Tim Pertama']);
        $secondTeam = WorkTeam::factory()->create(['name' => 'Tim Kedua']);

        $this->actingAs($admin)->put(route('admin.users.team.update', $target), ['team_id' => $firstTeam->id])
            ->assertRedirect();
        $this->actingAs($admin)->put(route('admin.users.team.update', $target), ['team_id' => $secondTeam->id])
            ->assertRedirect();

        $this->assertSame(2, TeamMembership::query()->where('user_id', $target->id)->count());
        $this->assertSame(1, TeamMembership::query()->where('user_id', $target->id)->where('is_active', true)->count());
        $this->assertDatabaseHas('team_memberships', [
            'user_id' => $target->id,
            'work_team_id' => $firstTeam->id,
            'is_active' => false,
        ]);
        $this->assertDatabaseHas('team_memberships', [
            'user_id' => $target->id,
            'work_team_id' => $secondTeam->id,
            'is_active' => true,
        ]);
    }

    public function test_team_management_only_displays_read_only_membership_data(): void
    {
        $admin = $this->createUser([Role::SuperAdmin], ['username' => 'team-read-only-admin']);
        $team = WorkTeam::factory()->create(['name' => 'Tim Read Only']);

        $response = $this->actingAs($admin)->get(route('admin.teams.index'));

        $response->assertOk()
            ->assertSee('Nama Tim Kerja')
            ->assertSee('Ketua Tim Kerja')
            ->assertSee('Anggota')
            ->assertSee('Deskripsi')
            ->assertSee('Aksi')
            ->assertSee('Edit Tim Kerja')
            ->assertDontSee('Tambah anggota')
            ->assertDontSee('Pilih ketua')
            ->assertDontSee('Simpan ketua');
    }

    public function test_service_skill_mapping_and_soft_delete_preserve_history(): void
    {
        $this->seed(ServiceCatalogSeeder::class);
        $admin = $this->createUser([Role::SuperAdmin], ['username' => 'master-admin']);
        $skill = Skill::factory()->create(['name' => 'Basis Data', 'slug' => 'basis-data']);
        $service = ServiceType::query()->where('code', 'SVC-01')->firstOrFail();

        $this->actingAs($admin)->put(route('admin.catalog.services.skills.update', $service), [
            'skill_ids' => [$skill->id],
        ])->assertRedirect();

        $this->assertDatabaseHas('service_type_skill', [
            'service_type_id' => $service->id,
            'skill_id' => $skill->id,
            'assigned_by' => $admin->id,
        ]);

        $this->actingAs($admin)->delete(route('admin.skills.destroy', $skill))
            ->assertRedirect();
        $this->assertSoftDeleted('skills', ['id' => $skill->id]);
        $this->assertDatabaseHas('service_type_skill', [
            'service_type_id' => $service->id,
            'skill_id' => $skill->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'auditable_id' => $skill->id,
            'action' => 'admin.skill.deleted',
            'outcome' => 'succeeded',
        ]);
    }

    public function test_non_admin_cannot_open_organization_pages(): void
    {
        $user = $this->createUser([Role::Pemohon], ['username' => 'organization-viewer']);

        $this->actingAs($user)->get(route('admin.teams.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.skills.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.catalog.index'))->assertForbidden();
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'auth.role_required',
            'outcome' => 'denied',
        ]);
    }

    public function test_super_admin_can_render_organization_pages(): void
    {
        $this->seed(ServiceCatalogSeeder::class);
        $admin = $this->createUser([Role::SuperAdmin], ['username' => 'page-admin']);
        $this->createUser([Role::Pemohon], ['username' => 'page-listed-user']);
        $team = WorkTeam::factory()->create(['name' => 'Tim Halaman']);
        $skill = Skill::factory()->create(['name' => 'Dukungan Aplikasi', 'slug' => 'dukungan-aplikasi']);
        $team->currentMembers()->attach($admin->id, [
            'assigned_by' => $admin->id,
            'started_at' => now(),
            'is_active' => true,
        ]);
        $this->actingAs($admin)->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Pengguna dan akses')
            ->assertSee('Nama Pengguna')
            ->assertSee('Detail Pengguna')
            ->assertSee('Username')
            ->assertSee('NIP')
            ->assertSee('Tim Kerja')
            ->assertSee('Lihat pengguna')
            ->assertSee('Hapus pengguna')
            ->assertSee('Status')
            ->assertSee('data-clear-on-close="true"', false);
        $this->actingAs($admin)->get(route('admin.users.edit', $admin))->assertOk()->assertSee('Riwayat organisasi');
        $this->actingAs($admin)->get(route('admin.teams.index'))->assertOk()->assertSee('Tim Halaman');
        $this->actingAs($admin)->get(route('admin.skills.index'))->assertOk()->assertSee('Dukungan Aplikasi')->assertDontSee('Kategori Masalah');
        $serviceCatalogUrl = route('admin.catalog.index', ['section' => 'services']);
        $serviceCatalogResponse = $this->actingAs($admin)->get($serviceCatalogUrl);
        $serviceCatalogResponse->assertOk();
        $this->assertResponseContainsWithoutDump($serviceCatalogResponse, 'Manajemen Layanan');
        $this->assertResponseContainsWithoutDump($serviceCatalogResponse, 'Syarat keahlian');
        $this->assertResponseContainsWithoutDump($serviceCatalogResponse, 'Identitas Aplikasi');
        $this->assertAdminNavigation(
            $this->navigationXPath($serviceCatalogResponse),
            $serviceCatalogUrl,
            route('admin.locations.index'),
        );

        $locationCatalogUrl = route('admin.catalog.index', ['section' => 'locations']);
        $locationCatalogResponse = $this->actingAs($admin)->get($locationCatalogUrl);
        $locationCatalogResponse->assertOk();
        $this->assertResponseContainsWithoutDump($locationCatalogResponse, 'Manajemen Lokasi');
        $this->assertAdminNavigation(
            $this->navigationXPath($locationCatalogResponse),
            $locationCatalogUrl,
            $locationCatalogUrl,
        );
    }

    public function test_skill_management_matches_user_list_controls_and_searches(): void
    {
        $admin = $this->createUser([Role::SuperAdmin], ['username' => 'skill-list-admin']);
        Skill::factory()->create(['name' => 'Jaringan Kantor', 'slug' => 'jaringan-kantor']);
        Skill::factory()->create(['name' => 'Basis Data', 'slug' => 'basis-data']);

        $this->actingAs($admin)->get(route('admin.skills.index', [
            'q' => 'jaringan',
            'per_page' => 25,
        ]))->assertOk()
            ->assertSee('Daftar Keahlian')
            ->assertSee('Tampilkan')
            ->assertSee('Cari:')
            ->assertSee('Nama Keahlian')
            ->assertSee('Jaringan Kantor')
            ->assertDontSee('Basis Data')
            ->assertDontSee('Kode')
            ->assertDontSee('skill-create-slug', false)
            ->assertSee('data-ui-modal-open="skill-create-modal"', false)
            ->assertSee('data-ui-modal-open="skill-edit-modal-', false);
    }

    private function navigationXPath(TestResponse $response): \DOMXPath
    {
        $content = $response->getContent();
        $this->assertIsString($content, 'Organization page response must contain HTML.');

        $document = new \DOMDocument;
        $previousErrorMode = libxml_use_internal_errors(true);

        try {
            $loaded = $document->loadHTML($content, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousErrorMode);
        }

        $this->assertTrue($loaded, 'Organization page response must be parseable HTML.');

        return new \DOMXPath($document);
    }

    private function assertResponseContainsWithoutDump(TestResponse $response, string $needle): void
    {
        $content = $response->getContent();

        $this->assertIsString($content, 'Organization page response must contain HTML.');
        $this->assertTrue(
            str_contains($content, $needle),
            sprintf('Organization page must contain "%s".', $needle),
        );
    }

    private function assertAdminNavigation(\DOMXPath $xpath, string $activeHref, string $locationHref): void
    {
        $desktopScope = "//aside[@id='app-sidebar' and @data-sidebar]";
        $mobileScope = "//header//nav[@aria-label='Navigasi mobile']";

        $this->assertSame(1, $this->xpathCount($xpath, $desktopScope), 'Desktop navigation must use the application sidebar.');
        $this->assertSame(1, $this->xpathCount($xpath, $mobileScope), 'Mobile navigation must use its dedicated navigation region.');
        $this->assertSame(
            ['Menu Utama', 'Master Data', 'Konfigurasi', 'Laporan'],
            $this->xpathValues($xpath, $desktopScope.'/nav/@aria-label'),
            'Desktop administration navigation groups must remain ordered and distinct.',
        );
        $this->assertSame(
            ['Menu Utama', 'Master Data', 'Konfigurasi', 'Laporan'],
            $this->xpathValues($xpath, $mobileScope."/div[contains(concat(' ', normalize-space(@class), ' '), ' ui-mobile-nav-group-label ')]"),
            'Mobile administration navigation groups must remain ordered and distinct.',
        );

        $links = [
            route('dashboard') => 'Dashboard',
            route('admin.users.index') => 'Manajemen Pengguna',
            route('admin.teams.index') => 'Manajemen Tim Kerja',
            route('admin.skills.index') => 'Manajemen Keahlian',
            route('admin.catalog.index', ['section' => 'services']) => 'Manajemen Layanan',
            $locationHref => 'Manajemen Lokasi',
            route('admin.announcements.index') => 'Manajemen Pengumuman',
            route('admin.branding.index') => 'Manajemen Aplikasi',
            route('reports.index') => 'Laporan Bulanan',
            route('admin.audit-logs.index') => 'Audit Trail',
        ];

        foreach ($links as $href => $label) {
            $this->assertNavigationLink($xpath, $desktopScope, $href, $label, 'ui-nav-link', $href === $activeHref);
            $this->assertNavigationLink($xpath, $mobileScope, $href, $label, 'ui-mobile-nav-link', $href === $activeHref);
        }

        foreach (['Manajemen Formulir', 'Manajemen SLA', 'Parameter Batas Waktu', 'Parameter Jam Layanan', 'Kebijakan Lampiran'] as $label) {
            $expression = $desktopScope."//a[normalize-space(.)='".$label."'] | ".$mobileScope."//a[normalize-space(.)='".$label."']";
            $this->assertSame(0, $this->xpathCount($xpath, $expression), sprintf('Navigation must not expose "%s".', $label));
        }

        foreach ([route('tickets.index'), route('tickets.queue'), route('approvals.index')] as $unauthorizedHref) {
            $expression = $desktopScope."//a[starts-with(@href, '".$unauthorizedHref."')] | ".$mobileScope."//a[starts-with(@href, '".$unauthorizedHref."')]";
            $this->assertSame(0, $this->xpathCount($xpath, $expression), sprintf('Super Admin navigation must not expose operational URL "%s".', $unauthorizedHref));
        }
    }

    private function assertNavigationLink(
        \DOMXPath $xpath,
        string $scope,
        string $href,
        string $label,
        string $baseClass,
        bool $active,
    ): void {
        $links = $xpath->query($scope."//a[@href='".$href."']");
        $this->assertNotFalse($links, sprintf('Navigation selector for "%s" must be valid.', $label));
        $this->assertSame(1, $links->length, sprintf('Navigation must contain one "%s" link for %s.', $label, $href));

        $link = $links->item(0);
        $this->assertNotNull($link, sprintf('Navigation link "%s" must be available.', $label));
        $classes = preg_split('/\s+/', trim((string) $link->attributes?->getNamedItem('class')?->nodeValue)) ?: [];
        $this->assertContains($baseClass, $classes, sprintf('Navigation link "%s" must use %s.', $label, $baseClass));
        $this->assertSame($label, trim((string) preg_replace('/\s+/', ' ', $link->textContent)), sprintf('Navigation link for %s must retain its label.', $href));

        if ($active) {
            $this->assertContains('is-active', $classes, sprintf('Navigation link "%s" must represent the active route.', $label));
        } else {
            $this->assertNotContains('is-active', $classes, sprintf('Navigation link "%s" must not be marked active.', $label));
        }
    }

    private function xpathCount(\DOMXPath $xpath, string $expression): int
    {
        $nodes = $xpath->query($expression);
        $this->assertNotFalse($nodes, sprintf('XPath expression must be valid: %s', $expression));

        return $nodes->length;
    }

    /** @return list<string> */
    private function xpathValues(\DOMXPath $xpath, string $expression): array
    {
        $nodes = $xpath->query($expression);
        $this->assertNotFalse($nodes, sprintf('XPath expression must be valid: %s', $expression));
        $values = [];

        foreach ($nodes as $node) {
            $values[] = trim((string) preg_replace('/\s+/', ' ', $node->textContent));
        }

        return $values;
    }
}
