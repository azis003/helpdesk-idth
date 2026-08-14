<?php

namespace Database\Seeders;

use App\Enums\Priority;
use App\Enums\TicketStatus;
use App\Models\ApproverAssignment;
use App\Models\Attachment;
use App\Models\AttachmentPolicy;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Role;
use App\Models\ServiceFieldDefinition;
use App\Models\ServiceType;
use App\Models\Skill;
use App\Models\TeamChairAssignment;
use App\Models\TeamMembership;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use App\Models\WorkTeam;
use App\Services\ApproverAssignmentService;
use App\Services\DatabaseChangeControlService;
use App\Services\TicketApprovalService;
use App\Services\TicketCancellationService;
use App\Services\TicketCommunicationService;
use App\Services\TicketInternalFieldService;
use App\Services\TicketResolutionService;
use App\Services\TicketSlaService;
use App\Services\TicketWaitingService;
use App\Services\TicketWorkflowService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class UiBaselineFixtureSeeder extends Seeder
{
    public const DEFAULT_PASSWORD = 'Ui-Baseline-Local-2026!';

    public const DEFAULT_APPROVER_PROFILE = 'ACT-05';

    public const ACTOR_COUNT = 13;

    public const TICKET_COUNT = 22;

    /** @var array<string, string> */
    public const APPROVER_PROFILES = [
        'ACT-05' => 'ui_test_approver_current',
        'ACT-09' => 'ui_test_requester_approver',
    ];

    /** @var array<string, array{username:string,name:string,roles:list<string>}> */
    public const ACTORS = [
        'ACT-01' => [
            'username' => 'ui_test_super_admin',
            'name' => 'ACT-01 — Super Admin UI Fixture',
            'roles' => ['super_admin'],
        ],
        'ACT-02' => [
            'username' => 'ui_test_requester',
            'name' => 'ACT-02 — Pemohon UI Fixture dengan Nama Tampilan Panjang untuk Pengujian Responsif',
            'roles' => ['pemohon'],
        ],
        'ACT-03' => [
            'username' => 'ui_test_agent_t1',
            'name' => 'ACT-03 — Agen Tier 1 UI Fixture',
            'roles' => ['agen_tier_1'],
        ],
        'ACT-04' => [
            'username' => 'ui_test_agent_t2',
            'name' => 'ACT-04 — Agen Tier 2 UI Fixture',
            'roles' => ['agen_tier_2'],
        ],
        'ACT-05' => [
            'username' => 'ui_test_approver_current',
            'name' => 'ACT-05 — Approver Current UI Fixture',
            'roles' => ['approver'],
        ],
        'ACT-06' => [
            'username' => 'ui_test_approver_stale',
            'name' => 'ACT-06 — Approver Stale UI Fixture',
            'roles' => ['approver'],
        ],
        'ACT-07' => [
            'username' => 'ui_test_team_chair',
            'name' => 'ACT-07 — Ketua Tim Kerja UI Fixture',
            'roles' => ['ketua_tim_kerja'],
        ],
        'ACT-08' => [
            'username' => 'ui_test_admin_t1',
            'name' => 'ACT-08 — Super Admin dan Agen Tier 1 UI Fixture',
            'roles' => ['super_admin', 'agen_tier_1'],
        ],
        'ACT-09' => [
            'username' => 'ui_test_requester_approver',
            'name' => 'ACT-09 — Pemohon dan Approver UI Fixture',
            'roles' => ['pemohon', 'approver'],
        ],
        'ACT-10' => [
            'username' => 'ui_test_team_chair_t1',
            'name' => 'ACT-10 — Ketua Tim dan Agen Tier 1 UI Fixture',
            'roles' => ['ketua_tim_kerja', 'agen_tier_1'],
        ],
        'ACT-11' => [
            'username' => 'ui_test_team_chair_admin',
            'name' => 'ACT-11 — Ketua Tim dan Super Admin UI Fixture',
            'roles' => ['ketua_tim_kerja', 'super_admin'],
        ],
        'SUP-01' => [
            'username' => 'ui_test_requester_outside',
            'name' => 'SUP-01 — Pemohon Outside Scope UI Fixture',
            'roles' => ['pemohon'],
        ],
        'SUP-02' => [
            'username' => 'ui_test_agent_t2_unrelated',
            'name' => 'SUP-02 — Agen Tier 2 Unrelated UI Fixture',
            'roles' => ['agen_tier_2'],
        ],
    ];

    /**
     * @var array<string, array{
     *     number:string,service:string,priority:string,requester:string,title:string,aliases:string,
     *     description?:string,long_service_snapshot?:bool
     * }>
     */
    public const TICKETS = [
        'UI-TKT-NEW-001' => [
            'number' => 'INC-2026-91001', 'service' => 'SVC-01', 'priority' => 'kritis', 'requester' => 'ACT-02',
            'aliases' => 'TKT-STATUS-01|WF-01', 'title' => 'Antrean baru Tier 1 tanpa penanggung jawab',
        ],
        'UI-TKT-T1-001' => [
            'number' => 'REQ-2026-91001', 'service' => 'SVC-06', 'priority' => 'tinggi', 'requester' => 'ACT-02',
            'aliases' => 'TKT-STATUS-02|WF-02', 'title' => 'Tiket sudah diklaim Agen Tier 1',
        ],
        'UI-TKT-T2-001' => [
            'number' => 'REQ-2026-91002', 'service' => 'SVC-05', 'priority' => 'sedang', 'requester' => 'ACT-02',
            'aliases' => 'TKT-STATUS-03|WF-03', 'title' => 'Tiket hardware ditugaskan kepada Agen Tier 2',
        ],
        'UI-TKT-APPROVAL-CURRENT-001' => [
            'number' => 'CHG-2026-91001', 'service' => 'SVC-04', 'priority' => 'rendah', 'requester' => 'ACT-02',
            'aliases' => 'TKT-STATUS-04|WF-06', 'title' => 'Persetujuan tertunda untuk approver current',
        ],
        'UI-TKT-APPROVAL-STALE-001' => [
            'number' => 'CHG-2026-91002', 'service' => 'SVC-04', 'priority' => 'tinggi', 'requester' => 'ACT-02',
            'aliases' => 'WF-07', 'title' => 'Persetujuan yang tidak dapat diputuskan approver stale',
        ],
        'UI-TKT-WAIT-REQUESTER-001' => [
            'number' => 'REQ-2026-91003', 'service' => 'SVC-06', 'priority' => 'sedang', 'requester' => 'ACT-02',
            'aliases' => 'TKT-STATUS-05|WF-04', 'title' => 'Menunggu informasi tambahan dari Pemohon',
        ],
        'UI-TKT-WAIT-THIRD-PARTY-001' => [
            'number' => 'REQ-2026-91004', 'service' => 'SVC-06', 'priority' => 'rendah', 'requester' => 'ACT-02',
            'aliases' => 'TKT-STATUS-06|WF-05', 'title' => 'Menunggu tindak lanjut vendor synthetic',
        ],
        'UI-TKT-SVC02-RESULT-001' => [
            'number' => 'REQ-2026-91005', 'service' => 'SVC-02', 'priority' => 'tinggi', 'requester' => 'ACT-02',
            'aliases' => 'TKT-STATUS-07|WF-08', 'title' => 'Hasil tarik data menunggu konfirmasi Pemohon',
        ],
        'UI-TKT-CLOSED-ELIGIBLE-001' => [
            'number' => 'CHG-2026-91003', 'service' => 'SVC-04', 'priority' => 'sedang', 'requester' => 'ACT-02',
            'aliases' => 'TKT-STATUS-08|WF-09', 'title' => 'Tiket ditutup yang masih eligible untuk reopen',
        ],
        'UI-TKT-CLOSED-INELIGIBLE-001' => [
            'number' => 'CHG-2026-91004', 'service' => 'SVC-04', 'priority' => 'rendah', 'requester' => 'ACT-02',
            'aliases' => 'WF-10', 'title' => 'Tiket ditutup di luar jendela reopen',
        ],
        'UI-TKT-CANCELLED-001' => [
            'number' => 'REQ-2026-91006', 'service' => 'SVC-06', 'priority' => 'sedang', 'requester' => 'ACT-02',
            'aliases' => 'TKT-STATUS-11|WF-11', 'title' => 'Tiket baru yang dibatalkan Pemohon',
        ],
        'UI-TKT-REJECTED-001' => [
            'number' => 'INC-2026-91002', 'service' => 'SVC-01', 'priority' => 'rendah', 'requester' => 'ACT-02',
            'aliases' => 'TKT-STATUS-09|WF-12', 'title' => 'Tiket antrean yang ditolak saat triase',
        ],
        'UI-TKT-APPROVAL-REJECTED-001' => [
            'number' => 'CHG-2026-91005', 'service' => 'SVC-04', 'priority' => 'kritis', 'requester' => 'ACT-02',
            'aliases' => 'TKT-STATUS-10|WF-13', 'title' => 'Permintaan perubahan yang tidak disetujui',
        ],
        'UI-TKT-SVC02-BEFORE-001' => [
            'number' => 'REQ-2026-91007', 'service' => 'SVC-02', 'priority' => 'sedang', 'requester' => 'ACT-02',
            'aliases' => 'SVC02-BEFORE-COMPLETION', 'title' => 'Tarik data sebelum hasil ekspor tersedia',
        ],
        'UI-TKT-SVC03-INCOMPLETE-001' => [
            'number' => 'CHG-2026-91006', 'service' => 'SVC-03', 'priority' => 'tinggi', 'requester' => 'ACT-02',
            'aliases' => 'SVC03-EVIDENCE-INCOMPLETE', 'title' => 'Perubahan database dengan evidence belum lengkap',
        ],
        'UI-TKT-SVC03-PREEXEC-001' => [
            'number' => 'CHG-2026-91007', 'service' => 'SVC-03', 'priority' => 'kritis', 'requester' => 'ACT-02',
            'aliases' => 'SVC03-PRE-EXECUTION', 'title' => 'Perubahan database siap sebelum eksekusi',
        ],
        'UI-TKT-SVC03-EXECUTED-001' => [
            'number' => 'CHG-2026-91008', 'service' => 'SVC-03', 'priority' => 'tinggi', 'requester' => 'ACT-02',
            'aliases' => 'SVC03-EXECUTED', 'title' => 'Perubahan database sudah dieksekusi belum diverifikasi',
        ],
        'UI-TKT-SVC03-VERIFIED-001' => [
            'number' => 'CHG-2026-91009', 'service' => 'SVC-03', 'priority' => 'sedang', 'requester' => 'ACT-02',
            'aliases' => 'SVC03-VERIFIED', 'title' => 'Perubahan database sudah dieksekusi dan diverifikasi',
        ],
        'UI-TKT-SVC07-001' => [
            'number' => 'CHG-2026-91010', 'service' => 'SVC-07', 'priority' => 'rendah', 'requester' => 'ACT-02',
            'aliases' => 'SVC07-INTERNAL-FIELDS|LONG-CONTENT',
            'title' => 'Usulan aplikasi baru dengan subject sangat panjang untuk menguji wrapping, truncation, breadcrumb, header, tabel, kartu, dan tampilan ponsel tanpa merusak hierarki informasi',
            'description' => "Baris pertama menjelaskan kebutuhan synthetic.\nBaris kedua memuat konteks lintas unit tanpa data nyata.\nBaris ketiga sengaja panjang untuk pengujian layout responsif dan keterbacaan detail tiket.",
        ],
        'UI-TKT-TEAM-IN-001' => [
            'number' => 'REQ-2026-91008', 'service' => 'SVC-06', 'priority' => 'tinggi', 'requester' => 'ACT-02',
            'aliases' => 'TEAM-CHAIR-IN-SCOPE', 'title' => 'Tiket anggota TEAM-A dalam scope ACT-07',
        ],
        'UI-TKT-TEAM-OUT-001' => [
            'number' => 'REQ-2026-91009', 'service' => 'SVC-06', 'priority' => 'sedang', 'requester' => 'SUP-01',
            'aliases' => 'TEAM-CHAIR-OUTSIDE-SCOPE', 'title' => 'Tiket anggota TEAM-B di luar scope ACT-07',
            'long_service_snapshot' => true,
        ],
        'UI-TKT-TEAM-MULTI-001' => [
            'number' => 'REQ-2026-91010', 'service' => 'SVC-06', 'priority' => 'rendah', 'requester' => 'ACT-09',
            'aliases' => 'MULTI-ROLE-TEAM-C', 'title' => 'Tiket requester ACT-09 dalam safe scope ACT-11',
        ],
    ];

    /** @var array<string, array{status:string,assignee:?string}> */
    private const EXPECTED_TICKET_STATES = [
        'UI-TKT-NEW-001' => ['status' => 'baru', 'assignee' => null],
        'UI-TKT-T1-001' => ['status' => 'diproses', 'assignee' => 'ui_test_admin_t1'],
        'UI-TKT-T2-001' => ['status' => 'dikerjakan', 'assignee' => 'ui_test_agent_t2'],
        'UI-TKT-APPROVAL-CURRENT-001' => ['status' => 'menunggu_persetujuan', 'assignee' => 'ui_test_agent_t1'],
        'UI-TKT-APPROVAL-STALE-001' => ['status' => 'menunggu_persetujuan', 'assignee' => 'ui_test_agent_t1'],
        'UI-TKT-WAIT-REQUESTER-001' => ['status' => 'menunggu_pemohon', 'assignee' => 'ui_test_agent_t1'],
        'UI-TKT-WAIT-THIRD-PARTY-001' => ['status' => 'menunggu_pihak_ketiga', 'assignee' => 'ui_test_agent_t1'],
        'UI-TKT-SVC02-RESULT-001' => ['status' => 'menunggu_konfirmasi', 'assignee' => 'ui_test_agent_t1'],
        'UI-TKT-CLOSED-ELIGIBLE-001' => ['status' => 'ditutup', 'assignee' => 'ui_test_agent_t1'],
        'UI-TKT-CLOSED-INELIGIBLE-001' => ['status' => 'ditutup', 'assignee' => 'ui_test_agent_t1'],
        'UI-TKT-CANCELLED-001' => ['status' => 'dibatalkan', 'assignee' => null],
        'UI-TKT-REJECTED-001' => ['status' => 'ditolak', 'assignee' => null],
        'UI-TKT-APPROVAL-REJECTED-001' => ['status' => 'tidak_disetujui', 'assignee' => 'ui_test_agent_t1'],
        'UI-TKT-SVC02-BEFORE-001' => ['status' => 'dikerjakan', 'assignee' => 'ui_test_agent_t1'],
        'UI-TKT-SVC03-INCOMPLETE-001' => ['status' => 'dikerjakan', 'assignee' => 'ui_test_agent_t1'],
        'UI-TKT-SVC03-PREEXEC-001' => ['status' => 'dikerjakan', 'assignee' => 'ui_test_agent_t1'],
        'UI-TKT-SVC03-EXECUTED-001' => ['status' => 'dikerjakan', 'assignee' => 'ui_test_agent_t1'],
        'UI-TKT-SVC03-VERIFIED-001' => ['status' => 'dikerjakan', 'assignee' => 'ui_test_agent_t1'],
        'UI-TKT-SVC07-001' => ['status' => 'dikerjakan', 'assignee' => 'ui_test_agent_t1'],
        'UI-TKT-TEAM-IN-001' => ['status' => 'dikerjakan', 'assignee' => 'ui_test_agent_t1'],
        'UI-TKT-TEAM-OUT-001' => ['status' => 'dikerjakan', 'assignee' => 'ui_test_agent_t1'],
        'UI-TKT-TEAM-MULTI-001' => ['status' => 'baru', 'assignee' => null],
    ];

    /** @var list<string> */
    private const EXPECTED_COMMENTS = [
        '[UI-CMT-WAIT-REQUESTER] Mohon tambahkan versi sistem operasi dan tangkapan layar synthetic.',
        '[UI-CMT-REQUEST] Mohon lengkapi sasaran pengguna sebelum analisis dilanjutkan.',
        '[UI-CMT-REQUESTER-REPLY] Sasaran pengguna adalah seluruh unit uji synthetic dan tidak memuat data pegawai nyata.',
        '[UI-CMT-PUBLIC-LONG] Pembaruan publik synthetic ini sengaja dibuat panjang untuk menguji wrapping pada timeline, jarak antarbaris, tautan lampiran, dan keterbacaan pada viewport sempit. Tidak ada informasi pribadi atau data operasional nyata di dalam komentar ini.',
        '[UI-CMT-INTERNAL-LONG] Catatan internal synthetic untuk pengujian visibility. Konten ini harus terlihat bagi agen yang berwenang, tetapi tidak boleh masuk ke safe projection Ketua Tim atau pengalaman Pemohon. Teks dibuat cukup panjang untuk menguji layout tanpa memakai informasi rahasia nyata.',
        '[UI-CMT-TEAM-PUBLIC] Pembaruan publik untuk safe projection Ketua Tim.',
        '[UI-CMT-TEAM-INTERNAL] Catatan internal yang wajib dikeluarkan dari safe projection Ketua Tim.',
    ];

    /** @var list<string> */
    private const EXPECTED_ATTACHMENTS = [
        'UI-ATT-01-public-requester-visible.txt',
        'UI-ATT-02-internal-agent-only.txt',
        'UI-ATT-03-data-export-result.csv',
        'UI-ATT-04-assigned-tier-two.txt',
        'UI-ATT-05-unrelated-tier-two-denied.txt',
        'UI-ATT-06-team-chair-denied.txt',
        'UI-ATT-07-soft-deleted-retained.txt',
        'UI-SVC03-INCOMPLETE-change-script.sql',
        'UI-SVC03-PREEXEC-change-script.sql',
        'UI-SVC03-PREEXEC-rollback-script.sql',
        'UI-SVC03-PREEXEC-backup-evidence.txt',
        'UI-SVC03-EXECUTED-change-script.sql',
        'UI-SVC03-EXECUTED-rollback-script.sql',
        'UI-SVC03-EXECUTED-backup-evidence.txt',
        'UI-SVC03-VERIFIED-change-script.sql',
        'UI-SVC03-VERIFIED-rollback-script.sql',
        'UI-SVC03-VERIFIED-backup-evidence.txt',
    ];

    public function __construct(
        private readonly ApproverAssignmentService $approverAssignments,
        private readonly TicketWorkflowService $workflow,
        private readonly TicketWaitingService $waiting,
        private readonly TicketApprovalService $approvals,
        private readonly TicketResolutionService $resolution,
        private readonly TicketCancellationService $cancellation,
        private readonly TicketCommunicationService $communication,
        private readonly TicketInternalFieldService $internalFields,
        private readonly TicketSlaService $sla,
        private readonly DatabaseChangeControlService $databaseChanges,
    ) {}

    public function run(): void
    {
        $this->seed(self::DEFAULT_APPROVER_PROFILE);
    }

    /** @return array<string, int|string|bool> */
    public function seed(string $approverProfile = self::DEFAULT_APPROVER_PROFILE): array
    {
        $this->assertAllowedEnvironment();
        $approverProfile = strtoupper(trim($approverProfile));

        if (! array_key_exists($approverProfile, self::APPROVER_PROFILES)) {
            throw new RuntimeException('Profil approver tidak valid. Gunakan ACT-05 atau ACT-09.');
        }

        if ($this->hasFixtureMarker()) {
            if (! $this->manifestIsComplete($approverProfile)) {
                throw new RuntimeException(
                    'UI baseline fixture terdeteksi tetapi manifest tidak lengkap atau profil approver berbeda. '.
                    'Gunakan database local/testing disposable yang baru, lalu jalankan ulang command.',
                );
            }

            return $this->summary($approverProfile, true);
        }

        $this->assertDisposableDatabase();
        $previousTestNow = Carbon::getTestNow();

        try {
            Carbon::setTestNow($this->at('2026-08-10 07:30:00'));
            (new RoleSeeder)->run();
            (new ServiceCatalogSeeder)->run();
            (new OperationalPolicySeeder)->run();

            $actors = $this->createActors();
            [$teams, $floor] = $this->createOrganization($actors);
            $this->createApproverHistory($actors, $approverProfile);
            $tickets = $this->createBaseTickets($actors, $teams, $floor);
            $this->applyWorkflows($tickets, $actors);
            $this->createCommentsAndAttachments($tickets, $actors);
        } finally {
            Carbon::setTestNow($previousTestNow);
        }

        if (! $this->manifestIsComplete($approverProfile)) {
            throw new RuntimeException('Pembuatan UI baseline fixture selesai tetapi verifikasi manifest gagal. Reset database disposable sebelum mencoba lagi.');
        }

        return $this->summary($approverProfile, false);
    }

    public function assertAllowedEnvironment(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException(
                'UI baseline fixture ditolak: command hanya boleh dijalankan pada environment local atau testing.',
            );
        }
    }

    /** @return array<string, User> */
    private function createActors(): array
    {
        $users = [];

        foreach (self::ACTORS as $id => $definition) {
            $sequence = (int) preg_replace('/\D+/', '', $id);
            $users[$id] = User::query()->create([
                'name' => $definition['name'],
                'username' => $definition['username'],
                'email' => $definition['username'].'@example.invalid',
                'nip' => sprintf('UIFIX-%s-%03d', str_starts_with($id, 'ACT') ? 'ACT' : 'SUP', $sequence),
                'password' => Hash::make(self::DEFAULT_PASSWORD),
                'is_active' => true,
                'must_change_password' => false,
                'password_changed_at' => now(),
            ]);
        }

        $roleIds = Role::query()->pluck('id', 'slug');
        $assignedBy = $users['ACT-01']->getKey();

        foreach (self::ACTORS as $id => $definition) {
            $ids = collect($definition['roles'])
                ->map(fn (string $slug): int => (int) $roleIds->get($slug))
                ->all();
            $users[$id]->roles()->syncWithPivotValues($ids, [
                'assigned_by' => $assignedBy,
                'assigned_at' => now(),
            ]);
            $users[$id]->load('roles');
        }

        return $users;
    }

    /**
     * @param  array<string, User>  $actors
     * @return array{array<string, WorkTeam>, Floor}
     */
    private function createOrganization(array $actors): array
    {
        $teams = [
            'TEAM-A' => WorkTeam::query()->create([
                'name' => 'UI Fixture TEAM-A',
                'description' => 'Tim synthetic utama untuk safe projection ACT-07.',
                'is_active' => true,
            ]),
            'TEAM-B' => WorkTeam::query()->create([
                'name' => 'UI Fixture TEAM-B',
                'description' => 'Tim synthetic di luar scope ACT-07.',
                'is_active' => true,
            ]),
            'TEAM-C' => WorkTeam::query()->create([
                'name' => 'UI Fixture TEAM-C',
                'description' => 'Tim synthetic untuk kombinasi role ACT-11.',
                'is_active' => true,
            ]),
        ];

        foreach ([
            ['TEAM-A', 'ACT-02'],
            ['TEAM-A', 'ACT-04'],
            ['TEAM-B', 'SUP-01'],
            ['TEAM-B', 'SUP-02'],
            ['TEAM-C', 'ACT-09'],
        ] as [$teamId, $actorId]) {
            TeamMembership::query()->create([
                'work_team_id' => $teams[$teamId]->getKey(),
                'user_id' => $actors[$actorId]->getKey(),
                'assigned_by' => $actors['ACT-01']->getKey(),
                'started_at' => $this->at('2026-07-01 08:00:00'),
                'is_active' => true,
            ]);
        }

        foreach ([
            ['TEAM-A', 'ACT-07'],
            ['TEAM-B', 'ACT-10'],
            ['TEAM-C', 'ACT-11'],
        ] as [$teamId, $actorId]) {
            TeamChairAssignment::query()->create([
                'work_team_id' => $teams[$teamId]->getKey(),
                'user_id' => $actors[$actorId]->getKey(),
                'assigned_by' => $actors['ACT-01']->getKey(),
                'started_at' => $this->at('2026-07-01 08:00:00'),
                'is_active' => true,
            ]);
        }

        $skill = Skill::query()->create([
            'name' => 'UI Fixture Database dan Infrastruktur',
            'slug' => 'ui-fixture-database-infrastructure',
            'description' => 'Skill synthetic untuk assignment Agen Tier 2.',
            'is_active' => true,
        ]);
        $pivot = ['assigned_by' => $actors['ACT-01']->getKey(), 'assigned_at' => now()];
        $actors['ACT-04']->skills()->attach($skill->getKey(), $pivot);
        $actors['SUP-02']->skills()->attach($skill->getKey(), $pivot);
        ServiceType::query()->whereIn('code', ['SVC-03', 'SVC-05'])->get()
            ->each(fn (ServiceType $service) => $service->skills()->attach($skill->getKey(), $pivot));

        $building = Building::query()->create(['name' => 'Gedung Uji A', 'is_active' => true]);
        $floor = Floor::query()->create([
            'building_id' => $building->getKey(),
            'name' => 'Lantai 1 — UI Fixture',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        return [$teams, $floor];
    }

    /** @param array<string, User> $actors */
    private function createApproverHistory(array $actors, string $approverProfile): void
    {
        $admin = $actors['ACT-01'];
        $this->approverAssignments->replace(
            $admin,
            (int) $actors['ACT-06']->getKey(),
            true,
            'UI fixture: buat assignment stale yang authoritative.',
        );
        $this->approverAssignments->replace(
            $admin,
            (int) $actors[$approverProfile]->getKey(),
            true,
            "UI fixture: aktifkan profil {$approverProfile}.",
        );
    }

    /**
     * @param  array<string, User>  $actors
     * @param  array<string, WorkTeam>  $teams
     * @return array<string, Ticket>
     */
    private function createBaseTickets(array $actors, array $teams, Floor $floor): array
    {
        $services = ServiceType::query()
            ->with('activeFieldDefinitions')
            ->whereIn('code', collect(self::TICKETS)->pluck('service')->unique()->all())
            ->get()
            ->keyBy('code');
        $tickets = [];

        foreach (self::TICKETS as $index => $definition) {
            $position = array_search($index, array_keys(self::TICKETS), true);
            $time = $index === 'UI-TKT-CLOSED-INELIGIBLE-001'
                ? $this->at('2026-06-01 08:00:00')
                : $this->at('2026-08-10 08:00:00')->addMinutes(((int) $position) * 4);
            Carbon::setTestNow($time);

            $service = $services->get($definition['service']);

            if (! $service instanceof ServiceType) {
                throw new RuntimeException("Service {$definition['service']} tidak tersedia setelah canonical seeding.");
            }

            $requester = $actors[$definition['requester']];
            $membership = TeamMembership::query()
                ->with('workTeam')
                ->where('user_id', $requester->getKey())
                ->where('is_active', true)
                ->first();
            $parts = explode('-', $definition['number']);
            $ticket = Ticket::query()->create([
                'ticket_number' => $definition['number'],
                'ticket_class' => $parts[0],
                'ticket_year' => (int) $parts[1],
                'ticket_sequence' => (int) $parts[2],
                'subject' => $this->subject($index, $definition),
                'requester_id' => $requester->getKey(),
                'created_by_id' => $requester->getKey(),
                'requester_name_snapshot' => $requester->name,
                'requester_nip_snapshot' => $requester->nip,
                'requester_team_snapshot' => $membership?->workTeam?->name,
                'created_by_name_snapshot' => $requester->name,
                'is_self_created' => true,
                'status' => TicketStatus::Baru,
                'assigned_to_id' => null,
                'assigned_tier' => null,
                'service_type_id' => $service->getKey(),
                'service_type_code_snapshot' => $service->code,
                'service_type_name_snapshot' => ($definition['long_service_snapshot'] ?? false)
                    ? 'Permintaan software dengan nama snapshot layanan historical yang panjang untuk pengujian tampilan tabel dan kartu responsif'
                    : $service->name,
                'priority' => Priority::from($definition['priority']),
                'description' => $definition['description'] ?? "Deskripsi synthetic untuk {$index}.\nTidak mengandung data pengguna atau organisasi nyata.",
                'floor_id' => in_array($service->code, ['SVC-01', 'SVC-05'], true) ? $floor->getKey() : null,
                'building_name_snapshot' => in_array($service->code, ['SVC-01', 'SVC-05'], true) ? $floor->building->name : null,
                'floor_name_snapshot' => in_array($service->code, ['SVC-01', 'SVC-05'], true) ? $floor->name : null,
                'submitted_at' => $time,
            ]);
            $ticket->statusHistories()->create([
                'from_status' => null,
                'to_status' => TicketStatus::Baru->value,
                'action' => 'ticket.created',
                'actor_id' => $requester->getKey(),
                'metadata' => ['source' => 'ui_baseline_fixture', 'fixture_id' => $index],
                'occurred_at' => $time,
            ]);
            $this->createRequesterFieldValues($ticket, $service);
            $this->sla->start($ticket, $time);
            $tickets[$index] = $ticket->fresh(['serviceType', 'requester']);
        }

        return $tickets;
    }

    private function createRequesterFieldValues(Ticket $ticket, ServiceType $service): void
    {
        $values = $this->requesterFields($service->code);

        foreach ($service->activeFieldDefinitions->whereIn('visibility', ['requester', 'both']) as $definition) {
            $ticket->fieldValues()->create([
                'service_field_definition_id' => $definition->getKey(),
                'field_key' => $definition->key,
                'label_snapshot' => $definition->label,
                'field_type_snapshot' => $definition->field_type,
                'visibility_snapshot' => $definition->visibility,
                'version_snapshot' => $definition->version,
                'value' => $values[$definition->key] ?? null,
            ]);
        }
    }

    /** @return array<string, mixed> */
    private function requesterFields(string $serviceCode): array
    {
        return match ($serviceCode) {
            'SVC-01' => [
                'incident_type' => 'wifi', 'connection_medium' => 'wifi', 'affected_scope' => 'self',
                'started_at' => '2026-08-10T07:45', 'error_message' => 'UI_FIXTURE_NETWORK_ERROR',
                'attempted_steps' => "Mematikan dan menyalakan Wi-Fi.\nMencoba jaringan synthetic lain.",
            ],
            'SVC-02' => [
                'data_name' => 'Dataset synthetic UI baseline', 'purpose' => 'Verifikasi tampilan hasil ekspor tanpa data nyata.',
                'period_start' => '2026-07-01', 'period_end' => '2026-07-31',
                'requested_columns' => "fixture_id\nstatus_label\ncreated_date", 'output_format' => 'csv',
                'filter_criteria' => null,
            ],
            'SVC-03' => [
                'change_target' => 'database_ui_fixture', 'change_type' => 'data_correction',
                'current_state' => 'Nilai synthetic belum ternormalisasi.',
                'desired_state' => 'Nilai synthetic sudah konsisten untuk verifikasi UI.',
                'business_reason' => 'Menampilkan state change control secara deterministic.',
                'verification_criteria' => 'Checksum synthetic cocok dan tidak ada data nyata.',
                'impact_estimate' => 'low', 'requested_execution_window' => '2026-08-12T10:00',
            ],
            'SVC-04' => [
                'application_name' => 'Aplikasi Synthetic Baseline', 'module_or_feature' => 'Modul UI Fixture',
                'change_type' => 'enhancement', 'current_behavior' => 'Tampilan contoh sebelum perubahan.',
                'desired_behavior' => 'Tampilan contoh sesudah perubahan.', 'business_impact' => 'Hanya untuk visual regression lokal.',
                'acceptance_criteria' => 'State dan aksi tampil sesuai policy.', 'reproduction_steps' => null,
            ],
            'SVC-05' => [
                'request_subtype' => 'repair', 'hardware_type' => 'laptop',
                'symptom_or_need' => 'Perangkat synthetic menampilkan indikator uji.', 'asset_tag' => 'UI-ASSET-0001',
                'quantity' => 1, 'business_justification' => null, 'needed_by' => '2026-08-20',
            ],
            'SVC-06' => [
                'software_name' => 'Synthetic UI Tool', 'purpose' => 'Visual regression lokal.',
                'target_platform' => 'Windows Test Device', 'user_device_count' => 1,
                'license_action' => 'free_or_open_source', 'version_or_edition' => null,
                'needed_by' => '2026-08-21', 'constraints' => null,
            ],
            'SVC-07' => [
                'application_name' => 'Sistem Synthetic UI Baseline',
                'background_problem' => 'Baseline memerlukan data panjang, multiline, dan state yang dapat diulang.',
                'objective' => 'Memvalidasi presentation layer tanpa mengubah behavior aplikasi.',
                'target_users' => 'Actor synthetic pada environment local dan testing.',
                'main_features' => "Matriks role\nWorkflow ticket\nSafe projection\nVisual stress data",
                'integrations' => null, 'data_types' => 'Data synthetic tanpa PII.',
                'target_time' => '2026-12-01', 'target_time_reason' => 'Tanggal deterministic untuk baseline.',
            ],
            default => [],
        };
    }

    /**
     * @param  array<string, Ticket>  $tickets
     * @param  array<string, User>  $actors
     */
    private function applyWorkflows(array $tickets, array $actors): void
    {
        $t1 = $actors['ACT-03'];
        $t2 = $actors['ACT-04'];
        $currentApprover = User::query()->findOrFail(
            (int) ApproverAssignment::query()->active()->value('user_id'),
        );

        Carbon::setTestNow($this->at('2026-08-10 11:00:00'));
        $this->workflow->claim($actors['ACT-08'], $tickets['UI-TKT-T1-001']);
        $this->triageToTierTwo($t1, $t2, $tickets['UI-TKT-T2-001']);

        foreach ([
            'UI-TKT-APPROVAL-CURRENT-001',
            'UI-TKT-APPROVAL-STALE-001',
            'UI-TKT-WAIT-REQUESTER-001',
            'UI-TKT-WAIT-THIRD-PARTY-001',
            'UI-TKT-SVC02-RESULT-001',
            'UI-TKT-SVC02-BEFORE-001',
            'UI-TKT-SVC03-INCOMPLETE-001',
            'UI-TKT-SVC03-PREEXEC-001',
            'UI-TKT-SVC03-EXECUTED-001',
            'UI-TKT-SVC03-VERIFIED-001',
            'UI-TKT-SVC07-001',
            'UI-TKT-TEAM-IN-001',
            'UI-TKT-TEAM-OUT-001',
        ] as $id) {
            $this->triageToTierOne($t1, $tickets[$id]);
        }

        $this->approvals->request($t1, $tickets['UI-TKT-APPROVAL-CURRENT-001'], 'Persetujuan synthetic untuk current approver.');
        $this->approvals->request($t1, $tickets['UI-TKT-APPROVAL-STALE-001'], 'Approver stale harus ditolak oleh assignment check.');
        $this->waiting->requestInformation(
            $t1,
            $tickets['UI-TKT-WAIT-REQUESTER-001'],
            self::EXPECTED_COMMENTS[0],
        );
        $this->waiting->startThirdParty(
            $t1,
            $tickets['UI-TKT-WAIT-THIRD-PARTY-001'],
            'Vendor Synthetic UI Fixture',
            '2026-08-17',
            'Menunggu konfirmasi jadwal synthetic.',
        );

        $this->createAttachment(
            $tickets['UI-TKT-SVC02-RESULT-001'],
            $t1,
            'UI-ATT-03-data-export-result.csv',
            'data_export_result',
            'Hasil tarik data',
            'both',
            "fixture_id,status\nUI-ROW-001,valid\n",
            null,
            null,
            'text/csv',
        );
        $this->resolution->complete(
            $t1,
            $tickets['UI-TKT-SVC02-RESULT-001'],
            "Hasil tarik data synthetic sudah tersedia.\nSilakan verifikasi file CSV pada lampiran hasil.",
        );

        Carbon::setTestNow($this->at('2026-08-12 10:00:00'));
        $this->triageToTierOne($t1, $tickets['UI-TKT-CLOSED-ELIGIBLE-001']);
        $this->resolution->complete(
            $t1,
            $tickets['UI-TKT-CLOSED-ELIGIBLE-001'],
            "Solusi synthetic selesai.\nBaris kedua menguji tampilan multiline solution.",
        );
        $tickets['UI-TKT-CLOSED-ELIGIBLE-001']->refresh();
        $this->resolution->confirm($actors['ACT-02'], $tickets['UI-TKT-CLOSED-ELIGIBLE-001']);

        Carbon::setTestNow($this->at('2026-06-01 10:00:00'));
        $this->triageToTierOne($t1, $tickets['UI-TKT-CLOSED-INELIGIBLE-001']);
        $this->resolution->complete($t1, $tickets['UI-TKT-CLOSED-INELIGIBLE-001'], 'Solusi historical synthetic.');
        $tickets['UI-TKT-CLOSED-INELIGIBLE-001']->refresh();
        $this->resolution->confirm($actors['ACT-02'], $tickets['UI-TKT-CLOSED-INELIGIBLE-001']);

        Carbon::setTestNow($this->at('2026-08-10 13:00:00'));
        $this->cancellation->cancel($actors['ACT-02'], $tickets['UI-TKT-CANCELLED-001']);
        $this->workflow->reject($t1, $tickets['UI-TKT-REJECTED-001'], 'Tidak termasuk layanan yang didukung pada skenario synthetic.');
        $this->triageToTierOne($t1, $tickets['UI-TKT-APPROVAL-REJECTED-001']);
        $approval = $this->approvals->request($t1, $tickets['UI-TKT-APPROVAL-REJECTED-001'], 'Minta keputusan tidak setuju synthetic.');
        $this->approvals->reject($currentApprover, $approval, 'Tidak disetujui untuk kebutuhan baseline synthetic.');

        $this->addDatabaseEvidence($tickets['UI-TKT-SVC03-INCOMPLETE-001'], $t1, 'UI-SVC03-INCOMPLETE', ['change_script']);
        $this->addDatabaseEvidence($tickets['UI-TKT-SVC03-PREEXEC-001'], $t1, 'UI-SVC03-PREEXEC');
        $this->addDatabaseEvidence($tickets['UI-TKT-SVC03-EXECUTED-001'], $t1, 'UI-SVC03-EXECUTED');
        $this->databaseChanges->startExecution($t1, $tickets['UI-TKT-SVC03-EXECUTED-001']);
        $this->addDatabaseEvidence($tickets['UI-TKT-SVC03-VERIFIED-001'], $t1, 'UI-SVC03-VERIFIED');
        $this->databaseChanges->startExecution($t1, $tickets['UI-TKT-SVC03-VERIFIED-001']);
        $this->databaseChanges->verify(
            $t1,
            $tickets['UI-TKT-SVC03-VERIFIED-001'],
            'Perubahan synthetic berhasil dan checksum sesuai.',
            'Verifikasi dilakukan pada data fixture tanpa data nyata.',
        );

        $definitions = ServiceFieldDefinition::query()
            ->where('service_type_id', $tickets['UI-TKT-SVC07-001']->service_type_id)
            ->where('visibility', 'internal')
            ->where('is_active', true)
            ->get();
        $this->internalFields->update($t1, $tickets['UI-TKT-SVC07-001'], [
            'internal_fields' => [
                'ti_assessment' => 'Penilaian internal synthetic: layak masuk discovery.',
                'complexity' => 'high',
                'security_data_risk' => 'Risiko synthetic sedang; tidak ada data nyata.',
                'ti_priority' => 'critical',
                'planned_start' => '2026-09-01',
                'ti_owner' => 'UI Fixture Product Engineering Team dengan Nama PIC Panjang',
                'follow_up_notes' => "Discovery synthetic.\nEstimasi synthetic.\nReview keamanan synthetic.",
            ],
            'internal_field_versions' => $definitions
                ->mapWithKeys(fn (ServiceFieldDefinition $field): array => [$field->key => $field->version])
                ->all(),
        ]);
    }

    /**
     * @param  array<string, Ticket>  $tickets
     * @param  array<string, User>  $actors
     */
    private function createCommentsAndAttachments(array $tickets, array $actors): void
    {
        $t1 = $actors['ACT-03'];
        $svc07 = $tickets['UI-TKT-SVC07-001'];

        Carbon::setTestNow($this->at('2026-08-10 14:00:00'));
        $this->waiting->requestInformation($t1, $svc07, self::EXPECTED_COMMENTS[1]);
        Carbon::setTestNow($this->at('2026-08-10 14:10:00'));
        $this->waiting->requesterReply($actors['ACT-02'], $svc07, self::EXPECTED_COMMENTS[2]);
        Carbon::setTestNow($this->at('2026-08-10 14:20:00'));
        $public = $this->communication->publicReply($t1, $svc07, self::EXPECTED_COMMENTS[3]);
        Carbon::setTestNow($this->at('2026-08-10 14:30:00'));
        $internal = $this->communication->internalNote($t1, $svc07, self::EXPECTED_COMMENTS[4]);

        $this->createAttachment(
            $svc07,
            $t1,
            'UI-ATT-01-public-requester-visible.txt',
            'supporting',
            'Lampiran pendukung',
            'both',
            'ATT-01 synthetic public fixture.',
            $this->supportingPolicyId(),
            $public,
        );
        $this->createAttachment(
            $svc07,
            $t1,
            'UI-ATT-02-internal-agent-only.txt',
            'supporting',
            'Lampiran pendukung internal',
            'internal',
            'ATT-02 synthetic internal fixture.',
            $this->supportingPolicyId(),
            $internal,
        );
        $softDeleted = $this->createAttachment(
            $svc07,
            $t1,
            'UI-ATT-07-soft-deleted-retained.txt',
            'supporting',
            'Lampiran retained setelah soft delete',
            'internal',
            'ATT-07 retained file content.',
            $this->supportingPolicyId(),
        );
        $softDeleted->delete();

        $t2Ticket = $tickets['UI-TKT-T2-001'];
        $this->createAttachment(
            $t2Ticket,
            $actors['ACT-04'],
            'UI-ATT-04-assigned-tier-two.txt',
            'supporting',
            'Lampiran pendukung',
            'both',
            'ATT-04 assigned Tier 2 authorized fixture.',
            $this->supportingPolicyId(),
        );
        $this->createAttachment(
            $t2Ticket,
            $actors['ACT-04'],
            'UI-ATT-05-unrelated-tier-two-denied.txt',
            'supporting',
            'Lampiran internal Tier 2',
            'internal',
            'ATT-05 must be denied for unrelated Tier 2.',
            $this->supportingPolicyId(),
        );

        $teamIn = $tickets['UI-TKT-TEAM-IN-001'];
        Carbon::setTestNow($this->at('2026-08-10 15:00:00'));
        $teamPublic = $this->communication->publicReply($t1, $teamIn, self::EXPECTED_COMMENTS[5]);
        Carbon::setTestNow($this->at('2026-08-10 15:10:00'));
        $this->communication->internalNote($t1, $teamIn, self::EXPECTED_COMMENTS[6]);
        $this->createAttachment(
            $teamIn,
            $t1,
            'UI-ATT-06-team-chair-denied.txt',
            'supporting',
            'Lampiran pendukung',
            'both',
            'ATT-06 remains denied for every Team Chair.',
            $this->supportingPolicyId(),
            $teamPublic,
        );
    }

    private function triageToTierOne(User $actor, Ticket $ticket): void
    {
        $this->workflow->triage($actor, $ticket, [
            'outcome' => 'self',
            'priority' => $ticket->priority->value,
        ]);
        $ticket->refresh();
    }

    private function triageToTierTwo(User $actor, User $tierTwo, Ticket $ticket): void
    {
        $this->workflow->triage($actor, $ticket, [
            'outcome' => 'tier_2',
            'priority' => $ticket->priority->value,
            'assigned_to_id' => $tierTwo->getKey(),
        ]);
        $ticket->refresh();
    }

    /** @param list<string>|null $types */
    private function addDatabaseEvidence(Ticket $ticket, User $actor, string $prefix, ?array $types = null): void
    {
        $types ??= DatabaseChangeControlService::REQUIRED_EVIDENCE_TYPES;
        $labels = [
            'change_script' => 'Change script',
            'rollback_script' => 'Rollback script',
            'backup_evidence' => 'Bukti backup',
        ];

        foreach ($types as $type) {
            $extension = str_contains($type, 'script') ? 'sql' : 'txt';
            $policy = AttachmentPolicy::query()
                ->where('service_type_id', $ticket->service_type_id)
                ->where('type_key', $type)
                ->firstOrFail();
            $this->createAttachment(
                $ticket,
                $actor,
                "{$prefix}-".str_replace('_', '-', $type).".{$extension}",
                $type,
                $labels[$type],
                'internal',
                "-- {$prefix} {$type} synthetic evidence\nSELECT 1;\n",
                (int) $policy->getKey(),
                null,
                'text/plain',
            );
        }
    }

    private function createAttachment(
        Ticket $ticket,
        User $actor,
        string $originalName,
        string $typeKey,
        string $typeLabel,
        string $visibility,
        string $content,
        ?int $policyId = null,
        ?TicketComment $comment = null,
        string $mime = 'text/plain',
    ): Attachment {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $disk = (string) config('filesystems.attachment_disk', 'local');
        $path = 'ui-baseline/'.$ticket->ticket_number.'/'.$originalName;
        Storage::disk($disk)->put($path, $content);

        return Attachment::query()->create([
            'ticket_id' => $ticket->getKey(),
            'ticket_comment_id' => $comment?->getKey(),
            'attachment_policy_id' => $policyId,
            'uploaded_by_id' => $actor->getKey(),
            'type_key' => $typeKey,
            'type_label_snapshot' => $typeLabel,
            'original_name' => $originalName,
            'storage_disk' => $disk,
            'storage_path' => $path,
            'size_bytes' => strlen($content),
            'mime_type' => $mime,
            'extension' => $extension !== '' ? $extension : null,
            'sha256' => hash('sha256', $content),
            'visibility' => $visibility,
        ]);
    }

    private function supportingPolicyId(): int
    {
        return (int) AttachmentPolicy::query()
            ->whereNull('service_type_id')
            ->where('type_key', 'supporting')
            ->value('id');
    }

    /** @param array<string, mixed> $definition */
    private function subject(string $id, array $definition): string
    {
        return "[UI-FIXTURE:{$id}|{$definition['aliases']}] {$definition['title']}";
    }

    private function hasFixtureMarker(): bool
    {
        return User::withTrashed()->whereIn('username', $this->expectedUsernames())->exists()
            || Ticket::query()->whereIn('subject', $this->expectedSubjects())->exists()
            || WorkTeam::withTrashed()->whereIn('name', $this->expectedTeamNames())->exists()
            || Skill::withTrashed()->where('slug', 'ui-fixture-database-infrastructure')->exists()
            || Building::withTrashed()->where('name', 'Gedung Uji A')->exists();
    }

    private function assertDisposableDatabase(): void
    {
        $occupied = [
            'users' => User::withTrashed()->count(),
            'tickets' => Ticket::query()->count(),
            'work teams' => WorkTeam::withTrashed()->count(),
            'team memberships' => TeamMembership::query()->count(),
            'team chair assignments' => TeamChairAssignment::query()->count(),
            'approver assignments' => ApproverAssignment::query()->count(),
            'buildings' => Building::withTrashed()->count(),
            'skills' => Skill::withTrashed()->count(),
        ];
        $nonEmpty = collect($occupied)->filter(fn (int $count): bool => $count > 0);

        if ($nonEmpty->isNotEmpty()) {
            $details = $nonEmpty->map(fn (int $count, string $name): string => "{$name}={$count}")->implode(', ');

            throw new RuntimeException(
                "UI baseline fixture memerlukan database local/testing disposable tanpa data domain ({$details}). ".
                'Gunakan database terpisah atau reset database disposable; data existing tidak diubah.',
            );
        }

        if (Ticket::query()->whereIn('ticket_number', $this->expectedTicketNumbers())->exists()) {
            throw new RuntimeException('Nomor tiket UI fixture bertabrakan dengan data existing.');
        }
    }

    private function manifestIsComplete(string $approverProfile): bool
    {
        $users = User::query()->with('roles')->whereIn('username', $this->expectedUsernames())->get()->keyBy('username');

        if ($users->count() !== self::ACTOR_COUNT) {
            return false;
        }

        foreach (self::ACTORS as $definition) {
            $actual = $users->get($definition['username'])?->roles->pluck('slug')->sort()->values()->all();
            $expected = collect($definition['roles'])->sort()->values()->all();

            if ($actual !== $expected) {
                return false;
            }
        }

        $tickets = Ticket::query()->with('assignee')->whereIn('subject', $this->expectedSubjects())->get();
        $ticketIds = $tickets->modelKeys();
        $currentUsername = ApproverAssignment::query()->active()->with('user')->first()?->user?->username;
        $staleExists = ApproverAssignment::query()
            ->where('user_id', $users->get('ui_test_approver_stale')?->getKey())
            ->where('is_active', false)
            ->whereNotNull('ended_at')
            ->exists();
        $attachments = Attachment::withTrashed()
            ->whereIn('ticket_id', $ticketIds)
            ->whereIn('original_name', self::EXPECTED_ATTACHMENTS)
            ->get();

        if ($tickets->count() !== self::TICKET_COUNT) {
            return false;
        }

        $ticketsByNumber = $tickets->keyBy('ticket_number');

        foreach (self::EXPECTED_TICKET_STATES as $id => $expected) {
            $ticket = $ticketsByNumber->get(self::TICKETS[$id]['number']);

            if ($ticket?->status?->value !== $expected['status']
                || $ticket?->assignee?->username !== $expected['assignee']) {
                return false;
            }
        }

        return WorkTeam::query()->whereIn('name', $this->expectedTeamNames())->count() === 3
            && TeamChairAssignment::query()->where('is_active', true)->count() === 3
            && $currentUsername === self::APPROVER_PROFILES[$approverProfile]
            && $staleExists
            && TicketComment::query()->whereIn('ticket_id', $ticketIds)->whereIn('body', self::EXPECTED_COMMENTS)->count() === count(self::EXPECTED_COMMENTS)
            && $attachments->count() === count(self::EXPECTED_ATTACHMENTS)
            && $attachments->every(fn (Attachment $attachment): bool => Storage::disk($attachment->storage_disk)->exists($attachment->storage_path))
            && $tickets->pluck('status')->map(fn (TicketStatus $status): string => $status->value)->unique()->count() === count(TicketStatus::cases())
            && $tickets->pluck('priority')->map(fn (Priority $priority): string => $priority->value)->unique()->count() === count(Priority::cases());
    }

    /** @return array<string, int|string|bool> */
    private function summary(string $approverProfile, bool $existing): array
    {
        $tickets = Ticket::query()->whereIn('subject', $this->expectedSubjects())->get();
        $ticketIds = $tickets->modelKeys();

        return [
            'existing' => $existing,
            'approver_profile' => $approverProfile,
            'current_approver' => self::APPROVER_PROFILES[$approverProfile],
            'actors' => User::query()->whereIn('username', $this->expectedUsernames())->count(),
            'teams' => WorkTeam::query()->whereIn('name', $this->expectedTeamNames())->count(),
            'tickets' => $tickets->count(),
            'statuses' => $tickets->pluck('status')->unique()->count(),
            'priorities' => $tickets->pluck('priority')->unique()->count(),
            'services' => $tickets->pluck('service_type_id')->unique()->count(),
            'comments' => TicketComment::query()->whereIn('ticket_id', $ticketIds)->whereIn('body', self::EXPECTED_COMMENTS)->count(),
            'attachments' => Attachment::withTrashed()->whereIn('ticket_id', $ticketIds)->whereIn('original_name', self::EXPECTED_ATTACHMENTS)->count(),
        ];
    }

    /** @return list<string> */
    private function expectedUsernames(): array
    {
        return array_column(self::ACTORS, 'username');
    }

    /** @return list<string> */
    private function expectedSubjects(): array
    {
        return collect(self::TICKETS)
            ->map(fn (array $definition, string $id): string => $this->subject($id, $definition))
            ->values()
            ->all();
    }

    /** @return list<string> */
    private function expectedTicketNumbers(): array
    {
        return array_column(self::TICKETS, 'number');
    }

    /** @return list<string> */
    private function expectedTeamNames(): array
    {
        return ['UI Fixture TEAM-A', 'UI Fixture TEAM-B', 'UI Fixture TEAM-C'];
    }

    private function at(string $time): Carbon
    {
        return Carbon::parse($time, (string) config('app.timezone', 'Asia/Jakarta'));
    }
}
