<?php

namespace Tests\Feature;

use App\Enums\Priority;
use App\Enums\Role;
use App\Enums\TicketStatus;
use App\Models\ApproverAssignment;
use App\Models\ProblemCategory;
use App\Models\ServiceType;
use App\Models\Ticket;
use App\Models\TicketAssignmentHistory;
use App\Models\TicketCategoryHistory;
use App\Models\TicketStatusHistory;
use Database\Seeders\OperationalPolicySeeder;
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class MonthlyReportTest extends TestCase
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

    public function test_authorized_user_sees_monthly_report_with_historical_values(): void
    {
        $admin = $this->createUser([Role::SuperAdmin]);
        $requester = $this->createUser([Role::Pemohon], [
            'name' => 'Nama Sekarang',
            'nip' => 'NIP-SEKARANG',
        ]);
        $agent = $this->createUser([Role::AgenTier1], ['name' => 'Penanggung Jawab']);
        $service = ServiceType::query()->where('code', 'SVC-01')->firstOrFail();
        $category = ProblemCategory::factory()->create(['name' => 'Kategori Sekarang']);
        $ticket = $this->createTicket($requester->id, $agent->id, $service, $category);

        TicketCategoryHistory::query()->create([
            'ticket_id' => $ticket->id,
            'from_category_id' => null,
            'to_category_id' => $category->id,
            'from_category_name' => null,
            'to_category_name' => 'Kategori Saat Tiket Dibuat',
            'actor_id' => $agent->id,
            'reason' => 'Kategori awal.',
            'occurred_at' => Carbon::parse('2026-08-01 08:01:00', 'Asia/Jakarta'),
        ]);
        TicketAssignmentHistory::query()->create([
            'ticket_id' => $ticket->id,
            'from_user_id' => null,
            'to_user_id' => $agent->id,
            'from_tier' => null,
            'to_tier' => Role::AgenTier1->value,
            'action' => 'claimed',
            'actor_id' => $agent->id,
            'occurred_at' => Carbon::parse('2026-08-01 08:02:00', 'Asia/Jakarta'),
        ]);
        TicketStatusHistory::query()->create([
            'ticket_id' => $ticket->id,
            'from_status' => TicketStatus::Dikerjakan->value,
            'to_status' => TicketStatus::MenungguKonfirmasi->value,
            'action' => 'ticket.completed',
            'actor_id' => $agent->id,
            'occurred_at' => Carbon::parse('2026-08-02 10:00:00', 'Asia/Jakarta'),
        ]);

        $requester->forceFill(['name' => 'Nama Master Terbaru', 'nip' => 'NIP-MASTER-TERBARU'])->save();
        $service->forceFill(['name' => 'Layanan Master Terbaru'])->save();
        $category->forceFill(['name' => 'Kategori Master Terbaru'])->save();

        $this->actingAs($admin)
            ->get(route('reports.index', ['month' => '2026-08']))
            ->assertOk()
            ->assertSee('Laporan bulanan')
            ->assertSee('Deskripsi historis')
            ->assertSee('Nama Historis')
            ->assertSee('NIP-HISTORIS')
            ->assertSee('Tim Historis')
            ->assertSee('Layanan Saat Tiket Dibuat')
            ->assertSee('Kategori Saat Tiket Dibuat')
            ->assertSee('02/08/2026 10:00')
            ->assertDontSee('Nama Master Terbaru')
            ->assertDontSee('Layanan Master Terbaru')
            ->assertDontSee('Kategori Master Terbaru');
    }

    public function test_report_defaults_to_current_jakarta_calendar_month(): void
    {
        $admin = $this->createUser([Role::SuperAdmin]);
        $requester = $this->createUser([Role::Pemohon]);
        $service = ServiceType::query()->where('code', 'SVC-01')->firstOrFail();

        Ticket::factory()->create([
            'ticket_number' => 'INC-2026-00011',
            'requester_id' => $requester->id,
            'created_by_id' => $requester->id,
            'service_type_id' => $service->id,
            'description' => 'Tiket bulan berjalan.',
            'submitted_at' => Carbon::parse('2026-08-01 00:30:00', 'Asia/Jakarta'),
        ]);
        Ticket::factory()->create([
            'ticket_number' => 'INC-2026-00012',
            'requester_id' => $requester->id,
            'created_by_id' => $requester->id,
            'service_type_id' => $service->id,
            'description' => 'Tiket bulan sebelumnya.',
            'submitted_at' => Carbon::parse('2026-07-31 23:59:00', 'Asia/Jakarta'),
        ]);

        $this->actingAs($admin)
            ->get(route('reports.index'))
            ->assertOk()
            ->assertSee('Tiket bulan berjalan.')
            ->assertDontSee('Tiket bulan sebelumnya.');
    }

    public function test_only_report_authorized_roles_can_view_and_export(): void
    {
        $admin = $this->createUser([Role::SuperAdmin]);
        $requester = $this->createUser([Role::Pemohon]);
        $tierTwo = $this->createUser([Role::AgenTier2]);
        $inactiveApprover = $this->createUser([Role::Approver]);
        $activeApprover = $this->createUser([Role::Approver]);

        foreach ([$requester, $tierTwo, $inactiveApprover] as $user) {
            $this->actingAs($user)
                ->get(route('reports.index'))
                ->assertForbidden();
        }

        ApproverAssignment::query()->create([
            'user_id' => $activeApprover->id,
            'assigned_by' => $admin->id,
            'started_at' => Carbon::parse('2026-08-01 08:00:00', 'Asia/Jakarta'),
            'is_active' => true,
        ]);

        $this->actingAs($activeApprover)
            ->get(route('reports.index'))
            ->assertOk()
            ->assertSee('Laporan bulanan');

        $this->actingAs($tierTwo)
            ->get(route('reports.monthly.excel'))
            ->assertForbidden();
    }

    public function test_excel_and_pdf_exports_are_downloadable_and_recorded(): void
    {
        $admin = $this->createUser([Role::SuperAdmin]);
        $requester = $this->createUser([Role::Pemohon]);
        $agent = $this->createUser([Role::AgenTier1]);
        $service = ServiceType::query()->where('code', 'SVC-01')->firstOrFail();
        $category = ProblemCategory::factory()->create(['name' => 'Kategori Ekspor']);
        $this->createTicket($requester->id, $agent->id, $service, $category);

        $excel = $this->actingAs($admin)
            ->get(route('reports.monthly.excel', ['month' => '2026-08']))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->assertHeader('Content-Disposition', 'attachment; filename="laporan-tiket-bulanan-2026-08.xlsx"');

        $this->assertStringStartsWith('PK', $excel->getContent());
        $excelPath = tempnam(sys_get_temp_dir(), 'sihati-report-');
        file_put_contents($excelPath, $excel->getContent());

        try {
            $worksheet = IOFactory::load($excelPath)->getActiveSheet();
            $this->assertSame('Nomor Tiket', $worksheet->getCell('A1')->getValue());
            $this->assertSame('Flag Tiket Mandiri', $worksheet->getCell('P1')->getValue());
            $this->assertSame('Deskripsi historis', $worksheet->getCell('E2')->getValue());
        } finally {
            @unlink($excelPath);
        }

        $pdf = $this->actingAs($admin)
            ->get(route('reports.monthly.pdf', ['month' => '2026-08']))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Disposition', 'attachment; filename="laporan-tiket-bulanan-2026-08.pdf"');

        $this->assertStringStartsWith('%PDF-', $pdf->getContent());
        $this->assertDatabaseHas('report_exports', ['format' => 'excel', 'row_count' => 1]);
        $this->assertDatabaseHas('report_exports', ['format' => 'pdf', 'row_count' => 1]);
    }

    private function createTicket(int $requesterId, int $agentId, ServiceType $service, ProblemCategory $category): Ticket
    {
        $ticket = Ticket::factory()->create([
            'ticket_number' => 'INC-2026-00010',
            'requester_id' => $requesterId,
            'created_by_id' => $requesterId,
            'requester_name_snapshot' => 'Nama Historis',
            'requester_nip_snapshot' => 'NIP-HISTORIS',
            'requester_team_snapshot' => 'Tim Historis',
            'created_by_name_snapshot' => 'Nama Pembuat Historis',
            'is_self_created' => true,
            'service_type_id' => $service->id,
            'service_type_code_snapshot' => $service->code,
            'service_type_name_snapshot' => 'Layanan Saat Tiket Dibuat',
            'problem_category_id' => $category->id,
            'problem_category_name_snapshot' => 'Kategori Saat Tiket Dibuat',
            'priority' => Priority::Tinggi,
            'description' => 'Deskripsi historis',
            'solution' => 'Solusi historis',
            'status' => TicketStatus::Ditutup,
            'assigned_to_id' => $agentId,
            'assigned_tier' => Role::AgenTier1->value,
            'confirmation_started_at' => Carbon::parse('2026-08-02 10:00:00', 'Asia/Jakarta'),
            'closed_at' => Carbon::parse('2026-08-03 11:00:00', 'Asia/Jakarta'),
            'submitted_at' => Carbon::parse('2026-08-01 08:00:00', 'Asia/Jakarta'),
        ]);

        return $ticket;
    }
}
