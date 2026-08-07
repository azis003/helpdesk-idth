<?php

namespace Tests\Feature;

use App\Enums\Priority;
use App\Enums\Role;
use App\Models\ProblemCategory;
use App\Models\ServiceType;
use App\Models\Ticket;
use App\Services\TicketNotificationService;
use App\Services\TicketWorkflowService;
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TicketNotificationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ServiceCatalogSeeder::class);
    }

    public function test_ticket_events_use_database_payload_and_skip_inactive_recipients(): void
    {
        $requester = $this->createUser([Role::Pemohon]);
        $inactive = $this->createUser([Role::Pemohon], ['is_active' => false]);
        $ticket = Ticket::factory()->create([
            'ticket_number' => 'INC-2026-00017',
            'requester_id' => $requester->id,
            'created_by_id' => $requester->id,
        ]);

        app(TicketNotificationService::class)->send(
            $ticket,
            'ticket_created',
            'Tiket berhasil dibuat',
            'Tiket berhasil dibuat dan masuk antrean Tier 1.',
            [$requester->id, $inactive->id],
            'ticket:17:created',
        );

        $notification = $requester->notifications()->firstOrFail();

        $this->assertSame('ticket_created', $notification->data['event']);
        $this->assertSame('Tiket berhasil dibuat', $notification->data['title']);
        $this->assertSame($ticket->id, $notification->data['ticket_id']);
        $this->assertSame($ticket->ticket_number, $notification->data['ticket_number']);
        $this->assertSame(route('tickets.show', $ticket), $notification->data['url']);
        $this->assertDatabaseMissing('notifications', ['notifiable_id' => $inactive->id]);
    }

    public function test_claim_and_triage_events_notify_the_requester_and_target_assignee(): void
    {
        $requester = $this->createUser([Role::Pemohon]);
        $agent = $this->createUser([Role::AgenTier1], ['name' => 'Agen Tier 1']);
        $tierTwo = $this->createUser([Role::AgenTier2], ['name' => 'Agen Tier 2']);
        $service = ServiceType::query()->where('code', 'SVC-01')->firstOrFail();
        $category = ProblemCategory::factory()->create([
            'name' => 'Konektivitas',
            'slug' => 'konektivitas',
        ]);

        $ticket = Ticket::factory()->create([
            'ticket_number' => 'INC-2026-00018',
            'requester_id' => $requester->id,
            'created_by_id' => $requester->id,
            'service_type_id' => $service->id,
        ]);

        $this->assertTrue(app(TicketWorkflowService::class)->claim($agent, $ticket));
        $this->assertContains('ticket_claimed', $this->eventsFor($requester));

        app(TicketWorkflowService::class)->triage($agent, $ticket->fresh(), [
            'outcome' => 'tier_2',
            'priority' => Priority::Sedang->value,
            'priority_reason' => 'Prioritas dikonfirmasi saat triase.',
            'problem_category_id' => $category->id,
            'category_reason' => 'Kategori sesuai hasil triase.',
            'assigned_to_id' => $tierTwo->id,
        ]);

        $this->assertContains('ticket_escalated', $this->eventsFor($requester));
        $this->assertContains('ticket_escalated', $this->eventsFor($tierTwo));
    }

    public function test_rollback_discards_notifications_and_same_event_key_is_idempotent(): void
    {
        $requester = $this->createUser([Role::Pemohon]);
        $ticket = Ticket::factory()->create([
            'ticket_number' => 'INC-2026-00019',
            'requester_id' => $requester->id,
            'created_by_id' => $requester->id,
        ]);

        DB::beginTransaction();
        app(TicketNotificationService::class)->send(
            $ticket,
            'ticket_cancelled',
            'Tiket dibatalkan',
            'Tiket dibatalkan.',
            [$requester->id],
            'ticket:19:cancelled',
        );
        DB::rollBack();

        $this->assertDatabaseCount('notifications', 0);

        $service = app(TicketNotificationService::class);
        $service->send($ticket, 'ticket_cancelled', 'Tiket dibatalkan', 'Tiket dibatalkan.', [$requester->id], 'ticket:19:cancelled');
        $service->send($ticket, 'ticket_cancelled', 'Tiket dibatalkan', 'Tiket dibatalkan.', [$requester->id], 'ticket:19:cancelled');

        $this->assertDatabaseCount('notifications', 1);
    }

    /** @return list<string> */
    private function eventsFor(object $user): array
    {
        return $user->notifications()
            ->get()
            ->map(fn ($notification): string => (string) ($notification->data['event'] ?? ''))
            ->all();
    }
}
