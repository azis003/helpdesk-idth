<?php

namespace Tests\Feature\Admin;

use App\Enums\Role;
use App\Models\Role as RoleModel;
use App\Models\ServiceType;
use App\Models\Skill;
use App\Models\TeamChairAssignment;
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
            'skill_ids' => [$skill->id],
        ]);

        $response->assertRedirect();
        $user = User::query()->where('username', 'teknisi-baru')->firstOrFail();

        $this->assertTrue(Hash::check('Initial-Password-123!', $user->password));
        $this->assertTrue($user->must_change_password);
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

    public function test_team_chair_must_be_an_active_member_and_assignment_is_audited(): void
    {
        $admin = $this->createUser([Role::SuperAdmin], ['username' => 'chair-admin']);
        $member = $this->createUser([Role::KetuaTimKerja], ['username' => 'chair-member']);
        $outsider = $this->createUser([Role::Pemohon], ['username' => 'chair-outsider']);
        $team = WorkTeam::factory()->create(['name' => 'Tim Ketua']);

        $this->actingAs($admin)->post(route('admin.teams.members.assign', $team), ['user_id' => $member->id])
            ->assertRedirect();
        $this->actingAs($admin)->put(route('admin.teams.chair.update', $team), ['user_id' => $member->id])
            ->assertRedirect();

        $this->assertDatabaseHas('team_chair_assignments', [
            'work_team_id' => $team->id,
            'user_id' => $member->id,
            'is_active' => true,
        ]);

        $this->actingAs($admin)->put(route('admin.teams.chair.update', $team), ['user_id' => $outsider->id])
            ->assertSessionHasErrors('user_id');
        $this->assertSame(1, TeamChairAssignment::query()->where('work_team_id', $team->id)->where('is_active', true)->count());
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'auditable_id' => $team->id,
            'action' => 'admin.team.chair.changed',
            'outcome' => 'succeeded',
        ]);
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
        $team = WorkTeam::factory()->create(['name' => 'Tim Halaman']);
        $skill = Skill::factory()->create(['name' => 'Dukungan Aplikasi', 'slug' => 'dukungan-aplikasi']);
        $team->currentMembers()->attach($admin->id, [
            'assigned_by' => $admin->id,
            'started_at' => now(),
            'is_active' => true,
        ]);
        $this->actingAs($admin)->get(route('admin.users.index'))->assertOk()->assertSee('Pengguna dan akses');
        $this->actingAs($admin)->get(route('admin.users.edit', $admin))->assertOk()->assertSee('Riwayat organisasi');
        $this->actingAs($admin)->get(route('admin.teams.index'))->assertOk()->assertSee('Tim Halaman');
        $this->actingAs($admin)->get(route('admin.skills.index'))->assertOk()->assertSee('Dukungan Aplikasi')->assertDontSee('Kategori Masalah');
        $this->actingAs($admin)->get(route('admin.catalog.index', ['section' => 'services']))
            ->assertOk()
            ->assertSee('Layanan &amp; formulir', false)
            ->assertSee('Keahlian penanganan')
            ->assertSeeInOrder(['Menu Utama', 'Master Data', 'Konfigurasi', 'Laporan'])
            ->assertSee('Manajemen Pengguna')
            ->assertSee('Manajemen Tim Kerja')
            ->assertSee('Manajemen Keahlian')
            ->assertSee('Manajemen Formulir')
            ->assertSee('Manajemen Lokasi')
            ->assertSee('Manajemen SLA')
            ->assertSee('Parameter Batas Waktu')
            ->assertSee('Parameter Jam Layanan')
            ->assertSee('Kebijakan Lampiran')
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
}
