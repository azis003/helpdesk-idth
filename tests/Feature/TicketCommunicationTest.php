<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Enums\TicketCommentVisibility;
use App\Enums\TicketSlaSegmentState;
use App\Enums\TicketStatus;
use App\Enums\TicketWaitEndReason;
use App\Enums\TicketWaitType;
use App\Models\Attachment;
use App\Models\AttachmentPolicy;
use App\Models\ServiceType;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\TicketSlaSegment;
use App\Models\TicketWait;
use App\Notifications\TicketEventNotification;
use Database\Seeders\OperationalPolicySeeder;
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TicketCommunicationTest extends TestCase
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

    public function test_agent_can_keep_public_and_internal_comments_separate_with_attachment_access(): void
    {
        Storage::fake('local');

        $requester = $this->createUser([Role::Pemohon]);
        $agent = $this->createUser([Role::AgenTier1]);
        $service = ServiceType::query()->where('code', 'SVC-01')->firstOrFail();
        $publicPolicy = $this->createAttachmentPolicy($service, 'public-evidence', 'Bukti publik', 'both');
        $internalPolicy = $this->createAttachmentPolicy($service, 'internal-evidence', 'Bukti internal', 'internal');
        $ticket = $this->assignedTicket($requester->id, $agent->id, $service->id);

        $this->actingAs($agent)
            ->post(route('tickets.comments.public', $ticket), [
                'body' => 'Langkah perbaikan sudah dilakukan dan sedang diverifikasi.',
                'attachments' => [$publicPolicy->id => [UploadedFile::fake()->create('hasil-uji.pdf', 10, 'application/pdf')]],
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->actingAs($agent)
            ->post(route('tickets.comments.internal', $ticket), [
                'body' => 'Catatan vendor: cek konfigurasi port uplink.',
                'attachments' => [$internalPolicy->id => [UploadedFile::fake()->create('log-internal.pdf', 10, 'application/pdf')]],
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('ticket_comments', [
            'ticket_id' => $ticket->id,
            'visibility' => TicketCommentVisibility::Public->value,
            'body' => 'Langkah perbaikan sudah dilakukan dan sedang diverifikasi.',
        ]);
        $this->assertDatabaseHas('ticket_comments', [
            'ticket_id' => $ticket->id,
            'visibility' => TicketCommentVisibility::Internal->value,
            'body' => 'Catatan vendor: cek konfigurasi port uplink.',
        ]);
        $this->assertSame(2, TicketComment::query()->where('ticket_id', $ticket->id)->count());
        $this->assertSame(2, Attachment::query()->where('ticket_id', $ticket->id)->count());

        $this->actingAs($requester)
            ->get(route('tickets.show', $ticket))
            ->assertOk()
            ->assertSee('Langkah perbaikan sudah dilakukan dan sedang diverifikasi.')
            ->assertDontSee('Catatan vendor: cek konfigurasi port uplink.');

        $this->actingAs($agent)
            ->get(route('tickets.show', $ticket))
            ->assertOk()
            ->assertSee('ticket-reference-action-menu')
            ->assertSee('Tindakan')
            ->assertSee('Tambahkan Balasan')
            ->assertSee('ticket-reference-reply')
            ->assertSee('Balasan ke Pemohon')
            ->assertSee('Catatan Internal')
            ->assertSee('Langkah perbaikan sudah dilakukan dan sedang diverifikasi.')
            ->assertSee('Catatan vendor: cek konfigurasi port uplink.');

        $publicAttachment = Attachment::query()->where('visibility', 'both')->firstOrFail();
        $internalAttachment = Attachment::query()->where('visibility', 'internal')->firstOrFail();

        $this->actingAs($requester)
            ->get(route('attachments.download', $publicAttachment))
            ->assertOk();
        $this->actingAs($requester)
            ->get(route('attachments.download', $internalAttachment))
            ->assertForbidden();
        $this->actingAs($agent)
            ->get(route('attachments.download', $internalAttachment))
            ->assertOk();

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $requester->id,
            'type' => TicketEventNotification::class,
        ]);
        $this->assertDatabaseCount('notifications', 1);

        $notification = $requester->unreadNotifications()->firstOrFail();
        $this->actingAs($requester)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Balasan baru pada tiket');
        $this->actingAs($requester)
            ->post(route('notifications.read', $notification->id))
            ->assertRedirect(route('tickets.show', $ticket));
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_helpdesk_public_comment_stays_enabled_for_visible_ticket_until_final_status(): void
    {
        $requester = $this->createUser([Role::Pemohon]);
        $helpdesk = $this->createUser([Role::AgenTier1]);
        $otherAgent = $this->createUser([Role::AgenTier1]);
        $service = ServiceType::query()->where('code', 'SVC-01')->firstOrFail();
        $ticket = $this->assignedTicket($requester->id, $otherAgent->id, $service->id);

        $activeStatuses = [
            TicketStatus::Baru,
            TicketStatus::Diproses,
            TicketStatus::Dikerjakan,
            TicketStatus::MenungguPersetujuan,
            TicketStatus::MenungguPemohon,
            TicketStatus::MenungguPihakKetiga,
            TicketStatus::MenungguKonfirmasi,
        ];

        foreach ($activeStatuses as $status) {
            $ticket->forceFill([
                'status' => $status,
                'assigned_to_id' => $status === TicketStatus::Baru ? null : $otherAgent->id,
                'assigned_tier' => $status === TicketStatus::Baru ? null : Role::AgenTier1->value,
            ])->save();

            $body = "Balasan Helpdesk pada status {$status->label()}.";

            $this->actingAs($helpdesk)
                ->get(route('tickets.show', $ticket))
                ->assertOk()
                ->assertSee('Balasan ke Pemohon')
                ->assertSee('Kirim Pesan');

            $this->actingAs($helpdesk)
                ->post(route('tickets.comments.public', $ticket), ['body' => $body])
                ->assertRedirect()
                ->assertSessionHasNoErrors();

            $this->assertDatabaseHas('ticket_comments', [
                'ticket_id' => $ticket->id,
                'author_id' => $helpdesk->id,
                'visibility' => TicketCommentVisibility::Public->value,
                'body' => $body,
            ]);
        }

        foreach (TicketStatus::closedCases() as $status) {
            $ticket->forceFill(['status' => $status])->save();

            $this->actingAs($helpdesk)
                ->get(route('tickets.show', $ticket))
                ->assertOk()
                ->assertDontSee('Kirim Pesan');

            $this->actingAs($helpdesk)
                ->post(route('tickets.comments.public', $ticket), [
                    'body' => "Komentar setelah {$status->label()}.",
                ])
                ->assertForbidden();
        }
    }

    public function test_requester_wait_reply_restores_assignee_and_resumes_sla(): void
    {
        $requester = $this->createUser([Role::Pemohon]);
        $agent = $this->createUser([Role::AgenTier1]);
        $service = ServiceType::query()->where('code', 'SVC-01')->firstOrFail();
        $ticket = $this->assignedTicket($requester->id, $agent->id, $service->id);

        $this->actingAs($agent)
            ->post(route('tickets.request-information', $ticket), [
                'body' => 'Mohon kirimkan tangkapan layar pesan error.',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $ticket->refresh();
        $wait = TicketWait::query()->where('ticket_id', $ticket->id)->firstOrFail();
        $this->assertSame(TicketStatus::MenungguPemohon, $ticket->status);
        $this->assertSame(TicketWaitType::Requester, $wait->kind);
        $this->assertSame(Carbon::parse('2026-08-11 16:00:00', 'Asia/Jakarta')->toDateTimeString(), $wait->due_at->toDateTimeString());
        $this->assertDatabaseHas('ticket_sla_segments', [
            'ticket_id' => $ticket->id,
            'state' => TicketSlaSegmentState::Paused->value,
            'reason' => 'requester_wait',
        ]);

        $this->actingAs($requester)
            ->post(route('tickets.requester-reply', $ticket), [
                'body' => 'Berikut tangkapan layar pesan error yang diminta.',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $ticket->refresh();
        $wait->refresh();
        $this->assertSame(TicketStatus::Dikerjakan, $ticket->status);
        $this->assertSame($agent->id, $ticket->assigned_to_id);
        $this->assertSame(TicketWaitEndReason::RequesterReplied, $wait->end_reason);
        $this->assertNotNull($wait->ended_at);
        $this->assertSame(2, TicketSlaSegment::query()->where('ticket_id', $ticket->id)->where('state', TicketSlaSegmentState::Active->value)->count());
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $agent->id,
            'type' => TicketEventNotification::class,
        ]);

        $ticket->forceFill([
            'status' => TicketStatus::Ditutup,
            'closed_at' => now(),
        ])->save();

        $this->actingAs($requester)
            ->get(route('tickets.show', $ticket))
            ->assertOk()
            ->assertDontSee('Kirim Pesan')
            ->assertSee('Balasan tidak tersedia karena tiket sudah berstatus akhir.');

        $this->actingAs($requester)
            ->post(route('tickets.requester-reply', $ticket), [
                'body' => 'Pesan setelah tiket ditutup.',
            ])
            ->assertForbidden();
    }

    public function test_requester_can_send_follow_up_messages_until_ticket_is_closed(): void
    {
        $requester = $this->createUser([Role::Pemohon]);
        $agent = $this->createUser([Role::AgenTier1]);
        $service = ServiceType::query()->where('code', 'SVC-01')->firstOrFail();
        $ticket = $this->assignedTicket($requester->id, $agent->id, $service->id);

        $this->actingAs($requester)
            ->get(route('tickets.show', $ticket))
            ->assertOk()
            ->assertSee('Kirim Pesan')
            ->assertSee('Tambahkan Balasan')
            ->assertDontSee('Balas informasi')
            ->assertDontSee('Balasan tidak tersedia karena tiket sudah berstatus akhir.');

        $this->actingAs($requester)
            ->post(route('tickets.requester-reply', $ticket), [
                'body' => 'Saya menambahkan informasi lanjutan untuk pemeriksaan tiket.',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Komentar berhasil ditambahkan.');

        $ticket->refresh();
        $this->assertSame(TicketStatus::Dikerjakan, $ticket->status);
        $this->assertDatabaseHas('ticket_comments', [
            'ticket_id' => $ticket->id,
            'author_id' => $requester->id,
            'visibility' => TicketCommentVisibility::Public->value,
            'body' => 'Saya menambahkan informasi lanjutan untuk pemeriksaan tiket.',
        ]);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $agent->id,
            'type' => TicketEventNotification::class,
        ]);
    }

    public function test_scheduler_expires_requester_wait_idempotently_and_marks_timeout(): void
    {
        $requester = $this->createUser([Role::Pemohon]);
        $agent = $this->createUser([Role::AgenTier1]);
        $service = ServiceType::query()->where('code', 'SVC-01')->firstOrFail();
        $ticket = $this->assignedTicket($requester->id, $agent->id, $service->id, TicketStatus::MenungguPemohon);
        $wait = TicketWait::query()->create([
            'ticket_id' => $ticket->id,
            'kind' => TicketWaitType::Requester,
            'started_by_id' => $agent->id,
            'from_status' => TicketStatus::Dikerjakan,
            'from_assignee_id' => $agent->id,
            'from_assigned_tier' => Role::AgenTier1->value,
            'started_at' => now()->subDays(4),
            'due_at' => now()->subMinute(),
        ]);

        $this->artisan('sihati:tickets:expire-requester-waits')
            ->expectsOutput('1 waktu tunggu Pemohon diakhiri.')
            ->assertExitCode(0);

        $ticket->refresh();
        $wait->refresh();
        $this->assertSame(TicketStatus::Dikerjakan, $ticket->status);
        $this->assertTrue($wait->timed_out);
        $this->assertSame(TicketWaitEndReason::Timeout, $wait->end_reason);
        $this->assertDatabaseHas('ticket_status_histories', [
            'ticket_id' => $ticket->id,
            'action' => 'ticket.wait.requester.timeout',
            'actor_id' => null,
        ]);

        $this->artisan('sihati:tickets:expire-requester-waits')
            ->expectsOutput('0 waktu tunggu Pemohon diakhiri.')
            ->assertExitCode(0);
        $this->assertDatabaseCount('notifications', 1);
    }

    public function test_agent_can_pause_for_third_party_and_resume_with_history(): void
    {
        $requester = $this->createUser([Role::Pemohon]);
        $agent = $this->createUser([Role::AgenTier1]);
        $service = ServiceType::query()->where('code', 'SVC-01')->firstOrFail();
        $ticket = $this->assignedTicket($requester->id, $agent->id, $service->id);

        $this->actingAs($agent)
            ->post(route('tickets.wait-third-party', $ticket), [
                'third_party_name' => 'Vendor jaringan',
                'follow_up_date' => '2026-08-10',
                'note' => 'Menunggu hasil pengecekan port.',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $ticket->refresh();
        $wait = TicketWait::query()->where('ticket_id', $ticket->id)->firstOrFail();
        $this->assertSame(TicketStatus::MenungguPihakKetiga, $ticket->status);
        $this->assertSame(TicketWaitType::ThirdParty, $wait->kind);
        $this->assertSame('Vendor jaringan', $wait->third_party_name);
        $this->assertSame('2026-08-10', $wait->follow_up_date->toDateString());

        $this->actingAs($agent)
            ->post(route('tickets.resume-third-party', $ticket), [
                'reason' => 'Vendor mengonfirmasi konfigurasi sudah diperbaiki.',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $ticket->refresh();
        $wait->refresh();
        $this->assertSame(TicketStatus::Dikerjakan, $ticket->status);
        $this->assertSame(TicketWaitEndReason::ThirdPartyCompleted, $wait->end_reason);
        $this->assertNotNull($wait->ended_at);
        $this->assertDatabaseHas('ticket_status_histories', [
            'ticket_id' => $ticket->id,
            'action' => 'ticket.wait.third_party',
        ]);
        $this->assertDatabaseHas('ticket_status_histories', [
            'ticket_id' => $ticket->id,
            'action' => 'ticket.resume.third_party',
        ]);
        $this->assertSame(2, TicketSlaSegment::query()->where('ticket_id', $ticket->id)->where('state', TicketSlaSegmentState::Active->value)->count());
    }

    private function assignedTicket(int $requesterId, int $agentId, int $serviceTypeId, TicketStatus $status = TicketStatus::Dikerjakan): Ticket
    {
        return Ticket::factory()->create([
            'ticket_number' => 'INC-2026-'.fake()->unique()->numerify('#####'),
            'requester_id' => $requesterId,
            'created_by_id' => $requesterId,
            'service_type_id' => $serviceTypeId,
            'service_type_code_snapshot' => 'SVC-01',
            'service_type_name_snapshot' => 'Kendala jaringan atau konektivitas',
            'status' => $status,
            'assigned_to_id' => $agentId,
            'assigned_tier' => Role::AgenTier1->value,
            'submitted_at' => now()->subHour(),
        ]);
    }

    private function createAttachmentPolicy(ServiceType $service, string $typeKey, string $label, string $visibility): AttachmentPolicy
    {
        return AttachmentPolicy::query()->create([
            'service_type_id' => $service->id,
            'type_key' => $typeKey,
            'label' => $label,
            'max_file_size_kb' => 100,
            'max_file_count' => 2,
            'allowed_mimes' => ['application/pdf'],
            'allowed_extensions' => ['pdf'],
            'visibility' => $visibility,
            'is_active' => true,
        ]);
    }
}
