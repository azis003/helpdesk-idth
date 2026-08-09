<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Enums\TicketCommentVisibility;
use App\Enums\TicketSlaSegmentState;
use App\Enums\TicketStatus;
use App\Models\ApprovalRequest;
use App\Models\ProblemCategory;
use App\Models\ServiceCalendar;
use App\Models\ServiceType;
use App\Models\TeamChairAssignment;
use App\Models\TeamMembership;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\TicketPriorityHistory;
use App\Models\TicketSlaSegment;
use App\Models\WorkTeam;
use Database\Seeders\OperationalPolicySeeder;
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DashboardTest extends TestCase
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

    public function test_requester_dashboard_is_scoped_to_the_requester_and_shows_ticket_summary(): void
    {
        $requester = $this->createUser([Role::Pemohon], ['name' => 'Pemohon Utama']);
        $otherRequester = $this->createUser([Role::Pemohon], ['name' => 'Pemohon Lain']);

        Ticket::factory()->create([
            'subject' => 'Tiket pemohon bulan berjalan',
            'requester_id' => $requester->id,
            'created_by_id' => $requester->id,
            'status' => TicketStatus::MenungguPemohon,
            'created_at' => Carbon::parse('2026-08-03 09:00:00', 'Asia/Jakarta'),
            'updated_at' => Carbon::parse('2026-08-04 09:00:00', 'Asia/Jakarta'),
        ]);
        Ticket::factory()->create([
            'subject' => 'Tiket pemohon bulan lalu',
            'requester_id' => $requester->id,
            'created_by_id' => $requester->id,
            'status' => TicketStatus::MenungguKonfirmasi,
            'created_at' => Carbon::parse('2026-07-30 09:00:00', 'Asia/Jakarta'),
        ]);
        Ticket::factory()->create([
            'subject' => 'Tiket pemohon sudah ditutup',
            'requester_id' => $requester->id,
            'created_by_id' => $requester->id,
            'status' => TicketStatus::Ditutup,
            'created_at' => Carbon::parse('2026-06-30 09:00:00', 'Asia/Jakarta'),
        ]);
        Ticket::factory()->create([
            'subject' => 'Tiket milik pengguna lain',
            'requester_id' => $otherRequester->id,
            'created_by_id' => $otherRequester->id,
            'created_at' => Carbon::parse('2026-08-03 09:00:00', 'Asia/Jakarta'),
        ]);

        $this->actingAs($requester)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Tata cara pelaporan')
            ->assertSee('Total tiket')
            ->assertDontSee('Tiket pemohon bulan berjalan')
            ->assertDontSee('Tiket pemohon bulan lalu')
            ->assertDontSee('Tiket milik pengguna lain')
            ->assertDontSee('Tiket yang membutuhkan jawaban')
            ->assertViewHas('requesterDashboard', function (array $dashboard): bool {
                return $dashboard['total_ticket_count'] === 3
                    && $dashboard['total_active_ticket_count'] === 1
                    && $dashboard['total_completed_ticket_count'] === 1
                    && $dashboard['total_closed_ticket_count'] === 1;
            });

        $this->actingAs($requester)
            ->get(route('dashboard', ['start_date' => '2026-07-01', 'end_date' => '2026-07-31']))
            ->assertOk()
            ->assertSee('Total tiket')
            ->assertViewHas('requesterDashboard.total_ticket_count', 3);
    }

    public function test_agent_dashboard_shows_queue_responsibility_waiting_and_sla_risk(): void
    {
        $agent = $this->createUser([Role::AgenTier1], ['name' => 'Agen Operasional']);
        $requester = $this->createUser([Role::Pemohon]);
        $service = ServiceType::query()->where('code', 'SVC-01')->firstOrFail();
        $calendar = ServiceCalendar::query()->active()->firstOrFail();

        $queueTicket = Ticket::factory()->create([
            'subject' => 'Tiket baru di antrean',
            'requester_id' => $requester->id,
            'created_by_id' => $requester->id,
            'service_type_id' => $service->id,
            'status' => TicketStatus::Baru,
            'submitted_at' => now()->subHour(),
        ]);
        $waitingTicket = $this->assignedTicket($agent->id, $requester->id, 'Tiket menunggu pemohon', TicketStatus::MenungguPemohon);
        $nearTicket = $this->assignedTicket($agent->id, $requester->id, 'Tiket mendekati SLA', TicketStatus::Dikerjakan);
        $overdueTicket = $this->assignedTicket($agent->id, $requester->id, 'Tiket terlewat SLA', TicketStatus::Dikerjakan);

        TicketSlaSegment::query()->create([
            'ticket_id' => $nearTicket->id,
            'cycle' => 1,
            'state' => TicketSlaSegmentState::Active,
            'reason' => 'ticket_created',
            'sla_policy_id' => $service->activeSlaPolicy()->firstOrFail()->id,
            'target_working_days' => 1,
            'target_working_minutes' => 480,
            'calendar_id' => $calendar->id,
            'calendar_version' => $calendar->version,
            'started_at' => now()->startOfDay()->setTime(8, 0),
        ]);
        TicketSlaSegment::query()->create([
            'ticket_id' => $overdueTicket->id,
            'cycle' => 1,
            'state' => TicketSlaSegmentState::Active,
            'reason' => 'ticket_created',
            'sla_policy_id' => $service->activeSlaPolicy()->firstOrFail()->id,
            'target_working_days' => 1,
            'target_working_minutes' => 480,
            'calendar_id' => $calendar->id,
            'calendar_version' => $calendar->version,
            'started_at' => now()->subDays(2)->setTime(8, 0),
        ]);

        $this->actingAs($agent)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Antrean Baru')
            ->assertSee($queueTicket->subject)
            ->assertSee($waitingTicket->subject)
            ->assertSee('Mendekati SLA')
            ->assertSee('Terlewat SLA')
            ->assertSee($nearTicket->subject)
            ->assertSee($overdueTicket->subject);
    }

    public function test_authorized_overall_dashboard_contains_operational_distributions(): void
    {
        $admin = $this->createUser([Role::SuperAdmin]);
        $agent = $this->createUser([Role::AgenTier1], ['name' => 'Agen Beban']);
        $requester = $this->createUser([Role::Pemohon]);
        $aggregateRequester = $this->createUser([Role::Pemohon]);
        $service = ServiceType::query()->where('code', 'SVC-01')->firstOrFail();
        $category = ProblemCategory::factory()->create(['name' => 'Jaringan Kantor']);

        $ticket = Ticket::factory()->create([
            'subject' => 'Tiket untuk agregasi',
            'requester_id' => $aggregateRequester->id,
            'created_by_id' => $aggregateRequester->id,
            'service_type_id' => $service->id,
            'service_type_name_snapshot' => $service->name,
            'problem_category_id' => $category->id,
            'assigned_to_id' => $agent->id,
            'status' => TicketStatus::Ditutup,
            'reopen_count' => 1,
            'sla_compliant' => true,
            'is_self_created' => true,
        ]);
        TicketPriorityHistory::query()->create([
            'ticket_id' => $ticket->id,
            'from_priority' => 'sedang',
            'to_priority' => 'tinggi',
            'actor_id' => $agent->id,
            'occurred_at' => now()->subHour(),
        ]);
        ApprovalRequest::query()->create([
            'ticket_id' => $ticket->id,
            'approver_id' => $admin->id,
            'requested_by' => $admin->id,
            'status' => ApprovalRequest::STATUS_APPROVED,
            'requested_at' => now()->subHour(),
        ]);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Periode dasbor')
            ->assertDontSee('Dasbor menyeluruh')
            ->assertDontSee('Akses cepat')
            ->assertDontSee('Status akses Anda')
            ->assertDontSee('Gambaran operasional')
            ->assertDontSee('Pusat administrasi');

        $this->actingAs($agent)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Dasbor menyeluruh')
            ->assertSee('Sebaran layanan')
            ->assertSee('Jaringan Kantor')
            ->assertSee('Beban kerja per agen')
            ->assertSee('Agen Beban')
            ->assertSee('Sumber pembuatan tiket')
            ->assertSee('Dibuat mandiri oleh Pemohon')
            ->assertSee('Persetujuan mandiri');

        $this->actingAs($requester)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Dasbor menyeluruh')
            ->assertDontSee('Tiket untuk agregasi');
    }

    public function test_team_chair_sees_only_member_metadata_and_public_updates(): void
    {
        $chair = $this->createUser([Role::KetuaTimKerja], ['name' => 'Ketua Tim']);
        $member = $this->createUser([Role::Pemohon], ['name' => 'Anggota Tim']);
        $outsider = $this->createUser([Role::Pemohon], ['name' => 'Anggota Lain']);
        $team = WorkTeam::factory()->create(['name' => 'Tim Infrastruktur']);
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

        $memberTicket = Ticket::factory()->create([
            'subject' => 'Tiket anggota yang dapat dipantau',
            'requester_id' => $member->id,
            'created_by_id' => $member->id,
            'requester_team_snapshot' => $team->name,
            'solution' => 'Solusi publik untuk anggota.',
        ]);
        $outsiderTicket = Ticket::factory()->create([
            'subject' => 'Tiket milik tim lain',
            'requester_id' => $outsider->id,
            'created_by_id' => $outsider->id,
            'requester_team_snapshot' => 'Tim Lain',
        ]);
        TicketComment::query()->create([
            'ticket_id' => $memberTicket->id,
            'author_id' => $member->id,
            'visibility' => TicketCommentVisibility::Public,
            'body' => 'Balasan publik yang boleh dilihat.',
        ]);
        TicketComment::query()->create([
            'ticket_id' => $memberTicket->id,
            'author_id' => $member->id,
            'visibility' => TicketCommentVisibility::Internal,
            'body' => 'Catatan internal rahasia.',
        ]);

        $this->actingAs($chair)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee($memberTicket->subject)
            ->assertSee('Balasan publik yang boleh dilihat.')
            ->assertDontSee($outsiderTicket->subject)
            ->assertDontSee('Catatan internal rahasia.');

        $this->actingAs($chair)
            ->get(route('tickets.show', $memberTicket))
            ->assertOk()
            ->assertSee('Balasan publik yang boleh dilihat.')
            ->assertDontSee('Catatan internal rahasia.');

        $this->actingAs($chair)
            ->get(route('tickets.show', $outsiderTicket))
            ->assertForbidden();

        $this->actingAs($chair)
            ->get(route('tickets.index'))
            ->assertOk()
            ->assertSee($memberTicket->subject)
            ->assertDontSee($outsiderTicket->subject)
            ->assertSee('Tiket tim');
    }

    private function assignedTicket(int $agentId, int $requesterId, string $subject, TicketStatus $status): Ticket
    {
        $service = ServiceType::query()->where('code', 'SVC-01')->firstOrFail();

        return Ticket::factory()->create([
            'subject' => $subject,
            'requester_id' => $requesterId,
            'created_by_id' => $requesterId,
            'service_type_id' => $service->id,
            'service_type_code_snapshot' => $service->code,
            'service_type_name_snapshot' => $service->name,
            'status' => $status,
            'assigned_to_id' => $agentId,
            'assigned_tier' => Role::AgenTier1->value,
            'submitted_at' => now()->subHour(),
        ]);
    }
}
