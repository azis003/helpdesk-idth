<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Enums\TicketStatus;
use App\Http\Requests\StoreTicketRequest;
use App\Models\Announcement;
use App\Models\AttachmentPolicy;
use App\Models\Building;
use App\Models\ServiceType;
use App\Models\Ticket;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\DomainAuthorization;
use App\Services\TicketCancellationService;
use App\Services\TicketCreationService;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function __construct(
        private readonly DomainAuthorization $authorization,
        private readonly AuditLogger $auditLogger,
        private readonly DatabaseManager $database,
        private readonly TicketCreationService $creation,
        private readonly TicketCancellationService $cancellation,
    ) {}

    public function index(Request $request): mixed
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'viewAny', Ticket::class, 'ticket.list');

        $query = Ticket::query()
            ->with(['serviceType', 'requester', 'creator'])
            ->orderByDesc('submitted_at')
            ->orderByDesc('id');

        if ($actor->hasRole(Role::AgenTier1)) {
            $query->where(function ($query) use ($actor): void {
                $query->where('requester_id', $actor->getKey())
                    ->orWhere('created_by_id', $actor->getKey());
            });
        } elseif ($actor->hasRole(Role::AgenTier2)) {
            $query->where('assigned_to_id', $actor->getKey());
        } else {
            $query->where('requester_id', $actor->getKey());
        }

        return view('tickets.index', [
            'tickets' => $query->paginate(15)->withQueryString(),
        ]);
    }

    public function create(Request $request): mixed
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'create', Ticket::class, 'ticket.create');

        $serviceTypes = ServiceType::query()
            ->active()
            ->with(['activeFieldDefinitions.options', 'activeVariants'])
            ->orderBy('sort_order')
            ->orderBy('code')
            ->get();
        $includeInternal = $actor->hasRole(Role::AgenTier1);
        $attachmentPolicies = AttachmentPolicy::query()
            ->active()
            ->when(! $includeInternal, fn ($query) => $query->whereIn('visibility', ['requester', 'both']))
            ->with('serviceType')
            ->orderByRaw('service_type_id IS NOT NULL')
            ->orderBy('type_key')
            ->get();
        $buildings = Building::query()
            ->active()
            ->with(['floors' => fn ($query) => $query->active()->with(['rooms' => fn ($roomQuery) => $roomQuery->active()])])
            ->orderBy('name')
            ->get();
        $requesters = $actor->hasRole(Role::AgenTier1)
            ? User::query()->where('is_active', true)->orderBy('name')->get()
            : collect([$actor->loadMissing('currentTeamMembership.workTeam')]);

        return view('tickets.create', [
            'actor' => $actor,
            'serviceTypes' => $serviceTypes,
            'attachmentPolicies' => $attachmentPolicies,
            'buildings' => $buildings,
            'requesters' => $requesters,
            'canCreateForOthers' => $actor->hasRole(Role::AgenTier1),
            'announcements' => Announcement::query()
                ->activeAt(now())
                ->orderByDesc('starts_at')
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function store(StoreTicketRequest $request): RedirectResponse
    {
        $ticket = $this->creation->create(
            $request->user(),
            $request->validated(),
            $request->file('attachments', []),
        );

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', "Tiket {$ticket->ticket_number} berhasil dibuat.");
    }

    public function show(Request $request, Ticket $ticket): mixed
    {
        $actor = $request->user();
        $this->authorization->authorize($actor, 'view', $ticket, 'ticket.view');

        $ticket->load([
            'requester',
            'creator',
            'assignee',
            'serviceType',
            'serviceTypeVariant',
            'room.floor.building',
            'fieldValues',
            'attachments',
        ]);

        if (! $actor->hasAnyRole([Role::SuperAdmin, Role::AgenTier1, Role::AgenTier2])) {
            $ticket->setRelation(
                'attachments',
                $ticket->attachments->whereIn('visibility', ['requester', 'both'])->values(),
            );
        }

        return view('tickets.show', [
            'ticket' => $ticket,
        ]);
    }

    public function cancel(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->cancellation->cancel($request->user(), $ticket);

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', "Tiket {$ticket->ticket_number} berhasil dibatalkan.");
    }

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
