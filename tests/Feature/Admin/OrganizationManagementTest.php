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
        $this->actingAs($admin)->get(route('admin.catalog.index', ['section' => 'services']))
            ->assertOk()
            ->assertSee('Manajemen Layanan')
            ->assertSee('Syarat keahlian')
            ->assertSeeInOrder(['Menu Utama', 'Master Data', 'Konfigurasi', 'Laporan'])
            ->assertSee('Manajemen Pengguna')
            ->assertSee('Manajemen Tim Kerja')
            ->assertSee('Manajemen Keahlian')
            ->assertDontSee('Manajemen Formulir')
            ->assertSee('Manajemen Lokasi')
            ->assertDontSee('Manajemen SLA')
            ->assertDontSee('Parameter Batas Waktu')
            ->assertDontSee('Parameter Jam Layanan')
            ->assertDontSee('Kebijakan Lampiran')
            ->assertSee('Manajemen Pengumuman')
            ->assertSee('Manajemen Aplikasi')
            ->assertSee('Identitas Aplikasi')
            ->assertSee('Laporan Bulanan')
            ->assertSee('Audit Trail')
            ->assertSee('class="ui-sidebar-dropdown"', false)
            ->assertSee('class="ui-mobile-nav-dropdown"', false)
            ->assertSee('href="'.route('admin.catalog.index', ['section' => 'services']).'" class="ui-nav-link is-active"', false);
        $this->actingAs($admin)->get(route('admin.catalog.index', ['section' => 'locations']))
            ->assertOk()
            ->assertSee('Manajemen Lokasi')
            ->assertSee('href="'.route('admin.catalog.index', ['section' => 'locations']).'" class="ui-nav-link is-active"', false);
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
}
