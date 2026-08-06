<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Enums\TicketSlaSegmentState;
use App\Enums\TicketStatus;
use App\Models\ApprovalRequest;
use App\Models\ApproverAssignment;
use App\Models\ServiceType;
use App\Models\Ticket;
use App\Notifications\TicketEventNotification;
use Database\Seeders\OperationalPolicySeeder;
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TicketApprovalTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ServiceCatalogSeeder::class);
        $this->seed(OperationalPolicySeeder::class);
        Carbon::setTestNow(Carbon::parse('2026-08-06 09:00:00', 'Asia/Jakarta'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_assigned_agent_can_request_approval_and_active_approver_can_restore_previous_state(): void
    {
        $requester = $this->createUser([Role::Pemohon], ['name' => 'Pemohon Tiket']);
        $agent = $this->createUser([Role::AgenTier1], ['name' => 'Agen Penanganan']);
        $approver = $this->createUser([Role::Approver], ['name' => 'Manajer TI']);
        $this->assignApprover($approver);
        $service = ServiceType::query()->where('code', 'SVC-01')->firstOrFail();
        $ticket = Ticket::factory()->create([
            'ticket_number' => 'INC-2026-00001',
            'requester_id' => $requester->id,
            'created_by_id' => $requester->id,
            'service_type_id' => $service->id,
            'status' => TicketStatus::Dikerjakan,
            'assigned_to_id' => $agent->id,
            'assigned_tier' => Role::AgenTier1->value,
        ]);

        $this->actingAs($agent)
            ->post(route('tickets.request-approval', $ticket), [
                'reason' => 'Perubahan ini memerlukan persetujuan Manajer TI.',
            ])
            ->assertRedirect(route('tickets.show', $ticket))
            ->assertSessionHasNoErrors();

        $ticket->refresh();
        $approval = ApprovalRequest::query()->firstOrFail();
        $this->assertSame(TicketStatus::MenungguPersetujuan, $ticket->status);
        $this->assertSame(TicketStatus::Dikerjakan->value, $approval->previous_status);
        $this->assertSame($agent->id, $approval->previous_assignee_id);
        $this->assertSame(Role::AgenTier1->value, $approval->previous_tier);
        $this->assertSame($approver->id, $approval->approver_id);
        $this->assertDatabaseHas('ticket_status_histories', [
            'ticket_id' => $ticket->id,
            'from_status' => TicketStatus::Dikerjakan->value,
            'to_status' => TicketStatus::MenungguPersetujuan->value,
            'action' => 'ticket.approval.requested',
        ]);
        $this->assertDatabaseHas('ticket_sla_segments', [
            'ticket_id' => $ticket->id,
            'state' => TicketSlaSegmentState::Paused->value,
            'reason' => 'approval',
        ]);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $approver->id,
            'type' => TicketEventNotification::class,
        ]);

        $this->actingAs($approver)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Perlu Tindakan Saya')
            ->assertSee($ticket->subject);

        $this->actingAs($approver)
            ->get(route('tickets.show', $ticket))
            ->assertOk()
            ->assertSee('Menunggu keputusan Manajer TI')
            ->assertSee('Setuju')
            ->assertSee('Tidak Setuju');

        $this->actingAs($approver)
            ->post(route('approvals.approve', $approval), [
                'decision_note' => 'Disetujui untuk dilanjutkan.',
            ])
            ->assertRedirect(route('tickets.show', $ticket))
            ->assertSessionHasNoErrors();

        $ticket->refresh();
        $approval->refresh();
        $this->assertSame(TicketStatus::Dikerjakan, $ticket->status);
        $this->assertSame($agent->id, $ticket->assigned_to_id);
        $this->assertSame(Role::AgenTier1->value, $ticket->assigned_tier);
        $this->assertSame(ApprovalRequest::STATUS_APPROVED, $approval->status);
        $this->assertSame('Disetujui untuk dilanjutkan.', $approval->decision_note);
        $this->assertDatabaseHas('ticket_status_histories', [
            'ticket_id' => $ticket->id,
            'from_status' => TicketStatus::MenungguPersetujuan->value,
            'to_status' => TicketStatus::Dikerjakan->value,
            'action' => 'ticket.approval.approved',
            'actor_id' => $approver->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $approver->id,
            'auditable_id' => $ticket->id,
            'action' => 'ticket.approval.approved',
            'outcome' => 'succeeded',
        ]);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $requester->id,
            'type' => TicketEventNotification::class,
        ]);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $agent->id,
            'type' => TicketEventNotification::class,
        ]);
    }

    public function test_rejection_requires_note_and_sets_final_status(): void
    {
        $requester = $this->createUser([Role::Pemohon]);
        $agent = $this->createUser([Role::AgenTier2]);
        $approver = $this->createUser([Role::Approver]);
        $this->assignApprover($approver);
        $ticket = $this->ticketFor($requester, $agent, TicketStatus::Diproses, Role::AgenTier2->value);

        $approval = ApprovalRequest::query()->create([
            'ticket_id' => $ticket->id,
            'approver_id' => $approver->id,
            'status' => ApprovalRequest::STATUS_PENDING,
            'previous_status' => TicketStatus::Diproses->value,
            'previous_assignee_id' => $agent->id,
            'previous_tier' => Role::AgenTier2->value,
            'requested_by' => $agent->id,
            'requested_at' => now()->subHour(),
        ]);
        $ticket->forceFill(['status' => TicketStatus::MenungguPersetujuan])->save();

        $this->actingAs($approver)
            ->post(route('approvals.reject', $approval))
            ->assertRedirect()
            ->assertSessionHasErrors('decision_note');

        $this->assertSame(ApprovalRequest::STATUS_PENDING, $approval->fresh()->status);
        $this->assertSame(TicketStatus::MenungguPersetujuan, $ticket->fresh()->status);

        $reason = 'Risiko perubahan belum dapat diterima.';
        $this->actingAs($approver)
            ->post(route('approvals.reject', $approval), ['decision_note' => $reason])
            ->assertRedirect(route('tickets.show', $ticket))
            ->assertSessionHasNoErrors();

        $this->assertSame(ApprovalRequest::STATUS_REJECTED, $approval->fresh()->status);
        $this->assertSame(TicketStatus::TidakDisetujui, $ticket->fresh()->status);
        $this->assertSame($reason, $approval->fresh()->decision_note);

        $this->actingAs($requester)
            ->get(route('tickets.show', $ticket))
            ->assertOk()
            ->assertSee('Tidak Disetujui')
            ->assertSee($reason);
    }

    public function test_non_active_approver_and_initial_password_account_cannot_decide(): void
    {
        $requester = $this->createUser([Role::Pemohon]);
        $agent = $this->createUser([Role::AgenTier1]);
        $approver = $this->createUser([Role::Approver]);
        $this->assignApprover($approver);
        $otherApprover = $this->createUser([Role::Approver]);
        $ticket = $this->ticketFor($requester, $agent, TicketStatus::Dikerjakan, Role::AgenTier1->value);
        $approval = $this->pendingApproval($ticket, $approver, $agent);

        $this->actingAs($otherApprover)
            ->post(route('approvals.approve', $approval))
            ->assertForbidden();
        $this->assertSame(ApprovalRequest::STATUS_PENDING, $approval->fresh()->status);

        $approver->forceFill([
            'must_change_password' => true,
            'password_changed_at' => null,
        ])->save();

        $this->actingAs($approver)
            ->post(route('approvals.approve', $approval))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('warning');
        $this->assertSame(ApprovalRequest::STATUS_PENDING, $approval->fresh()->status);
        $this->assertSame(TicketStatus::MenungguPersetujuan, $ticket->fresh()->status);
    }

    private function assignApprover(object $approver): ApproverAssignment
    {
        return ApproverAssignment::query()->create([
            'user_id' => $approver->id,
            'started_at' => now()->subDay(),
            'is_active' => true,
        ]);
    }

    private function ticketFor(object $requester, object $assignee, TicketStatus $status, string $tier): Ticket
    {
        $service = ServiceType::query()->where('code', 'SVC-01')->firstOrFail();

        return Ticket::factory()->create([
            'ticket_number' => 'INC-2026-'.fake()->unique()->numerify('#####'),
            'requester_id' => $requester->id,
            'created_by_id' => $requester->id,
            'service_type_id' => $service->id,
            'status' => $status,
            'assigned_to_id' => $assignee->id,
            'assigned_tier' => $tier,
        ]);
    }

    private function pendingApproval(Ticket $ticket, object $approver, object $agent): ApprovalRequest
    {
        $approval = ApprovalRequest::query()->create([
            'ticket_id' => $ticket->id,
            'approver_id' => $approver->id,
            'status' => ApprovalRequest::STATUS_PENDING,
            'previous_status' => $ticket->status->value,
            'previous_assignee_id' => $agent->id,
            'previous_tier' => $ticket->assigned_tier,
            'requested_by' => $agent->id,
            'requested_at' => now()->subHour(),
        ]);
        $ticket->forceFill(['status' => TicketStatus::MenungguPersetujuan])->save();

        return $approval;
    }
}
