<?php

namespace App\Services;

use App\Models\ServiceType;
use App\Models\Ticket;
use App\Models\TicketFieldValue;
use App\Models\User;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class TicketInternalFieldService
{
    public function __construct(
        private readonly DatabaseManager $database,
        private readonly DomainAuthorization $authorization,
        private readonly AuditLogger $auditLogger,
        private readonly DynamicFieldValidator $fieldValidator,
    ) {}

    /**
     * Store the internal SVC-07 fields for the agent currently handling the ticket.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(User $actor, Ticket $ticket, array $data): Ticket
    {
        $this->authorization->authorize($actor, 'updateInternalFields', $ticket, 'ticket.internal_fields.update');
        $now = Carbon::now(config('app.timezone'));

        return $this->database->transaction(function () use ($actor, $ticket, $data, $now): Ticket {
            $lockedTicket = Ticket::query()
                ->whereKey($ticket->getKey())
                ->lockForUpdate()
                ->first();

            if ($lockedTicket === null) {
                $this->deny($actor, $ticket, 'Tiket tidak ditemukan.');
            }

            $this->authorization->authorize($actor, 'updateInternalFields', $lockedTicket, 'ticket.internal_fields.update');

            $serviceType = ServiceType::query()
                ->with('activeFieldDefinitions.options')
                ->whereKey($lockedTicket->service_type_id)
                ->first();

            if ($serviceType === null || $serviceType->code !== 'SVC-07') {
                $this->deny($actor, $lockedTicket, 'Field internal hanya tersedia untuk layanan SVC-07.');
            }

            $definitions = $serviceType->activeFieldDefinitions
                ->where('visibility', 'internal')
                ->values();

            if ($definitions->isEmpty()) {
                $this->deny($actor, $lockedTicket, 'Belum ada field internal aktif untuk SVC-07.');
            }

            $existingValues = TicketFieldValue::query()
                ->where('ticket_id', $lockedTicket->getKey())
                ->whereIn('field_key', $definitions->pluck('key')->all())
                ->lockForUpdate()
                ->get()
                ->keyBy('field_key');

            $input = is_array($data['internal_fields'] ?? null)
                ? $data['internal_fields']
                : (is_array($data['fields'] ?? null) ? $data['fields'] : []);
            $expectedVersions = is_array($data['internal_field_versions'] ?? null)
                ? $data['internal_field_versions']
                : (is_array($data['field_versions'] ?? null) ? $data['field_versions'] : []);
            $submittedKeys = array_keys($input);

            // A form submits every active field. Keeping existing values for a
            // partial caller also makes the endpoint safe for incremental saves.
            foreach ($existingValues as $key => $existingValue) {
                if (! array_key_exists($key, $input)) {
                    $input[$key] = $existingValue->value;
                }
            }

            try {
                $validatedFields = $this->fieldValidator->validateInternal(
                    $serviceType,
                    $input,
                    $expectedVersions,
                    $submittedKeys,
                );
            } catch (ValidationException $exception) {
                $this->auditLogger->denied(
                    $actor,
                    'ticket.internal_fields.update',
                    $lockedTicket,
                    'Nilai field internal tidak lolos validasi.',
                );

                throw $exception;
            }

            $before = [];
            $after = [];
            $changedFields = [];

            foreach ($validatedFields as $field) {
                $definition = $field['definition'];
                $value = $field['value'];
                $existing = $existingValues->get($definition->key);

                if ($existing !== null && ! $existing->isInternal()) {
                    $this->deny(
                        $actor,
                        $lockedTicket,
                        "Field {$definition->label} memiliki nilai Pemohon yang tidak dapat diubah menjadi field internal.",
                        'internal_fields.'.$definition->key,
                    );
                }

                $before[$definition->key] = $existing === null
                    ? null
                    : $this->valueSnapshot($existing);
                $after[$definition->key] = [
                    'label' => $definition->label,
                    'field_type' => $definition->field_type,
                    'visibility' => $definition->visibility,
                    'version' => $definition->version,
                    'value' => $value,
                ];

                $hasChange = $existing === null
                    ? $value !== null
                    : ! $this->sameValue($existing->value, $value)
                        || (int) $existing->service_field_definition_id !== (int) $definition->getKey()
                        || $existing->label_snapshot !== $definition->label
                        || $existing->field_type_snapshot !== $definition->field_type
                        || $existing->visibility_snapshot !== $definition->visibility
                        || (int) $existing->version_snapshot !== (int) $definition->version;

                if ($existing === null && $value === null) {
                    continue;
                }

                $attributes = [
                    'service_field_definition_id' => $definition->getKey(),
                    'field_key' => $definition->key,
                    'label_snapshot' => $definition->label,
                    'field_type_snapshot' => $definition->field_type,
                    'visibility_snapshot' => $definition->visibility,
                    'version_snapshot' => $definition->version,
                    'value' => $value,
                ];

                if ($existing === null) {
                    $lockedTicket->fieldValues()->create($attributes);
                } else {
                    $existing->forceFill($attributes)->save();
                }

                if ($hasChange) {
                    $lockedTicket->fieldValueHistories()->create([
                        'service_field_definition_id' => $definition->getKey(),
                        'actor_id' => $actor->getKey(),
                        'field_key' => $definition->key,
                        'label_snapshot' => $definition->label,
                        'field_type_snapshot' => $definition->field_type,
                        'visibility_snapshot' => $definition->visibility,
                        'version_snapshot' => $definition->version,
                        'change_type' => $existing === null ? 'created' : 'updated',
                        'old_value' => $existing === null ? null : $before[$definition->key]['value'],
                        'new_value' => $value,
                        'occurred_at' => $now,
                    ]);
                    $changedFields[] = $definition->key;
                }

            }

            $this->auditLogger->succeeded(
                $actor,
                'ticket.internal_fields.updated',
                $lockedTicket,
                $changedFields === []
                    ? 'Field internal SVC-07 dikirim tanpa perubahan nilai.'
                    : 'Field internal SVC-07 diperbarui dan snapshot definisinya disimpan.',
                ['fields' => $before],
                [
                    'fields' => $after,
                    'changed_fields' => $changedFields,
                ],
            );

            return $lockedTicket->fresh([
                'serviceType',
                'fieldValues',
                'fieldValueHistories',
            ]);
        });
    }

    /** @return array<string, mixed> */
    private function valueSnapshot(TicketFieldValue $value): array
    {
        return [
            'label' => $value->label_snapshot,
            'field_type' => $value->field_type_snapshot,
            'visibility' => $value->visibility_snapshot,
            'version' => $value->version_snapshot,
            'value' => $value->value,
        ];
    }

    private function sameValue(mixed $first, mixed $second): bool
    {
        return json_encode($first, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) === json_encode($second, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function deny(
        User $actor,
        Ticket $ticket,
        string $reason,
        string $field = 'internal_fields',
    ): never {
        $this->auditLogger->denied($actor, 'ticket.internal_fields.update', $ticket, $reason);

        throw ValidationException::withMessages([$field => $reason]);
    }
}
