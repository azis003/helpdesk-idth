<?php

namespace Tests\Feature;

use App\Enums\Priority;
use App\Enums\Role;
use App\Enums\TicketStatus;
use App\Models\ServiceFieldDefinition;
use App\Models\ServiceType;
use App\Models\Ticket;
use App\Models\TicketFieldValue;
use App\Models\TicketFieldValueHistory;
use Database\Seeders\OperationalPolicySeeder;
use Database\Seeders\ServiceCatalogSeeder;
use Tests\TestCase;

class TicketInternalFieldTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ServiceCatalogSeeder::class);
        $this->seed(OperationalPolicySeeder::class);
    }

    public function test_assigned_tier_one_can_save_svc07_internal_fields_with_snapshots_and_history(): void
    {
        $requester = $this->createUser([Role::Pemohon]);
        $agent = $this->createUser([Role::AgenTier1]);
        $ticket = $this->internalTicket($requester->id, $agent->id, Role::AgenTier1);

        $this->actingAs($agent)
            ->put(route('tickets.internal-fields.update', $ticket), $this->payload([
                'ti_assessment' => 'Analisis internal rahasia',
                'complexity' => 'medium',
                'security_data_risk' => 'Risiko data sedang.',
                'ti_priority' => 'high',
                'planned_start' => '2026-08-20',
                'ti_owner' => 'Tim Aplikasi',
                'follow_up_notes' => 'Susun discovery dan estimasi.',
            ]))
            ->assertRedirect(route('tickets.show', $ticket))
            ->assertSessionHasNoErrors();

        $field = TicketFieldValue::query()
            ->where('ticket_id', $ticket->id)
            ->where('field_key', 'ti_assessment')
            ->firstOrFail();

        $this->assertSame('Analisis internal rahasia', $field->value);
        $this->assertSame('internal', $field->visibility_snapshot);
        $this->assertSame(1, $field->version_snapshot);
        $this->assertDatabaseHas('ticket_field_value_histories', [
            'ticket_id' => $ticket->id,
            'field_key' => 'ti_assessment',
            'change_type' => 'created',
            'actor_id' => $agent->id,
            'version_snapshot' => 1,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $agent->id,
            'action' => 'ticket.internal_fields.updated',
            'outcome' => 'succeeded',
        ]);

        $this->actingAs($agent)
            ->get(route('tickets.show', $ticket))
            ->assertOk()
            ->assertSee('Bagian internal SVC-07')
            ->assertSee('Analisis internal rahasia');

        $this->actingAs($requester)
            ->get(route('tickets.show', $ticket))
            ->assertOk()
            ->assertDontSee('Bagian internal SVC-07')
            ->assertDontSee('Verifikasi dan penilaian')
            ->assertDontSee('Analisis internal rahasia');

        $this->actingAs($requester)
            ->get(route('reports.index'))
            ->assertForbidden();

        $admin = $this->createUser([Role::SuperAdmin]);
        $this->actingAs($admin)
            ->get(route('reports.index'))
            ->assertOk()
            ->assertDontSee('Analisis internal rahasia');
    }

    public function test_only_assigned_ti_agents_can_update_and_team_chair_or_requester_is_denied(): void
    {
        $requester = $this->createUser([Role::Pemohon]);
        $tierOne = $this->createUser([Role::AgenTier1]);
        $tierTwo = $this->createUser([Role::AgenTier2]);
        $teamChair = $this->createUser([Role::KetuaTimKerja]);
        $tierOneTicket = $this->internalTicket($requester->id, $tierOne->id, Role::AgenTier1);
        $tierTwoTicket = $this->internalTicket($requester->id, $tierTwo->id, Role::AgenTier2);

        $this->actingAs($tierTwo)
            ->put(route('tickets.internal-fields.update', $tierTwoTicket), $this->payload([
                'ti_assessment' => 'Penilaian dari Tier 2.',
            ]))
            ->assertRedirect(route('tickets.show', $tierTwoTicket))
            ->assertSessionHasNoErrors();

        foreach ([$requester, $teamChair] as $actor) {
            $this->actingAs($actor)
                ->put(route('tickets.internal-fields.update', $tierOneTicket), $this->payload([
                    'ti_assessment' => 'Tidak boleh disimpan.',
                ]))
                ->assertForbidden();
        }

        $this->assertDatabaseMissing('ticket_field_values', [
            'ticket_id' => $tierOneTicket->id,
            'field_key' => 'ti_assessment',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'ticket.internal_fields.update',
            'outcome' => 'denied',
        ]);
    }

    public function test_internal_field_validation_and_stale_definition_version_are_rejected(): void
    {
        $requester = $this->createUser([Role::Pemohon]);
        $agent = $this->createUser([Role::AgenTier1]);
        $admin = $this->createUser([Role::SuperAdmin]);
        $ticket = $this->internalTicket($requester->id, $agent->id, Role::AgenTier1);

        $this->actingAs($agent)
            ->put(route('tickets.internal-fields.update', $ticket), $this->payload([
                'complexity' => 'mustahil',
            ]))
            ->assertRedirect()
            ->assertSessionHasErrors('internal_fields.complexity');

        $this->actingAs($agent)
            ->put(route('tickets.internal-fields.update', $ticket), [
                'internal_fields' => ['ti_priority' => 'high'],
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('internal_fields.ti_priority');

        $this->assertDatabaseMissing('ticket_field_values', [
            'ticket_id' => $ticket->id,
            'field_key' => 'complexity',
        ]);

        $this->actingAs($agent)
            ->put(route('tickets.internal-fields.update', $ticket), $this->payload([
                'ti_priority' => 'high',
            ]))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $versionsV1 = $this->internalVersions($ticket->serviceType()->with('activeFieldDefinitions')->firstOrFail());

        $priorityField = ServiceFieldDefinition::query()
            ->where('service_type_id', $ticket->service_type_id)
            ->where('key', 'ti_priority')
            ->where('version', 1)
            ->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.catalog.fields.versions.store', $priorityField), [
                'key' => $priorityField->key,
                'label' => 'Prioritas Tim TI terbaru',
                'field_type' => 'select',
                'visibility' => 'internal',
                'sort_order' => $priorityField->sort_order,
                'options_text' => "critical|Kritis\nhigh|Tinggi\nmedium|Sedang\nlow|Rendah",
                'validation_rules_text' => '',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->actingAs($agent)
            ->put(route('tickets.internal-fields.update', $ticket), [
                'internal_fields' => ['ti_priority' => 'critical'],
                'internal_field_versions' => ['ti_priority' => $versionsV1['ti_priority']],
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('internal_fields.ti_priority');

        $this->assertDatabaseHas('ticket_field_values', [
            'ticket_id' => $ticket->id,
            'field_key' => 'ti_priority',
            'version_snapshot' => 1,
        ]);

        $versions = $this->internalVersions($ticket->serviceType()->with('activeFieldDefinitions')->firstOrFail());
        $this->actingAs($agent)
            ->put(route('tickets.internal-fields.update', $ticket), [
                'internal_fields' => ['ti_priority' => 'critical'],
                'internal_field_versions' => ['ti_priority' => $versions['ti_priority']],
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('ticket_field_values', [
            'ticket_id' => $ticket->id,
            'field_key' => 'ti_priority',
            'label_snapshot' => 'Prioritas Tim TI terbaru',
            'version_snapshot' => 2,
        ]);
        $this->assertSame(2, TicketFieldValueHistory::query()->where('ticket_id', $ticket->id)->where('field_key', 'ti_priority')->count());
    }

    /** @param array<string, mixed> $fields */
    private function payload(array $fields): array
    {
        $service = ServiceType::query()->where('code', 'SVC-07')->with('activeFieldDefinitions')->firstOrFail();

        return [
            'internal_fields' => $fields,
            'internal_field_versions' => $this->internalVersions($service),
        ];
    }

    /** @return array<string, int> */
    private function internalVersions(ServiceType $service): array
    {
        return $service->activeFieldDefinitions
            ->where('visibility', 'internal')
            ->mapWithKeys(fn (ServiceFieldDefinition $field): array => [$field->key => $field->version])
            ->all();
    }

    private function internalTicket(int $requesterId, int $assigneeId, Role $tier): Ticket
    {
        $service = ServiceType::query()->where('code', 'SVC-07')->firstOrFail();

        return Ticket::factory()->create([
            'ticket_number' => 'CHG-2026-'.str_pad((string) (Ticket::query()->count() + 1), 5, '0', STR_PAD_LEFT),
            'ticket_class' => 'CHG',
            'ticket_year' => 2026,
            'ticket_sequence' => Ticket::query()->count() + 1,
            'subject' => 'Usulan sistem baru',
            'requester_id' => $requesterId,
            'created_by_id' => $requesterId,
            'service_type_id' => $service->id,
            'service_type_code_snapshot' => $service->code,
            'service_type_name_snapshot' => $service->name,
            'priority' => Priority::Sedang,
            'description' => 'Usulan aplikasi internal.',
            'status' => TicketStatus::Dikerjakan,
            'assigned_to_id' => $assigneeId,
            'assigned_tier' => $tier->value,
            'submitted_at' => now(),
        ]);
    }
}
