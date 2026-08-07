<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Enums\TicketStatus;
use App\Models\Attachment;
use App\Models\Ticket;
use App\Services\ApplicationLogRetentionService;
use App\Services\AuditLogger;
use App\Services\TicketRetentionService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class AuditAndRetentionTest extends TestCase
{
    public function test_audit_has_request_context_and_redacts_secret_fields(): void
    {
        $actor = $this->createUser([Role::SuperAdmin]);
        $request = Request::create('/admin/audit-logs', 'POST');
        $request->headers->set('X-Request-ID', 'request-test-13');

        $log = app(AuditLogger::class)->record(
            $actor,
            'test.context',
            'succeeded',
            null,
            'Pengujian audit',
            ['password' => 'jangan simpan', 'status' => 'baru'],
            ['api_token' => 'jangan simpan', 'status' => 'diproses'],
            $request,
            ['source' => 'test', 'secret_value' => 'jangan simpan'],
        );

        $this->assertSame('request-test-13', $log->request_id);
        $this->assertSame('Asia/Jakarta', $log->context['timezone']);
        $this->assertSame('[REDACTED]', $log->before['password']);
        $this->assertSame('[REDACTED]', $log->after['api_token']);
        $this->assertSame('[REDACTED]', $log->context['secret_value']);
        $this->assertSame('test', $log->context['source']);
        $this->assertInstanceOf(Carbon::class, $log->created_at);
    }

    public function test_denied_audit_is_kept_when_domain_transaction_rolls_back(): void
    {
        $this->expectException(RuntimeException::class);

        try {
            DB::transaction(function (): void {
                app(AuditLogger::class)->denied(null, 'test.rollback.denied', null, 'Precondition gagal.');

                throw new RuntimeException('rollback');
            });
        } catch (RuntimeException $exception) {
            $this->assertDatabaseHas('audit_logs', [
                'action' => 'test.rollback.denied',
                'outcome' => 'denied',
            ]);

            throw $exception;
        }
    }

    public function test_ticket_retention_removes_terminal_ticket_and_private_files(): void
    {
        Storage::fake('local');
        $now = Carbon::parse('2031-08-07 09:00:00', 'Asia/Jakarta');
        $ticket = Ticket::factory()->create([
            'ticket_number' => 'INC-2026-00013',
            'status' => TicketStatus::Ditutup,
            'closed_at' => $now->copy()->subYears(5)->subDay(),
            'updated_at' => $now->copy()->subYears(5)->subDay(),
        ]);
        $path = "tickets/{$ticket->id}/old.pdf";
        Storage::disk('local')->put($path, 'private');
        $attachment = Attachment::query()->create([
            'ticket_id' => $ticket->id,
            'type_key' => 'supporting',
            'type_label_snapshot' => 'Dokumen pendukung',
            'original_name' => 'old.pdf',
            'storage_disk' => 'local',
            'storage_path' => $path,
            'size_bytes' => 7,
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'visibility' => 'internal',
        ]);

        $this->assertSame(1, app(TicketRetentionService::class)->purgeExpiredTickets($now));
        $this->assertDatabaseMissing('tickets', ['id' => $ticket->id]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'ticket.retention_deleted',
            'auditable_id' => $ticket->id,
        ]);
        Storage::disk('local')->assertMissing($path);
        $this->assertDatabaseMissing('attachments', ['id' => $attachment->id]);
    }

    public function test_application_log_retention_is_idempotent_and_keeps_recent_files(): void
    {
        $directory = storage_path('framework/testing/audit-retention-logs');
        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        file_put_contents($directory.'/laravel-2031-06-01.log', 'old');
        file_put_contents($directory.'/laravel-2031-07-20.log', 'recent');

        $now = Carbon::parse('2031-08-07 09:00:00', 'Asia/Jakarta');
        $service = app(ApplicationLogRetentionService::class);

        $this->assertSame(1, $service->purge($now, $directory));
        $this->assertSame(0, $service->purge($now, $directory));
        $this->assertFileDoesNotExist($directory.'/laravel-2031-06-01.log');
        $this->assertFileExists($directory.'/laravel-2031-07-20.log');

        @unlink($directory.'/laravel-2031-07-20.log');
        @rmdir($directory);
    }
}
