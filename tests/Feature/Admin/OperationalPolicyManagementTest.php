<?php

namespace Tests\Feature\Admin;

use App\Enums\Role;
use App\Models\ApprovalRequest;
use App\Models\ApproverAssignment;
use App\Models\ServiceCalendar;
use App\Models\ServiceType;
use App\Models\Ticket;
use Database\Seeders\OperationalPolicySeeder;
use Database\Seeders\ServiceCatalogSeeder;
use Tests\TestCase;

class OperationalPolicyManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            ServiceCatalogSeeder::class,
            OperationalPolicySeeder::class,
        ]);
    }

    public function test_sla_is_managed_from_service_catalog_and_standalone_menu_is_removed(): void
    {
        $admin = $this->createUser([Role::SuperAdmin]);
        $pemohon = $this->createUser([Role::Pemohon]);

        $this->actingAs($admin)
            ->get(route('admin.services.index'))
            ->assertOk()
            ->assertSee('Manajemen Layanan')
            ->assertSee('Target SLA')
            ->assertSee('Gunakan target SLA')
            ->assertSee('SVC-01')
            ->assertDontSee('Manajemen SLA');

        $this->get('/admin/operational-policies')
            ->assertNotFound();

        $this->actingAs($pemohon)
            ->get(route('admin.services.index'))
            ->assertForbidden();

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $pemohon->id,
            'action' => 'auth.role_required',
            'outcome' => 'denied',
        ]);
    }

    public function test_super_admin_can_update_versioned_sla_calendar_and_operational_settings(): void
    {
        $admin = $this->createUser([Role::SuperAdmin]);
        $svc01 = ServiceType::query()->where('code', 'SVC-01')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.catalog.services.update', $svc01), [
                'name' => $svc01->name,
                'description' => $svc01->description,
                'ticket_class' => $svc01->ticket_class,
                'uses_sla' => 1,
                'target_working_days' => 2,
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('sla_policies', [
            'service_type_id' => $svc01->id,
            'target_working_days' => 2,
            'version' => 2,
            'is_active' => 1,
        ]);
        $this->assertDatabaseHas('sla_policies', [
            'service_type_id' => $svc01->id,
            'target_working_days' => 1,
            'version' => 1,
            'is_active' => 0,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'admin.sla_policy.updated',
            'outcome' => 'succeeded',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.operational-policies.calendar.update'), [
                'timezone' => 'Asia/Jakarta',
                'working_days' => [1, 2, 3, 4, 5, 6],
                'opens_at' => '09:00',
                'closes_at' => '17:00',
                'holidays_text' => "2026-08-17|Hari Kemerdekaan\n2026-12-25|Hari Natal",
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $calendar = ServiceCalendar::query()->active()->with('holidays')->firstOrFail();
        $this->assertSame(2, $calendar->version);
        $this->assertSame([1, 2, 3, 4, 5, 6], $calendar->working_days);
        $this->assertSame('09:00', substr((string) $calendar->opens_at, 0, 5));
        $this->assertSame(2, $calendar->holidays->count());

        $this->actingAs($admin)
            ->put(route('admin.operational-policies.settings.update'), [
                'sla_warning_percent' => 25,
                'requester_wait_working_days' => 4,
                'confirmation_wait_working_days' => 4,
                'reopen_window_working_days' => 8,
                'max_reopen_count' => 4,
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('operational_settings', [
            'key' => 'sla_warning_percent',
            'value' => '25',
            'version' => 2,
            'is_active' => 1,
        ]);
        $this->assertDatabaseHas('operational_settings', [
            'key' => 'sla_warning_percent',
            'value' => '20',
            'version' => 1,
            'is_active' => 0,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'admin.operational_setting.updated',
            'outcome' => 'succeeded',
        ]);
    }

    public function test_active_approver_replacement_transfers_pending_approvals(): void
    {
        $admin = $this->createUser([Role::SuperAdmin]);
        $oldApprover = $this->createUser([Role::Approver], ['name' => 'Approver Lama']);
        $newApprover = $this->createUser([Role::Approver], ['name' => 'Approver Baru']);
        $ticket = Ticket::factory()->create();

        $assignment = ApproverAssignment::query()->create([
            'user_id' => $oldApprover->id,
            'assigned_by' => $admin->id,
            'started_at' => now()->subDay(),
            'is_active' => true,
        ]);
        $approval = ApprovalRequest::query()->create([
            'ticket_id' => $ticket->id,
            'approver_id' => $oldApprover->id,
            'requested_by' => $admin->id,
            'previous_status' => 'dikerjakan',
            'requested_at' => now()->subHour(),
            'status' => ApprovalRequest::STATUS_PENDING,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.operational-policies.approver.update'), [
                'replacement_user_id' => $newApprover->id,
                'transfer_pending_approvals' => '1',
                'reason' => 'Pergantian Manajer TI.',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('approver_assignments', [
            'id' => $assignment->id,
            'is_active' => 0,
        ]);
        $this->assertDatabaseHas('approver_assignments', [
            'user_id' => $newApprover->id,
            'is_active' => 1,
        ]);
        $this->assertDatabaseHas('approval_requests', [
            'id' => $approval->id,
            'approver_id' => $newApprover->id,
            'transferred_from_id' => $oldApprover->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'admin.approver.replaced',
            'outcome' => 'succeeded',
        ]);
    }

    public function test_active_approver_cannot_be_deactivated_or_have_approver_role_revoked(): void
    {
        $admin = $this->createUser([Role::SuperAdmin]);
        $approver = $this->createUser([Role::Approver]);
        $assignment = ApproverAssignment::query()->create([
            'user_id' => $approver->id,
            'assigned_by' => $admin->id,
            'started_at' => now(),
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.users.deactivate', $approver))
            ->assertRedirect()
            ->assertSessionHasErrors('user_id');

        $this->assertDatabaseHas('users', [
            'id' => $approver->id,
            'is_active' => 1,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.users.roles.update', $approver), [
                'role_ids' => [],
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('role_ids');

        $this->assertTrue($approver->fresh()->hasRole(Role::Approver));
        $this->assertTrue($assignment->fresh()->is_active);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'admin.approver.deactivate',
            'outcome' => 'denied',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'admin.approver.role.revoke',
            'outcome' => 'denied',
        ]);
    }
}
