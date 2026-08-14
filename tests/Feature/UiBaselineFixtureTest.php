<?php

namespace Tests\Feature;

use App\Enums\Priority;
use App\Enums\Role;
use App\Enums\TicketStatus;
use App\Models\ApprovalRequest;
use App\Models\ApproverAssignment;
use App\Models\Attachment;
use App\Models\DatabaseChangeControl;
use App\Models\TeamChairAssignment;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\TicketFieldValue;
use App\Models\User;
use App\Models\WorkTeam;
use App\Policies\ReportExportPolicy;
use App\Services\ApproverAssignmentService;
use App\Services\OperationalPolicyService;
use App\Services\TeamChairTicketProjection;
use App\Services\WorkingCalendarService;
use Database\Seeders\UiBaselineFixtureSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UiBaselineFixtureTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake((string) config('filesystems.attachment_disk', 'local'));
    }

    public function test_command_is_rejected_outside_local_or_testing_environment(): void
    {
        $originalEnvironment = $this->app['env'];
        $this->app->instance('env', 'production');
        $directSeederException = null;

        try {
            $exitCode = Artisan::call('app:seed-ui-baseline');
            $output = Artisan::output();

            try {
                app(UiBaselineFixtureSeeder::class)->seed();
            } catch (\RuntimeException $exception) {
                $directSeederException = $exception->getMessage();
            }
        } finally {
            $this->app->instance('env', $originalEnvironment);
        }

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('hanya boleh dijalankan pada environment local atau testing', $output);
        $this->assertStringContainsString('hanya boleh dijalankan pada environment local atau testing', (string) $directSeederException);
        $this->assertSame(0, User::query()->count());
        $this->assertSame(0, Ticket::query()->count());
    }

    public function test_command_refuses_to_mix_fixtures_into_an_occupied_database(): void
    {
        $existing = $this->createUser([Role::Pemohon], ['username' => 'existing_local_user']);

        $exitCode = Artisan::call('app:seed-ui-baseline');

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('database local/testing disposable', Artisan::output());
        $this->assertSame(1, User::query()->count());
        $this->assertSame($existing->getKey(), User::query()->sole()->getKey());
        $this->assertSame(0, Ticket::query()->count());
    }

    public function test_default_fixture_pack_covers_contracts_and_rerun_is_idempotent(): void
    {
        $this->assertSame(0, Artisan::call('app:seed-ui-baseline'));
        $this->assertStringContainsString('berhasil dibuat', Artisan::output());

        $actors = User::query()
            ->with('roles')
            ->whereIn('username', array_column(UiBaselineFixtureSeeder::ACTORS, 'username'))
            ->get()
            ->keyBy('username');

        $this->assertCount(UiBaselineFixtureSeeder::ACTOR_COUNT, $actors);
        $this->assertTrue(Hash::check(
            UiBaselineFixtureSeeder::DEFAULT_PASSWORD,
            $actors->get('ui_test_super_admin')->password,
        ));

        foreach (UiBaselineFixtureSeeder::ACTORS as $definition) {
            $this->assertSame(
                collect($definition['roles'])->sort()->values()->all(),
                $actors->get($definition['username'])->roles->pluck('slug')->sort()->values()->all(),
                $definition['username'],
            );
        }

        $tickets = Ticket::query()
            ->where('subject', 'like', '[UI-FIXTURE:%')
            ->get();
        $this->assertCount(UiBaselineFixtureSeeder::TICKET_COUNT, $tickets);
        $this->assertEqualsCanonicalizing(
            array_map(fn (TicketStatus $status): string => $status->value, TicketStatus::cases()),
            $tickets->map(fn (Ticket $ticket): string => $ticket->status->value)->unique()->all(),
        );
        $this->assertEqualsCanonicalizing(
            array_map(fn (Priority $priority): string => $priority->value, Priority::cases()),
            $tickets->map(fn (Ticket $ticket): string => $ticket->priority->value)->unique()->all(),
        );
        $this->assertEqualsCanonicalizing(
            ['SVC-01', 'SVC-02', 'SVC-03', 'SVC-04', 'SVC-05', 'SVC-06', 'SVC-07'],
            $tickets->pluck('service_type_code_snapshot')->unique()->all(),
        );

        $this->assertSame(3, WorkTeam::query()->where('name', 'like', 'UI Fixture TEAM-%')->count());
        $this->assertSame(3, TeamChairAssignment::query()->where('is_active', true)->count());
        $this->assertDatabaseHas('buildings', ['name' => 'Gedung Uji A', 'is_active' => true]);
        $this->assertDatabaseHas('floors', ['name' => 'Lantai 1 — UI Fixture', 'is_active' => true]);

        $approvers = app(ApproverAssignmentService::class);
        $current = $actors->get('ui_test_approver_current');
        $stale = $actors->get('ui_test_approver_stale');
        $this->assertTrue($approvers->isCurrentApprover($current));
        $this->assertFalse($approvers->isCurrentApprover($stale));
        $this->assertDatabaseHas('approver_assignments', [
            'user_id' => $stale->getKey(),
            'is_active' => false,
        ]);

        $pending = ApprovalRequest::query()->pending()->firstOrFail();
        $this->assertTrue(Gate::forUser($current)->allows('decide', $pending));
        $this->assertFalse(Gate::forUser($stale)->allows('decide', $pending));

        $chair = $actors->get('ui_test_team_chair');
        $inScope = $this->fixtureTicket('UI-TKT-TEAM-IN-001');
        $outsideScope = $this->fixtureTicket('UI-TKT-TEAM-OUT-001');
        $this->assertTrue(Gate::forUser($chair)->allows('view', $inScope));
        $this->assertFalse(Gate::forUser($chair)->allows('view', $outsideScope));

        $projection = app(TeamChairTicketProjection::class);
        $safeTicket = $projection->find($chair, (int) $inScope->getKey());
        $this->assertNotNull($safeTicket);
        $this->assertNull($projection->find($chair, (int) $outsideScope->getKey()));
        $this->assertFalse(property_exists($safeTicket, 'description'));
        $this->assertFalse(property_exists($safeTicket, 'attachments'));
        $this->assertFalse(property_exists($safeTicket, 'internalFieldValues'));
        $this->assertTrue($safeTicket->publicComments->contains(
            fn ($comment): bool => str_contains($comment->body, 'UI-CMT-TEAM-PUBLIC'),
        ));
        $this->assertFalse($safeTicket->publicComments->contains(
            fn ($comment): bool => str_contains($comment->body, 'UI-CMT-TEAM-INTERNAL'),
        ));

        $svc07 = $this->fixtureTicket('UI-TKT-SVC07-001');
        $this->assertSame(7, TicketFieldValue::query()
            ->where('ticket_id', $svc07->getKey())
            ->where('visibility_snapshot', 'internal')
            ->count());
        $this->assertSame(7, TicketFieldValue::query()
            ->where('ticket_id', $svc07->getKey())
            ->where('visibility_snapshot', 'internal')
            ->where('version_snapshot', 1)
            ->count());

        $preExecution = $this->fixtureTicket('UI-TKT-SVC03-PREEXEC-001');
        $executed = $this->fixtureTicket('UI-TKT-SVC03-EXECUTED-001');
        $verified = $this->fixtureTicket('UI-TKT-SVC03-VERIFIED-001');
        $this->assertDatabaseMissing('database_change_controls', ['ticket_id' => $preExecution->getKey()]);
        $executedControl = DatabaseChangeControl::query()->where('ticket_id', $executed->getKey())->firstOrFail();
        $verifiedControl = DatabaseChangeControl::query()->where('ticket_id', $verified->getKey())->firstOrFail();
        $this->assertTrue($executedControl->executionStarted());
        $this->assertFalse($executedControl->verified());
        $this->assertTrue($verifiedControl->executionStarted());
        $this->assertTrue($verifiedControl->verified());

        $svc02Before = $this->fixtureTicket('UI-TKT-SVC02-BEFORE-001');
        $svc02After = $this->fixtureTicket('UI-TKT-SVC02-RESULT-001');
        $this->assertSame(TicketStatus::Dikerjakan, $svc02Before->status);
        $this->assertFalse($svc02Before->attachments()->where('type_key', Attachment::DATA_EXPORT_RESULT_TYPE)->exists());
        $this->assertSame(TicketStatus::MenungguKonfirmasi, $svc02After->status);
        $this->assertTrue($svc02After->attachments()->where('type_key', Attachment::DATA_EXPORT_RESULT_TYPE)->exists());

        $eligible = $this->fixtureTicket('UI-TKT-CLOSED-ELIGIBLE-001');
        $ineligible = $this->fixtureTicket('UI-TKT-CLOSED-INELIGIBLE-001');
        $operationalPolicies = app(OperationalPolicyService::class);
        $calendar = $operationalPolicies->currentCalendar();
        $windowDays = (int) $operationalPolicies->settings()['reopen_window_working_days'];
        $baselineNow = Carbon::parse('2026-08-13 10:00:00', 'Asia/Jakarta');
        $this->assertTrue(app(WorkingCalendarService::class)->isWithinWorkingDays(
            $eligible->closed_at,
            $baselineNow,
            $windowDays,
            $calendar,
        ));
        $this->assertFalse(app(WorkingCalendarService::class)->isWithinWorkingDays(
            $ineligible->closed_at,
            $baselineNow,
            $windowDays,
            $calendar,
        ));

        $tierTwo = $actors->get('ui_test_agent_t2');
        $unrelatedTierTwo = $actors->get('ui_test_agent_t2_unrelated');
        $assignedAttachment = Attachment::query()->where('original_name', 'UI-ATT-04-assigned-tier-two.txt')->firstOrFail();
        $unrelatedDenied = Attachment::query()->where('original_name', 'UI-ATT-05-unrelated-tier-two-denied.txt')->firstOrFail();
        $chairDenied = Attachment::query()->where('original_name', 'UI-ATT-06-team-chair-denied.txt')->firstOrFail();
        $this->assertTrue(Gate::forUser($tierTwo)->allows('view', $assignedAttachment));
        $this->assertFalse(Gate::forUser($unrelatedTierTwo)->allows('view', $unrelatedDenied));
        $this->assertFalse(Gate::forUser($chair)->allows('view', $chairDenied));

        $retained = Attachment::withTrashed()->where('original_name', 'UI-ATT-07-soft-deleted-retained.txt')->firstOrFail();
        $this->assertTrue($retained->trashed());
        Storage::disk($retained->storage_disk)->assertExists($retained->storage_path);

        $act08 = $actors->get('ui_test_admin_t1');
        $act09 = $actors->get('ui_test_requester_approver');
        $act10 = $actors->get('ui_test_team_chair_t1');
        $act11 = $actors->get('ui_test_team_chair_admin');
        $this->assertTrue(Gate::forUser($act08)->allows('viewAny', User::class));
        $this->assertTrue(Gate::forUser($act08)->allows('viewQueue', Ticket::class));
        $this->actingAs($act09)->get(route('tickets.index'))->assertOk()->assertViewIs('tickets.requester-index');
        $this->assertFalse(Gate::forUser($act10)->allows('handle', $outsideScope));
        $this->assertTrue(Gate::forUser($act10)->allows('view', $outsideScope));
        $teamC = $this->fixtureTicket('UI-TKT-TEAM-MULTI-001');
        $this->assertTrue(Gate::forUser($act11)->allows('viewAny', User::class));
        $this->assertTrue(Gate::forUser($act11)->allows('view', $teamC));
        $this->assertFalse(app(ReportExportPolicy::class)->viewAny($act11));

        $this->assertSame(7, TicketComment::query()
            ->whereIn('ticket_id', $tickets->modelKeys())
            ->where('body', 'like', '[UI-CMT-%')
            ->count());
        $this->assertSame(17, Attachment::withTrashed()
            ->whereIn('ticket_id', $tickets->modelKeys())
            ->where('original_name', 'like', 'UI-%')
            ->count());

        $countsBefore = $this->fixtureCounts();
        $this->assertSame(0, Artisan::call('app:seed-ui-baseline'));
        $this->assertStringContainsString('sudah tersedia; tidak ada data ditulis ulang', Artisan::output());
        $this->assertSame($countsBefore, $this->fixtureCounts());
    }

    public function test_act09_profile_provides_the_mutually_exclusive_multi_role_current_approver(): void
    {
        $this->assertSame(0, Artisan::call('app:seed-ui-baseline', ['--current-approver' => 'ACT-09']));

        $current = User::query()->where('username', 'ui_test_requester_approver')->firstOrFail();
        $pureApprover = User::query()->where('username', 'ui_test_approver_current')->firstOrFail();
        $stale = User::query()->where('username', 'ui_test_approver_stale')->firstOrFail();
        $service = app(ApproverAssignmentService::class);

        $this->assertTrue($service->isCurrentApprover($current));
        $this->assertFalse($service->isCurrentApprover($pureApprover));
        $this->assertFalse($service->isCurrentApprover($stale));
        $this->assertTrue($current->hasRole(Role::Pemohon));
        $this->assertTrue($current->hasRole(Role::Approver));
        $this->assertSame(2, ApprovalRequest::query()->pending()->where('approver_id', $current->getKey())->count());
        $this->actingAs($current)->get(route('tickets.index'))->assertOk()->assertViewIs('tickets.requester-index');
    }

    private function fixtureTicket(string $id): Ticket
    {
        return Ticket::query()->where('subject', 'like', "%{$id}%")->firstOrFail();
    }

    /** @return array<string, int> */
    private function fixtureCounts(): array
    {
        return [
            'users' => User::withTrashed()->count(),
            'roles' => DB::table('roles')->count(),
            'user_roles' => DB::table('user_roles')->count(),
            'teams' => WorkTeam::withTrashed()->count(),
            'memberships' => DB::table('team_memberships')->count(),
            'chairs' => DB::table('team_chair_assignments')->count(),
            'approver_assignments' => ApproverAssignment::query()->count(),
            'tickets' => Ticket::query()->count(),
            'status_histories' => DB::table('ticket_status_histories')->count(),
            'approval_requests' => ApprovalRequest::query()->count(),
            'comments' => TicketComment::query()->count(),
            'attachments' => Attachment::withTrashed()->count(),
            'internal_fields' => TicketFieldValue::query()->where('visibility_snapshot', 'internal')->count(),
            'audit_logs' => DB::table('audit_logs')->count(),
            'notifications' => DB::table('notifications')->count(),
        ];
    }
}
