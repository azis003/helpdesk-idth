<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResumeThirdPartyWaitRequest;
use App\Http\Requests\StoreTicketCommentRequest;
use App\Http\Requests\ThirdPartyWaitRequest;
use App\Models\Ticket;
use App\Services\TicketCommunicationService;
use App\Services\TicketWaitingService;
use Illuminate\Http\RedirectResponse;

class TicketCommunicationController extends Controller
{
    public function __construct(
        private readonly TicketCommunicationService $communication,
        private readonly TicketWaitingService $waiting,
    ) {}

    public function publicReply(StoreTicketCommentRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->communication->publicReply(
            $request->user(),
            $ticket,
            (string) $request->validated('body'),
            $request->file('attachments', []),
        );

        return back()->with('success', 'Balasan ke Pemohon berhasil dikirim.');
    }

    public function internalNote(StoreTicketCommentRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->communication->internalNote(
            $request->user(),
            $ticket,
            (string) $request->validated('body'),
            $request->file('attachments', []),
        );

        return back()->with('success', 'Catatan internal berhasil disimpan.');
    }

    public function requestInformation(StoreTicketCommentRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->waiting->requestInformation(
            $request->user(),
            $ticket,
            (string) $request->validated('body'),
            $request->file('attachments', []),
        );

        return back()->with('success', 'Pertanyaan dikirim dan tiket sekarang Menunggu Pemohon.');
    }

    public function requesterReply(StoreTicketCommentRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->waiting->requesterReply(
            $request->user(),
            $ticket,
            (string) $request->validated('body'),
            $request->file('attachments', []),
        );

        return back()->with('success', 'Komentar berhasil ditambahkan.');
    }

    public function startThirdParty(ThirdPartyWaitRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->waiting->startThirdParty(
            $request->user(),
            $ticket,
            (string) $request->validated('third_party_name'),
            $request->validated('follow_up_date'),
            $request->validated('note'),
        );

        return back()->with('success', 'Tiket sekarang Menunggu Pihak Ketiga.');
    }

    public function resumeThirdParty(ResumeThirdPartyWaitRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->waiting->resumeThirdParty(
            $request->user(),
            $ticket,
            $request->validated('reason'),
        );

        return back()->with('success', 'Tiket kembali ke status Dikerjakan.');
    }
}
