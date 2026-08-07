@extends('layouts.app')

@section('title', 'Notifikasi — '.$branding['application_name'])
@section('header_title', 'Notifikasi')

@section('content')
    <div class="ui-page-header">
        <div>
            <p class="ui-eyebrow"><span class="ui-eyebrow-dot" aria-hidden="true"></span>Pusat informasi</p>
            <h1 class="ui-page-title">Notifikasi</h1>
            <p class="ui-page-description">Ikuti balasan, permintaan informasi, dan perubahan penanganan tiket yang menjadi tanggung jawab Anda.</p>
        </div>
        @if ($notifications->contains(fn ($notification): bool => $notification->read_at === null))
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button type="submit" class="ui-btn ui-btn-secondary">Tandai semua dibaca</button>
            </form>
        @endif
    </div>

    <section class="ui-panel mt-8 overflow-hidden" aria-labelledby="notifications-heading">
        <div class="ui-panel-header">
            <h2 id="notifications-heading" class="ui-section-title">Pembaruan terbaru</h2>
            <p class="ui-section-description">Notifikasi tersimpan di akun Anda dan dapat dibuka kembali kapan saja.</p>
        </div>

        @if ($notifications->isEmpty())
            <div class="p-8 text-center sm:p-12">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-[#fff4cc] text-[#b77a00]" aria-hidden="true">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6.8 9.5a5.2 5.2 0 0 1 10.4 0c0 5 2 5.8 2 7H4.8c0-1.2 2-2 2-7ZM9.7 19a2.5 2.5 0 0 0 4.6 0" /></svg>
                </div>
                <p class="mt-4 text-sm font-extrabold text-[#35505b]">Belum ada notifikasi</p>
                <p class="mx-auto mt-1 max-w-md text-sm leading-6 text-[#78909a]">Pembaruan penting dari tiket Anda akan muncul di sini.</p>
            </div>
        @else
            <div class="divide-y divide-[#edf2f4]">
                @foreach ($notifications as $notification)
                    @php
                        $data = $notification->data;
                        $ticketId = $data['ticket_id'] ?? null;
                    @endphp
                    <article class="flex flex-col gap-4 p-5 transition sm:flex-row sm:items-start sm:justify-between sm:p-6 {{ $notification->read_at ? 'bg-white' : 'bg-[#f1fbfe]' }}">
                        <div class="flex min-w-0 gap-3">
                            <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full {{ $notification->read_at ? 'bg-[#dfe8ec]' : 'bg-[#2bb8aa]' }}" aria-hidden="true"></span>
                            <div class="min-w-0">
                                <p class="text-sm font-extrabold text-[#35505b]">{{ $data['title'] ?? 'Notifikasi tiket' }}</p>
                                <p class="mt-1 text-sm leading-6 text-[#526f79]">{{ $data['message'] ?? 'Ada pembaruan pada tiket.' }}</p>
                                <time class="mt-2 block text-xs text-[#78909a]" datetime="{{ $notification->created_at?->toIso8601String() }}">{{ $notification->created_at?->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i') }}</time>
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-3 pl-5 sm:pl-0">
                            @if ($ticketId)
                                <a href="{{ route('tickets.show', $ticketId) }}" class="ui-action-link">Buka tiket</a>
                            @endif
                            @if (! $notification->read_at)
                                <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                    @csrf
                                    <button type="submit" class="text-xs font-extrabold text-[#147a79] hover:text-[#0f5f5e]">Tandai dibaca</button>
                                </form>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="border-t border-[#edf2f4] px-5 py-4 sm:px-6">
                {{ $notifications->links() }}
            </div>
        @endif
    </section>
@endsection
