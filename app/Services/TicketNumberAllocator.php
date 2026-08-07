<?php

namespace App\Services;

use App\Models\TicketNumberSequence;
use Illuminate\Database\DatabaseManager;
use Illuminate\Validation\ValidationException;

class TicketNumberAllocator
{
    public function __construct(private readonly DatabaseManager $database) {}

    /**
     * Reserve the next number in its own committed transaction.
     *
     * A ticket transaction may fail after this method returns. The sequence
     * must still remain advanced so that an allocated number can never be
     * returned by a later retry.
     *
     * @return array{number:string, sequence:int}
     */
    public function next(string $ticketClass, int $ticketYear): array
    {
        if (! in_array($ticketClass, ServiceCatalogService::TICKET_CLASSES, true)) {
            throw ValidationException::withMessages([
                'service_type_id' => 'Kelas nomor tiket tidak didukung.',
            ]);
        }

        if ($ticketYear < 1000 || $ticketYear > 9999) {
            throw ValidationException::withMessages([
                'ticket_number' => 'Tahun nomor tiket harus terdiri dari empat digit.',
            ]);
        }

        return $this->database->transaction(function () use ($ticketClass, $ticketYear): array {
            /*
             * The unique class/year key serializes the first-row race. On
             * PostgreSQL a concurrent INSERT ... ON CONFLICT DO NOTHING waits
             * for the competing insert before the row is locked below.
             */
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
                'number' => sprintf('%s-%04d-%05d', $ticketClass, $ticketYear, $nextSequence),
                'sequence' => $nextSequence,
            ];
        }, 3);
    }
}
