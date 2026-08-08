@extends('layouts.app')

@section('title', 'Edit Pengguna — '.$branding['application_name'])
@section('header_kicker', 'Manajemen Pengguna')
@section('header_title', 'Edit pengguna')

@section('content')
    <div class="max-w-3xl">
        <a href="{{ route('admin.users.index') }}" class="ui-action-link">← Kembali ke daftar pengguna</a>
        <div class="ui-panel mt-5 overflow-hidden">
            <div class="flex flex-col gap-3 border-b border-[#e7eef1] bg-[#6098c6] px-5 py-4 text-white sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div>
                    <h1 class="text-lg font-extrabold">Edit Pengguna</h1>
                    <p class="mt-1 text-xs text-white/80">Perbarui data akun dan akses pengguna.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="rounded-full bg-white/15 px-2.5 py-1 text-xs font-bold">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                    @if ($user->isNot(auth()->user()))
                        @if ($user->is_active)
                            <form method="POST" action="{{ route('admin.users.deactivate', $user) }}" onsubmit="return confirm('Nonaktifkan akun ini? Pengguna tidak dapat login sampai diaktifkan kembali.');">
                                @csrf
                                <button type="submit" class="rounded-lg bg-rose-500/90 px-3 py-2 text-xs font-bold text-white transition hover:bg-rose-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white">Nonaktifkan</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.users.activate', $user) }}">
                                @csrf
                                <button type="submit" class="rounded-lg bg-emerald-500/90 px-3 py-2 text-xs font-bold text-white transition hover:bg-emerald-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white">Aktifkan</button>
                            </form>
                        @endif
                    @endif
                </div>
            </div>

            @include('admin.users._form', ['action' => route('admin.users.update', $user), 'method' => 'PUT', 'formId' => 'edit-page', 'prefix' => 'user-edit-page', 'isEdit' => true, 'isModal' => false])

            <details class="border-t border-[#e7eef1] px-5 py-4 sm:px-6">
                <summary class="cursor-pointer text-sm font-bold text-[#35505b] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0a87c9]">Riwayat organisasi</summary>
                <div class="mt-4 grid gap-5 text-sm text-[#607681] sm:grid-cols-2">
                    <div>
                        <h2 class="font-bold text-[#17313c]">Riwayat tim</h2>
                        <ul class="mt-2 space-y-2">
                            @forelse ($user->teamMemberships->sortByDesc('started_at') as $membership)
                                <li>{{ $membership->workTeam?->name ?? 'Tim dihapus' }} · {{ $membership->started_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</li>
                            @empty
                                <li>Belum ada riwayat tim.</li>
                            @endforelse
                        </ul>
                    </div>
                    <div>
                        <h2 class="font-bold text-[#17313c]">Riwayat role</h2>
                        <ul class="mt-2 space-y-2">
                            @forelse ($roleHistories as $history)
                                <li>{{ $history->role?->managementLabel() }} · {{ $history->action === 'assigned' ? 'Diberikan' : 'Dicabut' }} · {{ $history->occurred_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</li>
                            @empty
                                <li>Belum ada histori perubahan role.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </details>
        </div>
    </div>
@endsection
