@extends('layouts.app')

@php
    // Presentasional saja - tidak mengubah data maupun logika notifikasi.
    $notifItem = 'flex flex-col gap-4 p-5 transition-[background-color] duration-[var(--tm-dur-fast)] ease-[var(--tm-ease)] sm:flex-row sm:items-start sm:justify-between sm:p-6';
    $notifRead = 'bg-[color:var(--tm-surface)]';
    $notifUnread = 'bg-[color:var(--tm-brand-50)]/50';
    $notifDotRead = 'mt-1.5 h-2 w-2 shrink-0 rounded-full bg-[color:var(--tm-border-strong)]';
    $notifDotUnread = 'mt-1.5 h-2 w-2 shrink-0 rounded-full bg-[color:var(--tm-brand-500)]';
    $notifMarkRead = 'text-xs font-extrabold text-[color:var(--tm-brand-700)] transition-[color] duration-[var(--tm-dur-fast)] ease-[var(--tm-ease)] hover:text-[color:var(--tm-brand-800)] focus:outline-none focus:underline';
    $notifEmptyTitle = 'Belum ada notifikasi';
    $notifEmptyDescription = 'Pembaruan penting dari tiket Anda akan muncul di sini.';
@endphp

@section('title', 'Notifikasi — '.$branding['application_name'])
@section('header_title', 'Notifikasi')

@section('content')
    <x-page-header
        eyebrow="Pusat informasi"
        title="Notifikasi"
        description="Ikuti balasan, permintaan informasi, dan perubahan penanganan tiket yang menjadi tanggung jawab Anda."
    >
        @if ($notifications->contains(fn ($notification): bool => $notification->read_at === null))
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button type="submit" class="ui-btn ui-btn-secondary">Tandai semua dibaca</button>
            </form>
        @endif
    </x-page-header>

    <section class="ui-panel mt-6 overflow-hidden" aria-labelledby="notifications-heading">
        <div class="ui-panel-header border-b border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface-sunken)]/30 px-5 py-4 sm:px-6">
            <h2 id="notifications-heading" class="ui-section-title">Pembaruan terbaru</h2>
            <p class="ui-section-description">Notifikasi tersimpan di akun Anda dan dapat dibuka kembali kapan saja.</p>
        </div>

        @if ($notifications->isEmpty())
            <x-empty-state :title="$notifEmptyTitle" :description="$notifEmptyDescription" />
        @else
            <div class="divide-y divide-[color:var(--tm-border-subtle)]">
                @foreach ($notifications as $notification)
                    @php
                        $data = $notification->data;
                        $ticketId = $data['ticket_id'] ?? null;
                    @endphp
                    <article class="{{ $notifItem }} {{ $notification->read_at ? $notifRead : $notifUnread }}">
                        <div class="flex min-w-0 gap-3">
                            <span class="{{ $notification->read_at ? $notifDotRead : $notifDotUnread }}" aria-hidden="true"></span>
                            <div class="min-w-0">
                                <p class="text-sm font-extrabold text-[color:var(--tm-text)]">
                                    @if (! $notification->read_at)
                                        <span class="inline-flex items-center rounded-full bg-[color:var(--tm-brand-100)] px-1.5 py-0.5 text-[0.62rem] font-bold uppercase tracking-wide text-[color:var(--tm-brand-800)] mr-1.5 shrink-0">Baru</span>
                                    @endif
                                    {{ $data['title'] ?? 'Notifikasi tiket' }}
                                </p>
                                <p class="mt-1 text-sm leading-6 text-[color:var(--tm-text-secondary)]">{{ $data['message'] ?? 'Ada pembaruan pada tiket.' }}</p>
                                <time class="mt-2 block text-xs tabular-nums text-[color:var(--tm-text-muted)]" datetime="{{ $notification->created_at?->toIso8601String() }}">{{ $notification->created_at?->timezone(config('app.timezone'))->translatedFormat('d M Y, H:i') }}</time>
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-3 pl-5 sm:pl-0 mt-2 sm:mt-0 flex-wrap">
                            @if ($ticketId)
                                <a href="{{ route('tickets.show', $ticketId) }}" class="ui-action-link focus:outline-none focus:underline">Buka tiket</a>
                            @endif
                            @if (! $notification->read_at)
                                <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                    @csrf
                                    <button type="submit" class="{{ $notifMarkRead }}">Tandai dibaca</button>
                                </form>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="border-t border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-surface-sunken)] px-5 py-4 tabular-nums sm:px-6">
                {{ $notifications->links() }}
            </div>
        @endif
    </section>
@endsection
