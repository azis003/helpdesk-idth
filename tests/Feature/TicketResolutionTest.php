<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Enums\TicketSlaSegmentState;
use App\Enums\TicketStatus;
use App\Models\ServiceCalendar;
use App\Models\ServiceCalendarHoliday;
use App\Models\ServiceType;
use App\Models\Ticket;
use App\Models\TicketSlaSegment;
use App\Notifications\TicketEventNotification;
use App\Services\TicketSlaService;
use Database\Seeders\OperationalPolicySeeder;
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TicketResolutionTest extends TestCase
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

    public function test_sla_counts_only_active_service_minutes_and_keeps_calendar_snapshot(): void
    {
        $requester = $this->createUser([Role::Pemohon]);
        $agent = $this->createUser([Role::AgenTier1]);
        $service = ServiceType::query()->where('code', 'SVC-01')->firstOrFail();
        $calendar = ServiceCalendar::query()->active()->firstOrFail();
        ServiceCalendarHoliday::query()->create([
            'service_calendar_id' => $calendar->id,
            'holiday_date' => '2026-08-07',
            'name' => 'Hari libur pengujian',
        ]);
        $submittedAt = Carbon::parse('2026-08-06 15:00:00', 'Asia/Jakarta');
        $ticket = $this->assignedTicket($requester->id, $agent->id, $service->id, $submittedAt);

        app(TicketSlaService::class)->start($ticket, $submittedAt);
        Carbon::setTestNow(Carbon::parse('2026-08-10 09:00:00', 'Asia/Jakarta'));

        $metrics = app(TicketSlaService::class)->metrics($ticket->fresh());

        $this->assertNotNull($metrics);
        $this->assertSame(480, $metrics['target_working_minutes']);
        $this->assertSame(120, $metrics['elapsed_working_minutes']);
        $this->assertSame(360, $metrics['remaining_minutes']);
        $this->assertFalse($metrics['overdue']);
        $this->assertSame($calendar->id, $metrics['calendar_id']);
        $this->assertSame($calendar->version, $metrics['calendar_version']);
    }

    public function test_agent_can_complete_requester_can_report_not_satisfied_confirm_and_reopen(): void
    {
        $requester = $this->createUser([Role::Pemohon]);
        $agent = $this->createUser([Role::AgenTier1]);
        $service = ServiceType::query()->where('code', 'SVC-01')->firstOrFail();
        $ticket = $this->assignedTicket($requester->id, $agent->id, $service->id, now()->subHour());

        $this->actingAs($agent)
            ->post(route('tickets.complete', $ticket), ['solution' => 'Konfigurasi jaringan diperbaiki dan diuji.'])
            ->assertRedirect(route('tickets.show', $ticket))
            ->assertSessionHasNoErrors();

        $ticket->refresh();
        $this->assertSame(TicketStatus::MenungguKonfirmasi, $ticket->status);
        $this->assertSame('Konfigurasi jaringan diperbaiki dan diuji.', $ticket->solution);
        $this->assertNotNull($ticket->confirmation_due_at);
        $this->assertDatabaseHas('ticket_sla_segments', [
            'ticket_id' => $ticket->id,
            'state' => TicketSlaSegmentState::Paused->value,
            'reason' => 'confirmation',
        ]);
        $this->assertDatabaseHas('ticket_status_histories', [
            'ticket_id' => $ticket->id,
            'action' => 'ticket.completed',
        ]);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $requester->id,
            'type' => TicketEventNotification::class,
        ]);

        $this->actingAs($requester)
            ->post(route('tickets.not-satisfied', $ticket), ['reason' => 'Koneksi masih terputus di ruang rapat.'])
            ->assertRedirect(route('tickets.show', $ticket))
            ->assertSessionHasNoErrors();

        $ticket->refresh();
        $this->assertSame(TicketStatus::Dikerjakan, $ticket->status);
        $this->assertSame(0, $ticket->reopen_count);
        $this->assertDatabaseHas('ticket_status_histories', [
            'ticket_id' => $ticket->id,
            'action' => 'ticket.confirmation.not_satisfied',
            'reason' => 'Koneksi masih terputus di ruang rapat.',
        ]);

        $this->actingAs($agent)
            ->post(route('tickets.complete', $ticket), ['solution' => 'Port ruang rapat dikonfigurasi ulang dan diuji.'])
            ->assertRedirect(route('tickets.show', $ticket));

        $this->actingAs($requester)
            ->post(route('tickets.confirm', $ticket))
            ->assertRedirect(route('tickets.show', $ticket))
            ->assertSessionHasNoErrors();

        $ticket->refresh();
        $this->assertSame(TicketStatus::Ditutup, $ticket->status);
        $this->assertSame('requester_confirmed', $ticket->closed_reason);
        $this->assertNotNull($ticket->closed_at);
        $this->assertDatabaseHas('ticket_status_histories', [
            'ticket_id' => $ticket->id,
            'action' => 'ticket.closed',
            'to_status' => TicketStatus::Ditutup->value,
        ]);

        $this->actingAs($requester)
            ->post(route('tickets.reopen', $ticket), ['reason' => 'Masih perlu pemantauan selama satu hari.'])
            ->assertRedirect(route('tickets.show', $ticket))
            ->assertSessionHasNoErrors();

        $ticket->refresh();
        $this->assertSame(TicketStatus::Dikerjakan, $ticket->status);
        $this->assertSame(1, $ticket->reopen_count);
        $this->assertSame(2, $ticket->sla_cycle);
        $this->assertSame('INC-2026-'.substr($ticket->ticket_number, 9), $ticket->ticket_number);
        $this->assertSame(2, TicketSlaSegment::query()->where('ticket_id', $ticket->id)->max('cycle'));
        $this->assertDatabaseHas('ticket_status_histories', [
            'ticket_id' => $ticket->id,
            'action' => 'ticket.reopened',
        ]);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $agent->id,
            'type' => TicketEventNotification::class,
        ]);
    }

    public function test_scheduler_auto_closes_confirmation_wait_and_is_idempotent(): void
    {
        $requester = $this->createUser([Role::Pemohon]);
        $agent = $this->createUser([Role::AgenTier1]);
        $service = ServiceType::query()->where('code', 'SVC-01')->firstOrFail();
        $ticket = $this->assignedTicket($requester->id, $agent->id, $service->id, now()->subDay());
        $ticket->forceFill([
            'status' => TicketStatus::MenungguKonfirmasi,
            'solution' => 'Solusi telah diterapkan.',
            'confirmation_started_at' => now()->subDays(4),
            'confirmation_due_at' => now()->subMinute(),
        ])->save();

        $this->artisan('sihati:tickets:auto-close-confirmations')
            ->expectsOutput('1 tiket ditutup otomatis.')
            ->assertExitCode(0);

        $ticket->refresh();
        $this->assertSame(TicketStatus::Ditutup, $ticket->status);
        $this->assertSame('auto_closed', $ticket->closed_reason);
        $this->assertDatabaseHas('ticket_status_histories', [
            'ticket_id' => $ticket->id,
            'action' => 'ticket.auto_closed',
        ]);

        $this->artisan('sihati:tickets:auto-close-confirmations')
            ->expectsOutput('0 tiket ditutup otomatis.')
            ->assertExitCode(0);
        $this->assertDatabaseCount('notifications', 2);
    }

    private function assignedTicket(int $requesterId, int $agentId, int $serviceTypeId, Carbon $submittedAt): Ticket
    {
        return Ticket::factory()->create([
            'ticket_number' => 'INC-2026-'.fake()->unique()->numerify('#####'),
            'requester_id' => $requesterId,
            'created_by_id' => $requesterId,
            'service_type_id' => $serviceTypeId,
            'service_type_code_snapshot' => 'SVC-01',
            'service_type_name_snapshot' => 'Kendala jaringan atau konektivitas',
            'status' => TicketStatus::Dikerjakan,
            'assigned_to_id' => $agentId,
            'assigned_tier' => Role::AgenTier1->value,
            'submitted_at' => $submittedAt,
        ]);
    }
}
