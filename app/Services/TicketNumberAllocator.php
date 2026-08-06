<?php

namespace App\Services;

use App\Models\TicketNumberSequence;
use Illuminate\Validation\ValidationException;

class TicketNumberAllocator
{
    /**
     * @return array{number:string, sequence:int}
     */
    public function next(string $ticketClass, int $ticketYear): array
    {
        if (! in_array($ticketClass, ServiceCatalogService::TICKET_CLASSES, true)) {
            throw ValidationException::withMessages([
                'service_type_id' => 'Kelas nomor tiket tidak didukung.',
            ]);
        }

        TicketNumberSequence::query()->insertOrIgnore([
            'ticket_class' => $ticketClass,
            'ticket_year' => $ticketYear,
            'last_sequence' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sequence = TicketNumberSequence::query()
            ->where('ticket_class', $ticketClass)
            ->where('ticket_year', $ticketYear)
            ->lockForUpdate()
            ->firstOrFail();

        $nextSequence = ((int) $sequence->last_sequence) + 1;

        if ($nextSequence > 99999) {
            throw ValidationException::withMessages([
                'service_type_id' => "Nomor tiket {$ticketClass}-{$ticketYear} sudah mencapai batas urutan.",
            ]);
        }

        $sequence->forceFill(['last_sequence' => $nextSequence])->save();

        return [
            'number' => sprintf('%s-%d-%05d', $ticketClass, $ticketYear, $nextSequence),
            'sequence' => $nextSequence,
        ];
    }
}
