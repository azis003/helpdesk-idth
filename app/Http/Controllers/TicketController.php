<?php

namespace App\Http\Controllers;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Services\AuditLogger;
use App\Services\DomainAuthorization;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function __construct(
        private readonly DomainAuthorization $authorization,
        private readonly AuditLogger $auditLogger,
        private readonly DatabaseManager $database,
    ) {}

    public function claim(Request $request, Ticket $ticket): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'claim', $ticket, 'ticket.claim');

        $claimed = $this->database->transaction(function () use ($actor, $ticket): bool {
            $lockedTicket = Ticket::query()->whereKey($ticket->getKey())->lockForUpdate()->first();

            if ($lockedTicket === null
                || $lockedTicket->status !== TicketStatus::Baru
                || $lockedTicket->assigned_to_id !== null) {
                $this->auditLogger->denied($actor, 'ticket.claim', $ticket, 'Tiket sudah tidak tersedia untuk diklaim.');

                return false;
            }

            $lockedTicket->forceFill([
                'status' => TicketStatus::Diproses,
                'assigned_to_id' => $actor->getKey(),
                'assigned_tier' => 'agen_tier_1',
            ])->save();

            $this->auditLogger->succeeded($actor, 'ticket.claim', $lockedTicket);

            return true;
        });

        if (! $claimed) {
            return back()->withErrors(['ticket' => 'Tiket sudah diambil oleh agen lain atau tidak tersedia.']);
        }

        return back()->with('success', 'Tiket berhasil diambil dari antrean.');
    }

    public function handle(Request $request, Ticket $ticket): RedirectResponse
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'handle', $ticket, 'ticket.handle');

        $ticket->forceFill(['status' => TicketStatus::Dikerjakan])->save();
        $this->auditLogger->succeeded($actor, 'ticket.handle', $ticket);

        return back()->with('success', 'Tiket ditandai sedang dikerjakan.');
    }
}
