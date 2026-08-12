@extends('layouts.app')

@php
    // Presentasional saja - tidak mengubah data, logika, maupun alur halaman.
    $userEditIconTile = 'inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-[var(--tm-r-sm)] bg-[color:var(--tm-brand-50)] text-[color:var(--tm-brand-700)]';
    $userToggleBase = 'inline-flex min-h-9 items-center justify-center gap-1.5 rounded-[var(--tm-r-sm)] border px-3 py-1.5 text-xs font-bold transition-[background-color,border-color,color,box-shadow] duration-[var(--tm-dur-fast)] ease-[var(--tm-ease)]';
    $userToggleDanger = $userToggleBase.' border-[color:var(--tm-danger-200)] bg-[color:var(--tm-danger-50)] text-[color:var(--tm-danger-700)] hover:bg-[color:var(--tm-danger-100)]';
    $userToggleSuccess = $userToggleBase.' border-[color:var(--tm-success-200)] bg-[color:var(--tm-success-50)] text-[color:var(--tm-success-700)] hover:bg-[color:var(--tm-success-100)]';
    $userHistoryPanel = 'rounded-[var(--tm-r-md)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-sunken)] p-4';
    $userHistoryLabel = 'text-[0.7rem] font-bold uppercase tracking-[0.08em] text-[color:var(--tm-text-faint)]';
    $userHistoryItem = 'flex flex-wrap items-baseline gap-x-1.5 gap-y-0.5 text-[color:var(--tm-text-secondary)]';
    $userHistoryEmpty = 'text-[color:var(--tm-text-faint)]';
    $userHistorySeparator = 'text-[color:var(--tm-text-faint)]';
@endphp

@section('title', 'Edit Pengguna — '.$branding['application_name'])
@section('header_kicker', 'Manajemen Pengguna')
@section('header_title', 'Edit pengguna')

@section('content')
    <div class="max-w-3xl">
        <x-page-header
            eyebrow="Data Master · Akses pengguna"
            title="Edit Pengguna"
            description="Perbarui data akun dan akses pengguna."
            :back-url="route('admin.users.index')"
            back-label="Kembali ke daftar pengguna"
        />
        <div class="ui-panel mt-6 overflow-hidden">
            <div class="flex flex-col gap-3 border-b border-[color:var(--tm-border-subtle)] px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="{{ $userEditIconTile }}" aria-hidden="true">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19 20v-1.25a4.75 4.75 0 0 0-4.75-4.75h-4.5A4.75 4.75 0 0 0 5 18.75V20" /><circle cx="12" cy="7.75" r="3.75" /></svg>
                    </span>
                    <div class="min-w-0">
                        <h2 class="text-base font-extrabold tracking-tight text-[color:var(--tm-text)]">Data akun</h2>
                        <p class="mt-0.5 truncate text-xs text-[color:var(--tm-text-muted)]">{{ $user->name }}</p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="ui-status {{ $user->is_active ? 'ui-status-active' : 'ui-status-inactive' }}">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    @if ($user->isNot(auth()->user()))
                        @if ($user->is_active)
                            <form method="POST" action="{{ route('admin.users.deactivate', $user) }}" data-swal-confirm="Nonaktifkan akun ini? Pengguna tidak dapat login sampai diaktifkan kembali.">
                                @csrf
                                <button type="submit" class="{{ $userToggleDanger }}">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9" /><path stroke-linecap="round" d="m8.5 8.5 7 7" /></svg>
                                    Nonaktifkan
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.users.activate', $user) }}">
                                @csrf
                                <button type="submit" class="{{ $userToggleSuccess }}">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9" /><path stroke-linecap="round" stroke-linejoin="round" d="m8.5 12.25 2.4 2.4 4.6-5" /></svg>
                                    Aktifkan
                                </button>
                            </form>
                        @endif
                    @endif
                </div>
            </div>

            @include('admin.users._form', ['action' => route('admin.users.update', $user), 'method' => 'PUT', 'formId' => 'edit-page', 'prefix' => 'user-edit-page', 'isEdit' => true, 'isModal' => false])

            <details class="border-t border-[color:var(--tm-border-subtle)] px-5 py-4 sm:px-6">
                <summary class="ui-disclosure-summary">Riwayat organisasi</summary>
                <div class="mt-4 grid gap-4 text-sm sm:grid-cols-2">
                    <div class="{{ $userHistoryPanel }}">
                        <h2 class="{{ $userHistoryLabel }}">Riwayat tim</h2>
                        <ul class="mt-2.5 space-y-2">
                            @forelse ($user->teamMemberships->sortByDesc('started_at') as $membership)
                                <li class="{{ $userHistoryItem }}">
                                    <span class="font-semibold text-[color:var(--tm-text)]">{{ $membership->workTeam?->name ?? 'Tim dihapus' }}</span>
                                    <span class="{{ $userHistorySeparator }}">·</span>
                                    <span class="tabular-nums">{{ $membership->started_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</span>
                                </li>
                            @empty
                                <li class="{{ $userHistoryEmpty }}">Belum ada riwayat tim.</li>
                            @endforelse
                        </ul>
                    </div>
                    <div class="{{ $userHistoryPanel }}">
                        <h2 class="{{ $userHistoryLabel }}">Riwayat role</h2>
                        <ul class="mt-2.5 space-y-2">
                            @forelse ($roleHistories as $history)
                                @php
                                    // Presentasional saja - kondisi sama dengan label di bawah.
                                    $historyTone = $history->action === 'assigned'
                                        ? 'border-[color:var(--tm-success-200)] bg-[color:var(--tm-success-50)] text-[color:var(--tm-success-700)]'
                                        : 'border-[color:var(--tm-border)] bg-[color:var(--tm-surface)] text-[color:var(--tm-text-muted)]';
                                @endphp
                                <li class="{{ $userHistoryItem }}">
                                    <span class="font-semibold text-[color:var(--tm-text)]">{{ $history->role?->managementLabel() }}</span>
                                    <span class="inline-flex items-center rounded-[var(--tm-r-full)] border px-2 py-0.5 text-[0.7rem] font-bold {{ $historyTone }}">{{ $history->action === 'assigned' ? 'Diberikan' : 'Dicabut' }}</span>
                                    <span class="{{ $userHistorySeparator }}">·</span>
                                    <span class="tabular-nums">{{ $history->occurred_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</span>
                                </li>
                            @empty
                                <li class="{{ $userHistoryEmpty }}">Belum ada histori perubahan role.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </details>
        </div>
    </div>
@endsection
