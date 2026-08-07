<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Enums\TicketStatus;
use App\Models\Attachment;
use App\Models\AttachmentPolicy;
use App\Models\DatabaseChangeControl;
use App\Models\ServiceType;
use App\Models\Ticket;
use Database\Seeders\OperationalPolicySeeder;
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DatabaseChangeControlTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ServiceCatalogSeeder::class);
        $this->seed(OperationalPolicySeeder::class);
        Carbon::setTestNow(Carbon::parse('2026-08-06 09:00:00', 'Asia/Jakarta'));
        Storage::fake('local');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_svc03_blocks_execution_until_three_valid_evidence_files_and_audit_is_kept(): void
    {
        $requester = $this->createUser([Role::Pemohon]);
        $agent = $this->createUser([Role::AgenTier1]);
        $service = ServiceType::query()->where('code', 'SVC-03')->firstOrFail();
        $ticket = $this->assignedTicket($requester->id, $agent->id, $service, 'CHG-2026-00001');
        $policies = AttachmentPolicy::query()->where('service_type_id', $service->id)->get()->keyBy('type_key');

        $this->actingAs($agent)
            ->post(route('tickets.database-change.execute', $ticket))
            ->assertSessionHasErrors('database_change');

        $this->assertDatabaseHas('database_change_control_histories', [
            'ticket_id' => $ticket->id,
            'action' => 'execution_denied',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $agent->id,
            'action' => 'ticket.database_change.execution_denied',
            'outcome' => 'denied',
        ]);

        $this->upload($ticket, $agent, [
            $policies['change_script']->id => UploadedFile::fake()->create('change.sql', 10, 'text/plain'),
            $policies['rollback_script']->id => UploadedFile::fake()->create('rollback.sql', 10, 'text/plain'),
        ]);

        $this->actingAs($agent)
            ->get(route('tickets.show', $ticket))
            ->assertOk()
            ->assertSee('Kontrol SVC-03')
            ->assertSee('Mulai Eksekusi')
            ->assertSee('change.sql');
        $this->actingAs($requester)
            ->get(route('tickets.show', $ticket))
            ->assertOk()
            ->assertDontSee('Kontrol SVC-03')
            ->assertDontSee('change.sql');

        $this->actingAs($agent)
            ->post(route('tickets.database-change.execute', $ticket))
            ->assertSessionHasErrors('database_change');

        $this->upload($ticket, $agent, [
            $policies['backup_evidence']->id => UploadedFile::fake()->create('backup.txt', 10, 'text/plain'),
        ]);

        $changeAttachment = Attachment::query()->where('ticket_id', $ticket->id)->where('type_key', 'change_script')->firstOrFail();
        $backupAttachment = Attachment::query()->where('ticket_id', $ticket->id)->where('type_key', 'backup_evidence')->firstOrFail();
        $backupPath = $backupAttachment->storage_path;
        $backupAttachment->forceFill(['storage_path' => $changeAttachment->storage_path])->save();

        $this->actingAs($agent)
            ->post(route('tickets.database-change.execute', $ticket))
            ->assertSessionHasErrors('database_change');

        $backupAttachment->forceFill(['storage_path' => $backupPath])->save();

        $this->actingAs($agent)
            ->post(route('tickets.database-change.execute', $ticket))
            ->assertRedirect(route('tickets.show', $ticket))
            ->assertSessionHasNoErrors();

        $control = DatabaseChangeControl::query()->where('ticket_id', $ticket->id)->firstOrFail();
        $this->assertSame($agent->id, $control->execution_started_by_id);
        $this->assertSame('2026-08-06 09:00:00', $control->execution_started_at->toDateTimeString());
        $this->assertDatabaseHas('database_change_control_histories', [
            'ticket_id' => $ticket->id,
            'action' => 'execution_started',
            'actor_id' => $agent->id,
        ]);

        $this->actingAs($agent)
            ->post(route('tickets.database-change.verify', $ticket), [])
            ->assertSessionHasErrors('verification_result');
        $this->assertDatabaseHas('database_change_control_histories', [
            'ticket_id' => $ticket->id,
            'action' => 'verification_denied',
        ]);

        $this->actingAs($agent)
            ->post(route('tickets.database-change.verify', $ticket), [
                'verification_result' => 'Perubahan berhasil dan data sudah sesuai.',
                'verification_notes' => 'Jumlah baris dan checksum hasil pemeriksaan cocok.',
            ])
            ->assertRedirect(route('tickets.show', $ticket))
            ->assertSessionHasNoErrors();

        $control->refresh();
        $this->assertSame($agent->id, $control->verified_by_id);
        $this->assertSame('Perubahan berhasil dan data sudah sesuai.', $control->verification_result);
        $this->assertNotNull($control->verified_at);

        $this->actingAs($agent)
            ->post(route('tickets.complete', $ticket), ['solution' => 'Perubahan diterapkan dan diverifikasi.'])
            ->assertRedirect(route('tickets.show', $ticket))
            ->assertSessionHasNoErrors();

        $this->assertSame(TicketStatus::MenungguKonfirmasi, $ticket->refresh()->status);
    }

    public function test_svc02_requires_requester_accessible_export_before_completion_and_retention_keeps_metadata(): void
    {
        $requester = $this->createUser([Role::Pemohon]);
        $agent = $this->createUser([Role::AgenTier1]);
        $service = ServiceType::query()->where('code', 'SVC-02')->firstOrFail();
        $ticket = $this->assignedTicket($requester->id, $agent->id, $service, 'REQ-2026-00001');
        $policy = AttachmentPolicy::query()
            ->where('service_type_id', $service->id)
            ->where('type_key', 'data_export_result')
            ->firstOrFail();

        $this->actingAs($agent)
            ->post(route('tickets.complete', $ticket), ['solution' => 'Hasil data sedang disiapkan.'])
            ->assertSessionHasErrors('ticket');
        $this->assertSame(TicketStatus::Dikerjakan, $ticket->refresh()->status);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $agent->id,
            'action' => 'ticket.resolution',
            'outcome' => 'denied',
        ]);

        $this->upload($ticket, $agent, [
            $policy->id => UploadedFile::fake()->create('hasil-ekspor.csv', 10, 'text/csv'),
        ]);

        $attachment = Attachment::query()->where('ticket_id', $ticket->id)->firstOrFail();
        $this->assertSame('data_export_result', $attachment->type_key);
        $this->assertSame('both', $attachment->visibility);

        $this->actingAs($agent)
            ->post(route('tickets.complete', $ticket), ['solution' => 'Hasil tarik data tersedia untuk Pemohon.'])
            ->assertRedirect(route('tickets.show', $ticket))
            ->assertSessionHasNoErrors();

        $ticket->forceFill(['status' => TicketStatus::Ditutup, 'closed_at' => now()->subDays(91)])->save();
        $storagePath = $attachment->storage_path;
        Storage::disk('local')->assertExists($storagePath);

        $this->artisan('sihati:attachments:purge-data-exports')
            ->expectsOutput('1 lampiran hasil tarik data dihapus.')
            ->assertExitCode(0);

        $this->assertSoftDeleted('attachments', ['id' => $attachment->id]);
        Storage::disk('local')->assertMissing($storagePath);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'attachment.retention_deleted',
            'outcome' => 'succeeded',
            'auditable_id' => $attachment->id,
        ]);

        $this->artisan('sihati:attachments:purge-data-exports')
            ->expectsOutput('0 lampiran hasil tarik data dihapus.')
            ->assertExitCode(0);
    }

    /** @param array<int, UploadedFile> $files */
    private function upload(Ticket $ticket, mixed $agent, array $files): void
    {
        $payload = ['attachments' => []];

        foreach ($files as $policyId => $file) {
            $payload['attachments'][$policyId] = [$file];
        }

        $this->actingAs($agent)
            ->post(route('tickets.attachments.store', $ticket), $payload)
            ->assertRedirect(route('tickets.show', $ticket))
            ->assertSessionHasNoErrors();
    }

    private function assignedTicket(int $requesterId, int $agentId, ServiceType $service, string $number): Ticket
    {
        return Ticket::factory()->create([
            'ticket_number' => $number,
            'ticket_class' => $service->ticket_class,
            'ticket_year' => 2026,
            'ticket_sequence' => random_int(1, 9000),
            'requester_id' => $requesterId,
            'created_by_id' => $requesterId,
            'service_type_id' => $service->id,
            'service_type_code_snapshot' => $service->code,
            'service_type_name_snapshot' => $service->name,
            'status' => TicketStatus::Dikerjakan,
            'assigned_to_id' => $agentId,
            'assigned_tier' => Role::AgenTier1->value,
            'submitted_at' => now()->subHour(),
        ]);
    }
}
