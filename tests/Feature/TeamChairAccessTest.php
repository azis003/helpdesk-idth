<?php

namespace Tests\Feature;

use App\Enums\Priority;
use App\Enums\Role;
use App\Enums\TicketCommentVisibility;
use App\Enums\TicketSlaSegmentState;
use App\Enums\TicketStatus;
use App\Models\ApprovalRequest;
use App\Models\Attachment;
use App\Models\ServiceFieldDefinition;
use App\Models\ServiceType;
use App\Models\TeamChairAssignment;
use App\Models\TeamMembership;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\TicketFieldValue;
use App\Models\TicketSlaSegment;
use App\Models\User;
use App\Models\WorkTeam;
use App\ViewModels\TeamChairTicketView;
use Database\Seeders\OperationalPolicySeeder;
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class TeamChairAccessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ServiceCatalogSeeder::class);
        $this->seed(OperationalPolicySeeder::class);
        Carbon::setTestNow(Carbon::parse('2026-08-06 15:00:00', 'Asia/Jakarta'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_team_chair_surfaces_use_the_approved_projection(): void
    {
        $fixture = $this->teamTicketFixture();
        $chair = $fixture['chair'];
        $ticket = $fixture['ticket'];

        $detail = $this->actingAs($chair)
            ->get(route('tickets.show', $ticket))
            ->assertOk()
            ->assertViewIs('tickets.team-chair-show')
            ->assertSee($ticket->subject)
            ->assertSee('Solusi yang disetujui untuk pemantauan.')
            ->assertSee('Balasan publik yang disetujui untuk pemantauan.')
            ->assertSee('SLA tiket')
            ->assertDontSee('DESKRIPSI_SENSITIF_KETUA_TIM')
            ->assertDontSee('CATATAN_INTERNAL_SVC07_RAHASIA')
            ->assertDontSee('LAMPIRAN_PRIVAT_KETUA_TIM.pdf');

        $projectedTicket = $detail->viewData('ticket');
        $this->assertInstanceOf(TeamChairTicketView::class, $projectedTicket);
        $this->assertFalse(property_exists($projectedTicket, 'description'));
        $this->assertFalse(property_exists($projectedTicket, 'internalFieldValues'));
        $this->assertSame($ticket->subject, $projectedTicket->subject);

        $this->actingAs($chair)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee($ticket->subject)
            ->assertSee('Balasan publik yang disetujui untuk pemantauan.')
            ->assertDontSee('DESKRIPSI_SENSITIF_KETUA_TIM')
            ->assertDontSee('CATATAN_INTERNAL_SVC07_RAHASIA')
            ->assertDontSee('LAMPIRAN_PRIVAT_KETUA_TIM.pdf');

        $this->actingAs($chair)
            ->get(route('tickets.index'))
            ->assertOk()
            ->assertSee($ticket->subject)
            ->assertDontSee('DESKRIPSI_SENSITIF_KETUA_TIM')
            ->assertDontSee('CATATAN_INTERNAL_SVC07_RAHASIA')
            ->assertDontSee('LAMPIRAN_PRIVAT_KETUA_TIM.pdf');

        $this->actingAs($chair)
            ->get(route('reports.index'))
            ->assertForbidden();
    }

    public function test_team_chair_cannot_write_operate_or_download_from_a_ticket(): void
    {
        $fixture = $this->teamTicketFixture();
        $chair = $fixture['chair'];
        $ticket = $fixture['ticket'];
        $attachment = $fixture['attachment'];
        $approval = $fixture['approval'];

        foreach ([
            'commentPublic',
            'commentInternal',
            'complete',
            'uploadAttachment',
            'handle',
            'triage',
            'assignTierTwo',
            'startDatabaseChange',
            'verifyDatabaseChange',
        ] as $ability) {
            $this->assertFalse(Gate::forUser($chair)->allows($ability, $ticket), $ability.' should be denied');
        }

        $this->assertFalse(Gate::forUser($chair)->allows('decide', $approval));
        $this->assertFalse(Gate::forUser($chair)->allows('view', $attachment));

        $this->actingAs($chair)
            ->post(route('tickets.comments.public', $ticket), ['body' => 'Komentar yang tidak boleh dikirim.'])
            ->assertForbidden();

        $this->actingAs($chair)
            ->post(route('tickets.complete', $ticket), ['solution' => 'Solusi yang tidak boleh diubah.'])
            ->assertForbidden();

        $this->actingAs($chair)
            ->post(route('tickets.attachments.store', $ticket), [
                'attachments' => [
                    '1' => [UploadedFile::fake()->create('tidak-boleh.pdf', 10, 'application/pdf')],
                ],
            ])
            ->assertForbidden();

        $this->actingAs($chair)
            ->get(route('attachments.download', $attachment))
            ->assertForbidden();

        $this->actingAs($chair)
            ->post(route('approvals.approve', $approval), ['decision_note' => 'Tidak boleh menyetujui.'])
            ->assertForbidden();
    }

    /**
     * @return array{
     *     chair: User,
     *     ticket: Ticket,
     *     attachment: Attachment,
     *     approval: ApprovalRequest
     * }
     */
    private function teamTicketFixture(): array
    {
        $chair = $this->createUser([Role::KetuaTimKerja], ['name' => 'Ketua Tim Projection']);
        $member = $this->createUser([Role::Pemohon], ['name' => 'Anggota Projection']);
        $agent = $this->createUser([Role::AgenTier1], ['name' => 'Agen Projection']);
        $team = WorkTeam::factory()->create(['name' => 'Tim Projection']);

        TeamMembership::query()->create([
            'work_team_id' => $team->id,
            'user_id' => $member->id,
            'is_active' => true,
            'started_at' => now()->subMonth(),
        ]);
        TeamChairAssignment::query()->create([
            'work_team_id' => $team->id,
            'user_id' => $chair->id,
            'is_active' => true,
            'started_at' => now()->subMonth(),
        ]);

        $service = ServiceType::query()->where('code', 'SVC-07')->firstOrFail();
        $ticket = Ticket::factory()->create([
            'ticket_number' => 'CHG-2026-00991',
            'ticket_class' => 'CHG',
            'ticket_year' => 2026,
            'ticket_sequence' => 991,
            'subject' => 'Tiket projection anggota tim',
            'requester_id' => $member->id,
            'created_by_id' => $member->id,
            'requester_name_snapshot' => $member->name,
            'requester_nip_snapshot' => 'NIP_SENSITIF_KETUA_TIM',
            'requester_team_snapshot' => $team->name,
            'service_type_id' => $service->id,
            'service_type_code_snapshot' => $service->code,
            'service_type_name_snapshot' => $service->name,
            'priority' => Priority::Tinggi,
            'status' => TicketStatus::Dikerjakan,
            'assigned_to_id' => $agent->id,
            'assigned_tier' => Role::AgenTier1->value,
            'description' => 'DESKRIPSI_SENSITIF_KETUA_TIM',
            'solution' => 'Solusi yang disetujui untuk pemantauan.',
            'submitted_at' => now()->subHours(2),
        ]);

        $internalDefinition = ServiceFieldDefinition::query()
            ->where('service_type_id', $service->id)
            ->where('visibility', 'internal')
            ->firstOrFail();
        TicketFieldValue::query()->create([
            'ticket_id' => $ticket->id,
            'service_field_definition_id' => $internalDefinition->id,
            'field_key' => $internalDefinition->key,
            'label_snapshot' => $internalDefinition->label,
            'field_type_snapshot' => $internalDefinition->field_type,
            'visibility_snapshot' => 'internal',
            'version_snapshot' => $internalDefinition->version,
            'value' => 'CATATAN_INTERNAL_SVC07_RAHASIA',
        ]);

        TicketComment::query()->create([
            'ticket_id' => $ticket->id,
            'author_id' => $agent->id,
            'visibility' => TicketCommentVisibility::Public,
            'body' => 'Balasan publik yang disetujui untuk pemantauan.',
        ]);
        TicketComment::query()->create([
            'ticket_id' => $ticket->id,
            'author_id' => $agent->id,
            'visibility' => TicketCommentVisibility::Internal,
            'body' => 'CATATAN_INTERNAL_SVC07_RAHASIA',
        ]);

        $attachment = Attachment::query()->create([
            'ticket_id' => $ticket->id,
            'uploaded_by_id' => $agent->id,
            'type_key' => 'internal_evidence',
            'type_label_snapshot' => 'Bukti internal',
            'original_name' => 'LAMPIRAN_PRIVAT_KETUA_TIM.pdf',
            'storage_disk' => 'local',
            'storage_path' => 'tickets/'.$ticket->id.'/private.pdf',
            'size_bytes' => 100,
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'visibility' => 'internal',
        ]);

        TicketSlaSegment::query()->create([
            'ticket_id' => $ticket->id,
            'cycle' => 1,
            'state' => TicketSlaSegmentState::Active,
            'reason' => 'ticket_created',
            'target_working_days' => 1,
            'target_working_minutes' => 480,
            'started_at' => now()->subHour(),
        ]);

        $approval = ApprovalRequest::query()->create([
            'ticket_id' => $ticket->id,
            'approver_id' => $agent->id,
            'status' => ApprovalRequest::STATUS_PENDING,
            'previous_status' => TicketStatus::Dikerjakan->value,
            'previous_assignee_id' => $agent->id,
            'previous_tier' => Role::AgenTier1->value,
            'requested_by' => $agent->id,
            'requested_at' => now(),
        ]);

        return compact('chair', 'ticket', 'attachment', 'approval');
    }
}
