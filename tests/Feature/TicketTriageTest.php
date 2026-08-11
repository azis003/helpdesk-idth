<?php

namespace Tests\Feature;

use App\Enums\Priority;
use App\Enums\Role;
use App\Enums\TicketAssignmentAction;
use App\Enums\TicketStatus;
use App\Models\ProblemCategory;
use App\Models\ServiceType;
use App\Models\Skill;
use App\Models\Ticket;
use App\Models\TicketAssignmentHistory;
use Database\Seeders\ServiceCatalogSeeder;
use Tests\TestCase;

class TicketTriageTest extends TestCase
{
    public function test_tier_one_queue_is_shared_and_sorted_by_priority_then_age(): void
    {
        $agent = $this->createUser([Role::AgenTier1]);

        $oldLow = Ticket::factory()->create([
            'subject' => 'Tiket rendah paling lama',
            'priority' => Priority::Rendah,
            'submitted_at' => now()->subDays(4),
        ]);
        $high = Ticket::factory()->create([
            'subject' => 'Tiket tinggi',
            'priority' => Priority::Tinggi,
            'submitted_at' => now()->subDays(3),
        ]);
        $critical = Ticket::factory()->create([
            'subject' => 'Tiket kritis',
            'priority' => Priority::Kritis,
            'submitted_at' => now()->subDay(),
        ]);
        $medium = Ticket::factory()->create([
            'subject' => 'Tiket sedang',
            'priority' => Priority::Sedang,
            'submitted_at' => now()->subDays(2),
        ]);

        $this->actingAs($agent)
            ->get(route('tickets.queue'))
            ->assertOk()
            ->assertSeeInOrder([
                $critical->subject,
                $high->subject,
                $medium->subject,
                $oldLow->subject,
            ]);
    }

    public function test_work_area_separates_shared_queue_and_personal_tickets_by_tab(): void
    {
        $agent = $this->createUser([Role::AgenTier1]);
        $requester = $this->createUser([Role::Pemohon]);
        $queueTicket = Ticket::factory()->create([
            'subject' => 'Tiket pada antrian bersama',
            'requester_id' => $requester->id,
            'created_by_id' => $requester->id,
            'status' => TicketStatus::Baru,
            'assigned_to_id' => null,
        ]);
        $mineTicket = Ticket::factory()->create([
            'subject' => 'Tiket pada tanggung jawab saya',
            'requester_id' => $requester->id,
            'created_by_id' => $requester->id,
            'status' => TicketStatus::Dikerjakan,
            'assigned_to_id' => $agent->id,
            'assigned_tier' => Role::AgenTier1->value,
        ]);

        $this->actingAs($agent)
            ->get(route('tickets.queue', ['tab' => 'queue']))
            ->assertOk()
            ->assertSee('Antrian Tiket')
            ->assertSee($queueTicket->subject)
            ->assertDontSee($mineTicket->subject);

        $this->actingAs($agent)
            ->get(route('tickets.queue', ['tab' => 'mine']))
            ->assertOk()
            ->assertSee('Tiket Saya')
            ->assertSee($mineTicket->subject)
            ->assertDontSee($queueTicket->subject);
    }

    public function test_work_area_tabs_keep_the_requester_table_shape_and_compact_empty_state(): void
    {
        $agent = $this->createUser([Role::AgenTier1]);

        foreach (['queue', 'mine'] as $tab) {
            $this->actingAs($agent)
                ->get(route('tickets.queue', ['tab' => $tab]))
                ->assertOk()
                ->assertSeeInOrder(['No', 'No Tiket', 'Layanan', 'Judul', 'Status', 'Prioritas'])
                ->assertSee('Tidak ada data')
                ->assertDontSee('Tiket yang belum diambil')
                ->assertDontSee('Ambil tiket');
        }
    }

    public function test_tier_one_can_open_new_ticket_detail_and_claim_before_triage(): void
    {
        $agent = $this->createUser([Role::AgenTier1]);
        $ticket = Ticket::factory()->create([
            'status' => TicketStatus::Baru,
            'assigned_to_id' => null,
        ]);

        $this->actingAs($agent)
            ->get(route('tickets.show', $ticket))
            ->assertOk()
            ->assertSee('Ambil Tiket')
            ->assertSee('ticket-reference-page-actions')
            ->assertSee('ticket-reference-action-button--primary')
            ->assertDontSee('Ambil tiket dan lanjutkan')
            ->assertSee('ticket-reference-content')
            ->assertSee('ticket-description-heading')
            ->assertSee('Informasi Tiket')
            ->assertSee('Aktivitas Tiket')
            ->assertDontSee('Simpan triase');

        $this->actingAs($agent)
            ->post(route('tickets.claim', $ticket))
            ->assertRedirect(route('tickets.show', $ticket))
            ->assertSessionHasNoErrors();

        $this->actingAs($agent)
            ->get(route('tickets.show', $ticket->fresh()))
            ->assertOk()
            ->assertSee('Triase tiket')
            ->assertSee('data-ui-modal-open="ticket-triage-modal"', false)
            ->assertSee('id="ticket-triage-modal"', false)
            ->assertDontSee('ui-ticket-action-summary')
            ->assertDontSee('Penanganan');
    }

    public function test_tier_two_sees_only_personal_tickets_in_the_work_area(): void
    {
        $technician = $this->createUser([Role::AgenTier2]);
        $assignedTicket = Ticket::factory()->create([
            'subject' => 'Tiket teknisi saya',
            'status' => TicketStatus::Dikerjakan,
            'assigned_to_id' => $technician->id,
            'assigned_tier' => Role::AgenTier2->value,
        ]);
        $queueTicket = Ticket::factory()->create([
            'subject' => 'Tiket yang menunggu Helpdesk',
            'status' => TicketStatus::Baru,
            'assigned_to_id' => null,
        ]);

        $this->actingAs($technician)
            ->get(route('tickets.queue'))
            ->assertOk()
            ->assertSee('Tiket Saya')
            ->assertSee($assignedTicket->subject)
            ->assertDontSee($queueTicket->subject);

        $this->actingAs($technician)
            ->get(route('tickets.queue', ['tab' => 'queue']))
            ->assertForbidden();

        $this->actingAs($technician)
            ->get(route('tickets.all'))
            ->assertForbidden();
    }

    public function test_all_tickets_list_contains_tickets_outside_the_current_assignment(): void
    {
        $agent = $this->createUser([Role::AgenTier1]);
        $otherRequester = $this->createUser([Role::Pemohon]);
        $first = Ticket::factory()->create([
            'subject' => 'Tiket yang sedang saya kerjakan',
            'assigned_to_id' => $agent->id,
            'assigned_tier' => Role::AgenTier1->value,
            'status' => TicketStatus::Dikerjakan,
        ]);
        $second = Ticket::factory()->create([
            'subject' => 'Tiket milik penanggung jawab lain',
            'requester_id' => $otherRequester->id,
            'created_by_id' => $otherRequester->id,
            'status' => TicketStatus::Ditutup,
            'assigned_to_id' => null,
        ]);

        $this->actingAs($agent)
            ->get(route('tickets.all'))
            ->assertOk()
            ->assertSee('Semua Tiket')
            ->assertSee('Daftar seluruh tiket')
            ->assertSee($first->subject)
            ->assertSee($second->subject);
    }

    public function test_second_claim_gets_clear_failure_after_first_agent_wins(): void
    {
        $winner = $this->createUser([Role::AgenTier1], ['username' => 'agent.winner']);
        $loser = $this->createUser([Role::AgenTier1], ['username' => 'agent.loser']);
        $ticket = Ticket::factory()->create(['status' => TicketStatus::Baru]);

        $this->actingAs($winner)
            ->post(route('tickets.claim', $ticket))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->actingAs($loser)
            ->post(route('tickets.claim', $ticket))
            ->assertRedirect()
            ->assertSessionHasErrors('ticket');

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => TicketStatus::Diproses->value,
            'assigned_to_id' => $winner->id,
            'assigned_tier' => Role::AgenTier1->value,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $loser->id,
            'auditable_id' => $ticket->id,
            'action' => 'ticket.claim',
            'outcome' => 'denied',
        ]);
    }

    public function test_tier_one_can_triage_self_and_records_category_priority_and_assignment_history(): void
    {
        $agent = $this->createUser([Role::AgenTier1]);
        $category = ProblemCategory::factory()->create(['name' => 'Aplikasi Internal']);
        $ticket = Ticket::factory()->create([
            'status' => TicketStatus::Diproses,
            'assigned_to_id' => $agent->id,
            'assigned_tier' => Role::AgenTier1->value,
            'priority' => Priority::Sedang,
        ]);

        $this->actingAs($agent)
            ->post(route('tickets.triage', $ticket), [
                'outcome' => 'self',
                'problem_category_id' => $category->id,
                'priority' => Priority::Tinggi->value,
                'category_reason' => 'Permintaan termasuk dukungan aplikasi internal.',
                'priority_reason' => 'Dampak pekerjaan pemohon tinggi.',
            ])
            ->assertRedirect(route('tickets.show', $ticket))
            ->assertSessionHasNoErrors();

        $ticket->refresh();
        $this->assertSame(TicketStatus::Dikerjakan, $ticket->status);
        $this->assertSame(Priority::Tinggi, $ticket->priority);
        $this->assertSame($category->id, $ticket->problem_category_id);
        $this->assertSame($agent->id, $ticket->last_triaged_by_id);
        $this->assertDatabaseHas('ticket_category_histories', [
            'ticket_id' => $ticket->id,
            'from_category_id' => null,
            'to_category_id' => $category->id,
            'reason' => 'Permintaan termasuk dukungan aplikasi internal.',
        ]);
        $this->assertDatabaseHas('ticket_priority_histories', [
            'ticket_id' => $ticket->id,
            'from_priority' => Priority::Sedang->value,
            'to_priority' => Priority::Tinggi->value,
            'reason' => 'Dampak pekerjaan pemohon tinggi.',
        ]);
        $this->assertDatabaseHas('ticket_assignment_histories', [
            'ticket_id' => $ticket->id,
            'action' => TicketAssignmentAction::TriagedSelf->value,
            'to_user_id' => $agent->id,
            'to_tier' => Role::AgenTier1->value,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $agent->id,
            'auditable_id' => $ticket->id,
            'action' => 'ticket.triaged',
            'outcome' => 'succeeded',
        ]);

        $this->actingAs($agent)
            ->get(route('tickets.show', $ticket))
            ->assertOk()
            ->assertSee('Triase tiket')
            ->assertSee($category->name)
            ->assertSee('Aktivitas Tiket');
    }

    public function test_tier_one_can_assign_tier_two_using_manual_choice_while_saving_skill_suggestions(): void
    {
        $agent = $this->createUser([Role::AgenTier1]);
        $suggested = $this->createUser([Role::AgenTier2], ['name' => 'Teknisi Disarankan']);
        $manual = $this->createUser([Role::AgenTier2], ['name' => 'Teknisi Pilihan Manual']);
        $skill = Skill::factory()->create(['name' => 'Dukungan jaringan']);
        $category = ProblemCategory::factory()->create(['name' => 'Jaringan kantor']);
        $category->skills()->attach($skill->id);
        $suggested->skills()->attach($skill->id);
        $ticket = Ticket::factory()->create([
            'status' => TicketStatus::Diproses,
            'assigned_to_id' => $agent->id,
            'assigned_tier' => Role::AgenTier1->value,
            'problem_category_id' => $category->id,
            'priority' => Priority::Sedang,
        ]);

        $this->actingAs($agent)
            ->post(route('tickets.triage', $ticket), [
                'outcome' => 'tier_2',
                'problem_category_id' => $category->id,
                'priority' => Priority::Sedang->value,
                'assigned_to_id' => $manual->id,
            ])
            ->assertRedirect(route('tickets.show', $ticket))
            ->assertSessionHasNoErrors();

        $ticket->refresh();
        $this->assertSame(TicketStatus::Dikerjakan, $ticket->status);
        $this->assertSame($manual->id, $ticket->assigned_to_id);
        $this->assertSame(Role::AgenTier2->value, $ticket->assigned_tier);

        $assignment = TicketAssignmentHistory::query()
            ->where('ticket_id', $ticket->id)
            ->where('action', TicketAssignmentAction::TriagedTier2->value)
            ->firstOrFail();
        $this->assertSame($manual->id, $assignment->to_user_id);
        $this->assertTrue(collect($assignment->suggestions)->contains('user_id', $suggested->id));
    }

    public function test_service_mapping_drives_suggestions_without_problem_category(): void
    {
        $this->seed(ServiceCatalogSeeder::class);

        $agent = $this->createUser([Role::AgenTier1]);
        $suggested = $this->createUser([Role::AgenTier2], ['name' => 'Teknisi Layanan']);
        $manual = $this->createUser([Role::AgenTier2], ['name' => 'Teknisi Manual']);
        $skill = Skill::factory()->create(['name' => 'Layanan Data']);
        $service = ServiceType::query()->where('code', 'SVC-02')->firstOrFail();
        $service->skills()->attach($skill->id);
        $suggested->skills()->attach($skill->id);
        $ticket = Ticket::factory()->create([
            'service_type_id' => $service->id,
            'status' => TicketStatus::Diproses,
            'assigned_to_id' => $agent->id,
            'assigned_tier' => Role::AgenTier1->value,
            'priority' => Priority::Sedang,
        ]);

        $this->actingAs($agent)
            ->post(route('tickets.triage', $ticket), [
                'outcome' => 'tier_2',
                'priority' => Priority::Sedang->value,
                'assigned_to_id' => $manual->id,
            ])
            ->assertRedirect(route('tickets.show', $ticket))
            ->assertSessionHasNoErrors();

        $assignment = TicketAssignmentHistory::query()
            ->where('ticket_id', $ticket->id)
            ->where('action', TicketAssignmentAction::TriagedTier2->value)
            ->firstOrFail();
        $this->assertTrue(collect($assignment->suggestions)->contains('user_id', $suggested->id));
    }

    public function test_tier_two_returns_ticket_to_last_triaging_tier_one(): void
    {
        $agent = $this->createUser([Role::AgenTier1]);
        $tierTwo = $this->createUser([Role::AgenTier2]);
        $ticket = Ticket::factory()->create([
            'status' => TicketStatus::Dikerjakan,
            'assigned_to_id' => $tierTwo->id,
            'assigned_tier' => Role::AgenTier2->value,
            'last_triaged_by_id' => $agent->id,
        ]);

        $this->actingAs($tierTwo)
            ->post(route('tickets.return-to-tier-1', $ticket), [
                'reason' => 'Informasi teknis perlu dilengkapi oleh Agen Tier 1.',
            ])
            ->assertRedirect(route('tickets.show', $ticket))
            ->assertSessionHasNoErrors();

        $ticket->refresh();
        $this->assertSame(TicketStatus::Diproses, $ticket->status);
        $this->assertSame($agent->id, $ticket->assigned_to_id);
        $this->assertSame(Role::AgenTier1->value, $ticket->assigned_tier);
        $this->assertDatabaseHas('ticket_assignment_histories', [
            'ticket_id' => $ticket->id,
            'action' => TicketAssignmentAction::ReturnedTier1->value,
            'from_user_id' => $tierTwo->id,
            'to_user_id' => $agent->id,
        ]);
    }

    public function test_rejection_reason_is_visible_to_requester(): void
    {
        $requester = $this->createUser([Role::Pemohon]);
        $agent = $this->createUser([Role::AgenTier1]);
        $ticket = Ticket::factory()->create([
            'requester_id' => $requester->id,
            'created_by_id' => $requester->id,
            'status' => TicketStatus::Diproses,
            'assigned_to_id' => $agent->id,
            'assigned_tier' => Role::AgenTier1->value,
            'priority' => Priority::Sedang,
        ]);

        $reason = 'Permintaan berada di luar katalog layanan SIHATI.';
        $this->actingAs($agent)
            ->post(route('tickets.triage', $ticket), [
                'outcome' => 'reject',
                'priority' => Priority::Sedang->value,
                'rejection_reason' => $reason,
            ])
            ->assertRedirect(route('tickets.show', $ticket))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => TicketStatus::Ditolak->value,
            'rejection_reason' => $reason,
        ]);

        $this->actingAs($requester)
            ->get(route('tickets.show', $ticket))
            ->assertOk()
            ->assertSee('Alasan penolakan')
            ->assertSee($reason);
    }
}
