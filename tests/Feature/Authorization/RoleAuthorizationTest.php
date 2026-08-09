<?php

namespace Tests\Feature\Authorization;

use App\Enums\Role;
use App\Enums\TicketStatus;
use App\Models\AuditLog;
use App\Models\Ticket;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    public function test_user_can_have_multiple_roles_without_granting_super_admin_operational_access(): void
    {
        $user = $this->createUser([Role::SuperAdmin, Role::Pemohon], [
            'username' => 'multi.role',
        ]);

        $this->assertTrue($user->hasRole(Role::SuperAdmin));
        $this->assertTrue($user->hasRole(Role::Pemohon));
        $this->assertFalse($user->hasOperationalRole());
    }

    public function test_super_admin_without_operational_role_cannot_claim_ticket_and_attempt_is_audited(): void
    {
        $admin = $this->createUser([Role::SuperAdmin], ['username' => 'admin.no.operator']);
        $ticket = Ticket::factory()->create(['status' => TicketStatus::Baru]);

        $response = $this->actingAs($admin)->post(route('tickets.claim', $ticket));

        $response->assertForbidden();
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => TicketStatus::Baru->value,
            'assigned_to_id' => null,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'auditable_id' => $ticket->id,
            'action' => 'ticket.claim',
            'outcome' => 'denied',
        ]);
    }

    public function test_super_admin_without_operational_role_cannot_handle_ticket_and_attempt_is_audited(): void
    {
        $admin = $this->createUser([Role::SuperAdmin], ['username' => 'admin.no.handler']);
        $ticket = Ticket::factory()->create([
            'status' => TicketStatus::Diproses,
            'assigned_to_id' => $admin->id,
            'assigned_tier' => 'agen_tier_1',
        ]);

        $response = $this->actingAs($admin)->post(route('tickets.handle', $ticket));

        $response->assertForbidden();
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => TicketStatus::Diproses->value,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'auditable_id' => $ticket->id,
            'action' => 'ticket.handle',
            'outcome' => 'denied',
        ]);
    }

    public function test_initial_password_user_can_claim_ticket_without_changing_password(): void
    {
        $agent = $this->createUser([Role::AgenTier1], array_merge(
            ['username' => 'agent.password.pending'],
            $this->passwordAttributes('Initial-Password-123!'),
            ['must_change_password' => true, 'password_changed_at' => null],
        ));
        $ticket = Ticket::factory()->create(['status' => TicketStatus::Baru]);

        $response = $this->actingAs($agent)->post(route('tickets.claim', $ticket));

        $response->assertRedirect();
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => TicketStatus::Diproses->value,
            'assigned_to_id' => $agent->id,
            'assigned_tier' => Role::AgenTier1->value,
        ]);
    }

    public function test_tier_one_agent_can_claim_a_new_ticket_atomically(): void
    {
        $agent = $this->createUser([Role::AgenTier1], ['username' => 'agent.tier1']);
        $ticket = Ticket::factory()->create(['status' => TicketStatus::Baru]);

        $response = $this->actingAs($agent)->post(route('tickets.claim', $ticket));

        $response->assertRedirect();
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => TicketStatus::Diproses->value,
            'assigned_to_id' => $agent->id,
            'assigned_tier' => 'agen_tier_1',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $agent->id,
            'action' => 'ticket.claim',
            'outcome' => 'succeeded',
        ]);
    }

    public function test_inactive_authenticated_session_is_logged_out_before_operational_access(): void
    {
        $user = $this->createUser([Role::AgenTier1], array_merge(
            ['username' => 'agent.inactive.session'],
            $this->passwordAttributes(),
        ));
        $this->actingAs($user);
        $user->forceFill(['is_active' => false])->save();

        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'))
            ->assertSessionHasErrors(['username' => 'Akun Anda tidak aktif. Hubungi Super Admin.']);
        $this->assertGuest();
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'auth.inactive_session',
            'outcome' => 'denied',
        ]);
    }

    public function test_non_admin_cannot_open_user_administration(): void
    {
        $user = $this->createUser([Role::Pemohon], ['username' => 'ordinary.user']);

        $response = $this->actingAs($user)->get(route('admin.users.index'));

        $response->assertForbidden();
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'auth.role_required',
            'outcome' => 'denied',
        ]);
    }

    public function test_audit_log_cannot_be_updated_or_deleted_through_model_events(): void
    {
        $log = AuditLog::query()->create([
            'action' => 'test.append_only',
            'outcome' => 'succeeded',
        ]);

        $this->expectException(\LogicException::class);
        $log->update(['reason' => 'tidak boleh']);
    }
}
