<?php

namespace App\Http\Controllers;

use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function index(Request $request): mixed
    {
        return view('notifications.index', [
            'notifications' => $request->user()->notifications()->latest()->paginate(20),
        ]);
    }

    public function markRead(Request $request, string $notification): RedirectResponse
    {
        $record = $request->user()->notifications()->whereKey($notification)->first();

        if ($record === null) {
            $this->auditLogger->denied(
                $request->user(),
                'notification.read',
                null,
                'Notifikasi tidak termasuk dalam cakupan pengguna aktif.',
                ['notification_id' => $notification],
            );

            abort(404, 'Notifikasi tidak ditemukan.');
        }

        $record->markAsRead();

        $ticketId = $record->data['ticket_id'] ?? null;

        return $ticketId
            ? redirect()->route('tickets.show', $ticketId)
            : redirect()->route('notifications.index');
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }
}
