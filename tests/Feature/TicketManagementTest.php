<?php

namespace Tests\Feature;

use App\Enums\Priority;
use App\Enums\Role;
use App\Enums\TicketStatus;
use App\Models\Announcement;
use App\Models\Attachment;
use App\Models\AttachmentPolicy;
use App\Models\Building;
use App\Models\Floor;
use App\Models\ServiceType;
use App\Models\TeamMembership;
use App\Models\Ticket;
use App\Models\TicketFieldValue;
use App\Models\WorkTeam;
use Database\Seeders\OperationalPolicySeeder;
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TicketManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ServiceCatalogSeeder::class);
        $this->seed(OperationalPolicySeeder::class);
    }

    public function test_pemohon_can_create_track_and_cancel_a_ticket(): void
    {
        Storage::fake('local');

        $pemohon = $this->createUser([Role::Pemohon]);
        $floor = $this->createFloor();
        $service = ServiceType::query()->where('code', 'SVC-01')->firstOrFail();
        $policy = AttachmentPolicy::query()->create([
            'service_type_id' => $service->id,
            'type_key' => 'supporting',
            'label' => 'Bukti pendukung',
            'max_file_size_kb' => 100,
            'max_file_count' => 2,
            'allowed_mimes' => ['image/png'],
            'allowed_extensions' => ['png'],
            'visibility' => 'both',
            'is_active' => true,
        ]);
        Announcement::query()->create([
            'title' => 'Gangguan terjadwal',
            'body' => 'Pemeliharaan jaringan berlangsung malam ini.',
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addHour(),
            'is_active' => true,
            'created_by' => $pemohon->id,
        ]);

        $this->actingAs($pemohon)
            ->get(route('tickets.create'))
            ->assertOk()
            ->assertSee('Katalog layanan')
            ->assertSee('Kendala jaringan atau konektivitas')
            ->assertDontSee('Gangguan terjadwal')
            ->assertDontSee('Data pelapor');

        $this->actingAs($pemohon)
            ->get(route('tickets.create', ['service_type_id' => $service->id]))
            ->assertOk()
            ->assertSee('Data Pelapor')
            ->assertSee('Formulir Permintaan')
            ->assertSee($pemohon->name)
            ->assertDontSee('Gangguan terjadwal')
            ->assertSee($pemohon->email)
            ->assertSee($pemohon->nip)
            ->assertSee('Jenis gangguan')
            ->assertSee('name="floor_id"', false)
            ->assertSee('Gedung Utama — Lantai 1')
            ->assertSee('Lantai 1')
            ->assertDontSee('Gangguan terjadwal');

        $svc02 = ServiceType::query()->where('code', 'SVC-02')->firstOrFail();

        $this->actingAs($pemohon)
            ->get(route('tickets.create', ['service_type_id' => $svc02->id]))
            ->assertOk()
            ->assertSee('Nama data atau laporan')
            ->assertDontSee('Jenis gangguan')
            ->assertDontSee('name="floor_id"', false);

        $response = $this->actingAs($pemohon)->post(route('tickets.store'), [
            'requester_id' => $pemohon->id,
            'service_type_id' => $service->id,
            'subject' => 'Wi-Fi tidak dapat digunakan',
            'description' => 'Perangkat saya tidak dapat terhubung ke Wi-Fi kantor.',
            'priority' => Priority::Tinggi->value,
            'floor_id' => $floor->id,
            'fields' => [
                'incident_type' => 'wifi',
                'connection_medium' => 'wifi',
                'affected_scope' => 'self',
                'started_at' => '2026-08-06T08:00',
                'error_message' => 'Tidak ada jaringan yang muncul.',
                'attempted_steps' => 'Sudah mencoba mematikan dan menyalakan Wi-Fi.',
            ],
            'attachments' => [
                $policy->id => [UploadedFile::fake()->create('screenshot.png', 20, 'image/png')],
            ],
        ]);

        $ticket = Ticket::query()->firstOrFail();

        $response->assertRedirect(route('tickets.show', $ticket))
            ->assertSessionHasNoErrors();
        $this->assertMatchesRegularExpression('/^INC-\d{4}-00001$/', $ticket->ticket_number);
        $this->assertSame(TicketStatus::Baru, $ticket->status);
        $this->assertSame(Priority::Tinggi, $ticket->priority);
        $this->assertSame($pemohon->id, $ticket->requester_id);
        $this->assertSame($pemohon->id, $ticket->created_by_id);
        $this->assertSame($floor->id, $ticket->floor_id);
        $this->assertSame('Gedung Utama', $ticket->building_name_snapshot);
        $this->assertSame('Lantai 1', $ticket->floor_name_snapshot);
        $this->assertNull($ticket->room_id);
        $this->assertTrue($ticket->is_self_created);
        $this->assertNull($ticket->assigned_to_id);
        $this->assertSame('wifi', TicketFieldValue::query()->where('ticket_id', $ticket->id)->where('field_key', 'incident_type')->value('value'));
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $pemohon->id,
            'action' => 'ticket.created',
            'outcome' => 'succeeded',
        ]);

        $attachment = Attachment::query()->firstOrFail();
        Storage::disk('local')->assertExists($attachment->storage_path);

        $this->actingAs($pemohon)
            ->get(route('attachments.download', $attachment))
            ->assertOk();

        $otherPemohon = $this->createUser([Role::Pemohon]);
        $this->actingAs($otherPemohon)
            ->get(route('attachments.download', $attachment))
            ->assertForbidden();

        $this->actingAs($pemohon)
            ->get(route('tickets.index'))
            ->assertOk()
            ->assertSee($ticket->ticket_number)
            ->assertSee($ticket->subject);

        $this->actingAs($pemohon)
            ->get(route('tickets.show', $ticket))
            ->assertOk()
            ->assertSee($ticket->ticket_number)
            ->assertSee('Wi-Fi tidak dapat digunakan')
            ->assertSee('screenshot.png');

        $this->actingAs($pemohon)
            ->post(route('tickets.cancel', $ticket))
            ->assertRedirect(route('tickets.show', $ticket))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => TicketStatus::Dibatalkan->value,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $pemohon->id,
            'action' => 'ticket.cancelled',
            'outcome' => 'succeeded',
        ]);

        $this->actingAs($pemohon)
            ->post(route('tickets.store'), $this->payloadFor($service, [
                'subject' => 'Tiket setelah pembatalan',
                'floor_id' => $floor->id,
            ]))
            ->assertRedirect();

        $replacement = Ticket::query()->latest('id')->firstOrFail();
        $this->assertMatchesRegularExpression('/^INC-\d{4}-00002$/', $replacement->ticket_number);
    }

    public function test_ticket_form_hides_the_empty_optional_fields_message(): void
    {
        $pemohon = $this->createUser([Role::Pemohon]);
        $service = ServiceType::query()->create([
            'code' => 'SVC-99',
            'name' => 'Layanan tanpa field tambahan',
            'ticket_class' => 'REQ',
            'sort_order' => 99,
            'is_active' => true,
        ]);

        $this->actingAs($pemohon)
            ->get(route('tickets.create', ['service_type_id' => $service->id]))
            ->assertOk()
            ->assertSee('Layanan tanpa field tambahan')
            ->assertSee('Kirim tiket')
            ->assertDontSee('Belum ada field tambahan untuk layanan ini.');
    }

    public function test_every_service_form_offers_optional_supporting_attachments(): void
    {
        $pemohon = $this->createUser([Role::Pemohon]);

        foreach (ServiceType::query()->active()->orderBy('sort_order')->get() as $service) {
            $this->actingAs($pemohon)
                ->get(route('tickets.create', ['service_type_id' => $service->id]))
                ->assertOk()
                ->assertSee('Lampiran')
                ->assertSee('opsional')
                ->assertSee('Dokumen atau gambar')
                ->assertSee('Maks. 5 berkas')
                ->assertDontSee('Bagian ini boleh dikosongkan.')
                ->assertSee('name="attachments[', false);
        }
    }

    public function test_global_attachment_policy_rejects_executable_uploads(): void
    {
        Storage::fake('local');

        $pemohon = $this->createUser([Role::Pemohon]);
        $service = ServiceType::query()->where('code', 'SVC-02')->firstOrFail();
        $policy = AttachmentPolicy::query()
            ->whereNull('service_type_id')
            ->where('type_key', 'supporting')
            ->firstOrFail();

        $this->actingAs($pemohon)
            ->post(route('tickets.store'), $this->payloadFor($service, [
                'attachments' => [
                    $policy->id => [UploadedFile::fake()->create('malware.exe', 20, 'application/x-msdownload')],
                ],
            ]))
            ->assertRedirect()
            ->assertSessionHasErrors("attachments.{$policy->id}");

        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_svc01_and_svc05_require_a_location(): void
    {
        $pemohon = $this->createUser([Role::Pemohon]);
        $svc01 = ServiceType::query()->where('code', 'SVC-01')->firstOrFail();
        $svc05 = ServiceType::query()->where('code', 'SVC-05')->firstOrFail();

        $this->actingAs($pemohon)
            ->post(route('tickets.store'), $this->payloadFor($svc01, [
                'subject' => 'Koneksi kantor bermasalah',
            ]))
            ->assertRedirect()
            ->assertSessionHasErrors('floor_id');

        $this->actingAs($pemohon)
            ->post(route('tickets.store'), $this->payloadFor($svc05, [
                'subject' => 'Permintaan laptop baru',
                'fields' => array_merge($this->fieldsFor($svc05), ['request_subtype' => 'request']),
            ]))
            ->assertRedirect()
            ->assertSessionHasErrors('floor_id');

        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_hardware_subtype_cannot_override_the_service_ticket_class(): void
    {
        $pemohon = $this->createUser([Role::Pemohon]);
        $floor = $this->createFloor();
        $service = ServiceType::query()->where('code', 'SVC-05')->firstOrFail();

        $this->actingAs($pemohon)
            ->post(route('tickets.store'), $this->payloadFor($service, [
                'subject' => 'Perbaikan laptop kantor',
                'floor_id' => $floor->id,
                'fields' => array_merge($this->fieldsFor($service), ['request_subtype' => 'repair']),
            ]))
            ->assertRedirect();

        $ticket = Ticket::query()->firstOrFail();

        $this->assertSame('REQ', $ticket->ticket_class);
        $this->assertMatchesRegularExpression('/^REQ-\d{4}-00001$/', $ticket->ticket_number);
        $this->assertNull($ticket->service_type_variant_id);
    }

    public function test_agent_tier_one_can_create_a_ticket_for_another_employee_with_snapshots(): void
    {
        $agent = $this->createUser([Role::AgenTier1]);
        $requester = $this->createUser([Role::Pemohon], ['name' => 'Rina Pemohon', 'nip' => '19880001']);
        $team = WorkTeam::factory()->create(['name' => 'Tim Aplikasi']);
        TeamMembership::factory()->create([
            'work_team_id' => $team->id,
            'user_id' => $requester->id,
        ]);
        $service = ServiceType::query()->where('code', 'SVC-06')->firstOrFail();

        $this->actingAs($agent)
            ->post(route('tickets.store'), [
                'requester_id' => $requester->id,
                'service_type_id' => $service->id,
                'subject' => 'Permintaan software desain',
                'description' => 'Mohon instalasi software sesuai kebutuhan tim.',
                'priority' => Priority::Sedang->value,
                'fields' => [
                    'software_name' => 'Perangkat lunak desain',
                    'purpose' => 'Mendukung pekerjaan desain.',
                    'target_platform' => 'Windows',
                    'user_device_count' => '3',
                    'license_action' => 'new',
                ],
            ])
            ->assertRedirect();

        $ticket = Ticket::query()->firstOrFail();
        $this->assertSame($requester->id, $ticket->requester_id);
        $this->assertSame($agent->id, $ticket->created_by_id);
        $this->assertFalse($ticket->is_self_created);
        $this->assertSame('Rina Pemohon', $ticket->requester_name_snapshot);
        $this->assertSame('19880001', $ticket->requester_nip_snapshot);
        $this->assertSame('Tim Aplikasi', $ticket->requester_team_snapshot);
        $this->assertSame('REQ', $ticket->ticket_class);
    }

    public function test_failed_creation_does_not_reuse_a_reserved_number_on_retry(): void
    {
        $pemohon = $this->createUser([Role::Pemohon]);
        $floor = $this->createFloor();
        $service = ServiceType::query()->where('code', 'SVC-01')->firstOrFail();

        Ticket::creating(function (): void {
            throw new \RuntimeException('Simulasi kegagalan setelah alokasi nomor.');
        });

        try {
            $response = $this->actingAs($pemohon)->post(route('tickets.store'), $this->payloadFor($service, [
                'subject' => 'Tiket yang gagal disimpan',
                'floor_id' => $floor->id,
            ]));

            $this->assertSame(500, $response->getStatusCode());
        } finally {
            Ticket::flushEventListeners();
        }

        $this->actingAs($pemohon)
            ->post(route('tickets.store'), $this->payloadFor($service, [
                'subject' => 'Tiket retry',
                'floor_id' => $floor->id,
            ]))
            ->assertRedirect();

        $ticket = Ticket::query()->firstOrFail();
        $this->assertMatchesRegularExpression('/^INC-\d{4}-00002$/', $ticket->ticket_number);
        $this->assertDatabaseCount('tickets', 1);
    }

    public function test_pemohon_cannot_create_a_ticket_on_behalf_of_another_employee(): void
    {
        $pemohon = $this->createUser([Role::Pemohon]);
        $other = $this->createUser([Role::Pemohon]);
        $service = ServiceType::query()->where('code', 'SVC-02')->firstOrFail();

        $this->actingAs($pemohon)
            ->post(route('tickets.store'), [
                'requester_id' => $other->id,
                'service_type_id' => $service->id,
                'subject' => 'Permintaan data',
                'description' => 'Permintaan data untuk laporan.',
                'priority' => Priority::Rendah->value,
                'fields' => $this->fieldsFor($service),
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $pemohon->id,
            'action' => 'ticket.create_for_other',
            'outcome' => 'denied',
        ]);
        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_ticket_numbers_are_separated_by_class_and_increment_without_reuse(): void
    {
        $pemohon = $this->createUser([Role::Pemohon]);
        $floor = $this->createFloor();
        $svc01 = ServiceType::query()->where('code', 'SVC-01')->firstOrFail();
        $svc02 = ServiceType::query()->where('code', 'SVC-02')->firstOrFail();
        $svc03 = ServiceType::query()->where('code', 'SVC-03')->firstOrFail();

        $this->actingAs($pemohon)->post(route('tickets.store'), $this->payloadFor($svc01, [
            'subject' => 'Koneksi pertama',
            'floor_id' => $floor->id,
        ]))->assertRedirect();
        $this->actingAs($pemohon)->post(route('tickets.store'), $this->payloadFor($svc01, [
            'subject' => 'Koneksi kedua',
            'floor_id' => $floor->id,
        ]))->assertRedirect();
        $this->actingAs($pemohon)->post(route('tickets.store'), $this->payloadFor($svc02, [
            'subject' => 'Permintaan data pertama',
        ]))->assertRedirect();
        $this->actingAs($pemohon)->post(route('tickets.store'), $this->payloadFor($svc03, [
            'subject' => 'Perubahan data pertama',
            'fields' => [
                'change_target' => 'Data pegawai',
                'change_type' => 'data_correction',
                'current_state' => 'Data belum sesuai dokumen sumber.',
                'desired_state' => 'Data sesuai dokumen sumber.',
                'business_reason' => 'Koreksi data untuk kebutuhan operasional.',
                'verification_criteria' => 'Nilai baru dapat diverifikasi oleh pemohon.',
            ],
        ]))->assertRedirect();

        $numbers = Ticket::query()->orderBy('id')->pluck('ticket_number')->all();
        $this->assertMatchesRegularExpression('/^INC-\d{4}-00001$/', $numbers[0]);
        $this->assertMatchesRegularExpression('/^INC-\d{4}-00002$/', $numbers[1]);
        $this->assertMatchesRegularExpression('/^REQ-\d{4}-00001$/', $numbers[2]);
        $this->assertMatchesRegularExpression('/^CHG-\d{4}-00001$/', $numbers[3]);
        $this->assertSame(2, Ticket::query()->where('ticket_class', 'INC')->count());
        $this->assertSame(1, Ticket::query()->where('ticket_class', 'REQ')->count());
        $this->assertSame(1, Ticket::query()->where('ticket_class', 'CHG')->count());
    }

    public function test_ticket_year_uses_the_asia_jakarta_calendar(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-12-31 18:30:00', 'UTC'));

        try {
            $pemohon = $this->createUser([Role::Pemohon]);
            $service = ServiceType::query()->where('code', 'SVC-02')->firstOrFail();

            $this->actingAs($pemohon)
                ->post(route('tickets.store'), $this->payloadFor($service, [
                    'subject' => 'Tiket lintas pergantian tahun',
                ]))
                ->assertRedirect();

            $ticket = Ticket::query()->firstOrFail();
            $this->assertSame(2027, $ticket->ticket_year);
            $this->assertSame('REQ-2027-00001', $ticket->ticket_number);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_requester_only_sees_own_tickets_in_the_list(): void
    {
        $first = $this->createUser([Role::Pemohon]);
        $second = $this->createUser([Role::Pemohon]);

        Ticket::factory()->create([
            'subject' => 'Tiket milik pertama',
            'requester_id' => $first->id,
            'created_by_id' => $first->id,
        ]);
        Ticket::factory()->create([
            'subject' => 'Tiket milik kedua',
            'requester_id' => $second->id,
            'created_by_id' => $second->id,
        ]);

        $this->actingAs($first)
            ->get(route('tickets.index'))
            ->assertOk()
            ->assertSee('Tiket milik pertama')
            ->assertDontSee('Tiket milik kedua');
    }

    public function test_requester_list_shows_contextual_actions_for_owned_tickets(): void
    {
        $pemohon = $this->createUser([Role::Pemohon]);

        foreach ([
            [TicketStatus::MenungguPemohon, 'Balas informasi'],
            [TicketStatus::MenungguKonfirmasi, 'Tinjau hasil'],
            [TicketStatus::Ditutup, 'Buka kembali'],
            [TicketStatus::Baru, 'Batalkan'],
        ] as [$status, $subject]) {
            Ticket::factory()->create([
                'requester_id' => $pemohon->id,
                'created_by_id' => $pemohon->id,
                'status' => $status,
                'subject' => $subject,
            ]);
        }

        $this->actingAs($pemohon)
            ->get(route('tickets.index'))
            ->assertOk()
            ->assertSee('No Tiket')
            ->assertSee('Balas')
            ->assertSee('Tinjau hasil')
            ->assertSee('Buka kembali')
            ->assertSee('Batalkan');
    }

    public function test_requester_cannot_cancel_after_ticket_is_claimed(): void
    {
        $pemohon = $this->createUser([Role::Pemohon]);
        $agent = $this->createUser([Role::AgenTier1]);
        $ticket = Ticket::factory()->create([
            'requester_id' => $pemohon->id,
            'created_by_id' => $pemohon->id,
            'status' => TicketStatus::Diproses,
            'assigned_to_id' => $agent->id,
            'assigned_tier' => Role::AgenTier1->value,
        ]);

        $this->actingAs($pemohon)
            ->post(route('tickets.cancel', $ticket))
            ->assertForbidden();

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => TicketStatus::Diproses->value,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $pemohon->id,
            'action' => 'ticket.cancel',
            'outcome' => 'denied',
        ]);
    }

    private function createFloor(): Floor
    {
        $building = Building::query()->create(['name' => 'Gedung Utama', 'is_active' => true]);

        return Floor::query()->create(['building_id' => $building->id, 'name' => 'Lantai 1', 'sort_order' => 1, 'is_active' => true]);
    }

    /** @return array<string, mixed> */
    private function payloadFor(ServiceType $service, array $overrides = []): array
    {
        return array_replace([
            'requester_id' => $this->currentUserId(),
            'service_type_id' => $service->id,
            'subject' => 'Permintaan layanan',
            'description' => 'Deskripsi permintaan layanan yang cukup jelas.',
            'priority' => Priority::Sedang->value,
            'floor_id' => null,
            'fields' => $this->fieldsFor($service),
        ], $overrides);
    }

    /** @return array<string, mixed> */
    private function fieldsFor(ServiceType $service): array
    {
        return match ($service->code) {
            'SVC-01' => [
                'incident_type' => 'wifi',
                'connection_medium' => 'wifi',
                'affected_scope' => 'self',
                'started_at' => '2026-08-06T08:00',
            ],
            'SVC-02' => [
                'data_name' => 'Laporan tiket',
                'purpose' => 'Penyusunan laporan bulanan.',
                'period_start' => '2026-08-01',
                'period_end' => '2026-08-06',
                'requested_columns' => 'Nomor tiket dan status.',
                'output_format' => 'xlsx',
            ],
            'SVC-03' => [
                'change_target' => 'Data pegawai',
                'change_type' => 'data_correction',
                'current_state' => 'Data belum sesuai dokumen sumber.',
                'desired_state' => 'Data sesuai dokumen sumber.',
                'business_reason' => 'Koreksi data untuk kebutuhan operasional.',
                'verification_criteria' => 'Nilai baru dapat diverifikasi oleh pemohon.',
            ],
            'SVC-05' => [
                'request_subtype' => 'repair',
                'hardware_type' => 'laptop',
                'symptom_or_need' => 'Perangkat tidak menyala.',
            ],
            'SVC-06' => [
                'software_name' => 'Perangkat lunak kantor',
                'purpose' => 'Mendukung pekerjaan harian.',
                'target_platform' => 'Windows',
                'user_device_count' => '1',
                'license_action' => 'new',
            ],
            default => [],
        };
    }

    private function currentUserId(): int
    {
        return (int) auth()->id();
    }
}
