<?php

namespace App\Services;

use App\Enums\Priority;
use App\Enums\Role;
use App\Enums\TicketStatus;
use App\Models\Room;
use App\Models\ServiceType;
use App\Models\ServiceTypeVariant;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class TicketCreationService
{
    public function __construct(
        private readonly DatabaseManager $database,
        private readonly DomainAuthorization $authorization,
        private readonly AuditLogger $auditLogger,
        private readonly TicketNumberAllocator $numberAllocator,
        private readonly DynamicFieldValidator $fieldValidator,
        private readonly TicketAttachmentService $attachmentService,
        private readonly TicketSlaService $sla,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int|string, mixed>  $fileGroups
     */
    public function create(User $actor, array $data, array $fileGroups = []): Ticket
    {
        $requester = $this->resolveRequester($actor, $data['requester_id'] ?? null);
        $serviceType = $this->resolveServiceType($data['service_type_id'] ?? null);
        $fields = $this->fieldValidator->validate($serviceType, is_array($data['fields'] ?? null) ? $data['fields'] : []);
        [$ticketClass, $variant] = $this->resolveTicketClass($serviceType, $fields);
        $room = $this->resolveRoom($data['room_id'] ?? null, $serviceType);
        $priority = $this->resolvePriority($data['priority'] ?? null);
        $policies = $this->attachmentService->policiesFor(
            $serviceType,
            $actor->hasRole(Role::AgenTier1),
        );
        $attachments = $this->attachmentService->validate($fileGroups, $policies);
        $now = Carbon::now(config('app.timezone'));

        return $this->database->transaction(function () use (
            $actor,
            $requester,
            $serviceType,
            $fields,
            $ticketClass,
            $variant,
            $room,
            $priority,
            $attachments,
            $data,
            $now,
        ): Ticket {
            $ticketYear = $now->year;
            $number = $this->numberAllocator->next($ticketClass, $ticketYear);
            $isSelfCreated = (int) $actor->getKey() === (int) $requester->getKey();

            $ticket = Ticket::query()->create([
                'ticket_number' => $number['number'],
                'ticket_class' => $ticketClass,
                'ticket_year' => $ticketYear,
                'ticket_sequence' => $number['sequence'],
                'subject' => trim((string) $data['subject']),
                'requester_id' => $requester->getKey(),
                'created_by_id' => $actor->getKey(),
                'requester_name_snapshot' => $requester->name,
                'requester_nip_snapshot' => $requester->nip,
                'requester_team_snapshot' => $requester->currentTeamMembership?->workTeam?->name,
                'created_by_name_snapshot' => $actor->name,
                'is_self_created' => $isSelfCreated,
                'status' => TicketStatus::Baru,
                'assigned_to_id' => null,
                'assigned_tier' => null,
                'service_type_id' => $serviceType->getKey(),
                'service_type_variant_id' => $variant?->getKey(),
                'service_type_code_snapshot' => $serviceType->code,
                'service_type_name_snapshot' => $serviceType->name,
                'service_type_variant_code_snapshot' => $variant?->code,
                'service_type_variant_label_snapshot' => $variant?->label,
                'priority' => $priority,
                'description' => trim((string) $data['description']),
                'room_id' => $room?->getKey(),
                'building_name_snapshot' => $room?->floor?->building?->name,
                'floor_name_snapshot' => $room?->floor?->name,
                'room_name_snapshot' => $room?->name,
                'submitted_at' => $now,
            ]);

            $ticket->statusHistories()->create([
                'from_status' => null,
                'to_status' => TicketStatus::Baru->value,
                'action' => 'ticket.created',
                'actor_id' => $actor->getKey(),
                'metadata' => ['source' => 'ticket_creation'],
                'occurred_at' => $now,
            ]);

            $this->sla->start($ticket, $now);

            foreach ($fields as $field) {
                $definition = $field['definition'];
                $ticket->fieldValues()->create([
                    'service_field_definition_id' => $definition->getKey(),
                    'field_key' => $definition->key,
                    'label_snapshot' => $definition->label,
                    'field_type_snapshot' => $definition->field_type,
                    'version_snapshot' => $definition->version,
                    'value' => $field['value'],
                ]);
            }

            $this->attachmentService->store($ticket, $actor, $attachments);
            $ticket->load(['requester', 'creator', 'serviceType', 'serviceTypeVariant', 'room.floor.building', 'fieldValues', 'attachments']);

            $this->auditLogger->succeeded(
                $actor,
                'ticket.created',
                $ticket,
                'Tiket baru dibuat.',
                null,
                $this->snapshot($ticket),
            );

            return $ticket;
        });
    }

    private function resolveRequester(User $actor, mixed $requesterId): User
    {
        $requesterId = $requesterId === null || $requesterId === '' ? $actor->getKey() : (int) $requesterId;
        $requester = User::query()->with('currentTeamMembership.workTeam')->find($requesterId);

        if ($requester === null || ! $requester->isActive()) {
            throw ValidationException::withMessages([
                'requester_id' => 'Pemohon yang dipilih tidak aktif atau tidak ditemukan.',
            ]);
        }

        if ((int) $requester->getKey() === (int) $actor->getKey()) {
            $this->authorization->authorize($actor, 'createSelf', Ticket::class, 'ticket.create_self');
        } else {
            $this->authorization->authorize($actor, 'createForOther', $requester, 'ticket.create_for_other');
        }

        return $requester;
    }

    private function resolveServiceType(mixed $serviceTypeId): ServiceType
    {
        $serviceType = ServiceType::query()
            ->active()
            ->with(['activeFieldDefinitions.options', 'activeVariants', 'activeSlaPolicy'])
            ->find((int) $serviceTypeId);

        if ($serviceType === null) {
            throw ValidationException::withMessages([
                'service_type_id' => 'Layanan aktif wajib dipilih.',
            ]);
        }

        return $serviceType;
    }

    /**
     * @param  array<string, array{definition:mixed,value:mixed}>  $fields
     * @return array{0:string,1:ServiceTypeVariant|null}
     */
    private function resolveTicketClass(ServiceType $serviceType, array $fields): array
    {
        if ($serviceType->code !== 'SVC-05') {
            if (! in_array($serviceType->ticket_class, ServiceCatalogService::TICKET_CLASSES, true)) {
                throw ValidationException::withMessages([
                    'service_type_id' => 'Kelas nomor layanan belum dikonfigurasi.',
                ]);
            }

            return [$serviceType->ticket_class, null];
        }

        $subtype = $fields['request_subtype']['value'] ?? null;
        $variant = $serviceType->activeVariants->firstWhere('code', $subtype);

        if ($variant === null) {
            throw ValidationException::withMessages([
                'fields.request_subtype' => 'Subjenis layanan hardware wajib dipilih.',
            ]);
        }

        return [$variant->ticket_class, $variant];
    }

    private function resolveRoom(mixed $roomId, ServiceType $serviceType): ?Room
    {
        $required = in_array($serviceType->code, ['SVC-01', 'SVC-05'], true);
        $roomId = $roomId === null || $roomId === '' ? null : (int) $roomId;

        if ($roomId === null && $required) {
            throw ValidationException::withMessages([
                'room_id' => "Lokasi wajib diisi untuk {$serviceType->code}.",
            ]);
        }

        if ($roomId === null) {
            return null;
        }

        $room = Room::query()
            ->active()
            ->with('floor.building')
            ->whereKey($roomId)
            ->whereHas('floor', fn ($query) => $query->where('is_active', true)->whereHas('building', fn ($building) => $building->where('is_active', true)))
            ->first();

        if ($room === null) {
            throw ValidationException::withMessages([
                'room_id' => 'Lokasi yang dipilih tidak aktif atau tidak ditemukan.',
            ]);
        }

        return $room;
    }

    private function resolvePriority(mixed $priority): Priority
    {
        $resolved = Priority::tryFrom((string) $priority);

        if ($resolved === null) {
            throw ValidationException::withMessages([
                'priority' => 'Prioritas usulan wajib dipilih.',
            ]);
        }

        return $resolved;
    }

    /** @return array<string, mixed> */
    private function snapshot(Ticket $ticket): array
    {
        return [
            'id' => $ticket->id,
            'ticket_number' => $ticket->ticket_number,
            'ticket_class' => $ticket->ticket_class,
            'requester_id' => $ticket->requester_id,
            'created_by_id' => $ticket->created_by_id,
            'requester_name' => $ticket->requester_name_snapshot,
            'requester_nip' => $ticket->requester_nip_snapshot,
            'requester_team' => $ticket->requester_team_snapshot,
            'created_by_name' => $ticket->created_by_name_snapshot,
            'is_self_created' => $ticket->is_self_created,
            'service_type' => $ticket->service_type_code_snapshot,
            'priority' => $ticket->priority?->value,
            'status' => $ticket->status?->value,
            'room' => $ticket->room_name_snapshot,
            'attachment_count' => $ticket->attachments->count(),
        ];
    }
}
