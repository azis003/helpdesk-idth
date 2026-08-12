@php
    $currentTeamMembership = $user->currentTeamMembership;
    $currentTeamId = $currentTeamMembership?->work_team_id;
    $isChair = $currentTeamId !== null && $user->teamChairAssignments->contains(
        fn ($assignment): bool => $assignment->is_active
            && (int) $assignment->work_team_id === (int) $currentTeamId,
    );
    $displayRoles = $user->roles->reject(fn ($role): bool => $role->slug === \App\Enums\Role::KetuaTimKerja->value);
    $teamSummary = $currentTeamMembership?->workTeam
        ? $currentTeamMembership->workTeam->name.' - '.($isChair ? 'Ketua' : 'Anggota')
        : '—';

    // Presentasional saja - tidak mengubah data maupun logika.
    $userDetailTile = 'rounded-[var(--tm-r-md)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-sunken)] p-3.5';
    $userDetailLabel = 'text-[0.7rem] font-bold uppercase tracking-[0.08em] text-[color:var(--tm-text-faint)]';
    $userDetailValue = 'mt-1.5 text-sm font-semibold text-[color:var(--tm-text)]';
@endphp

<dl class="grid gap-3 p-5 sm:grid-cols-2 sm:p-6">
    <div class="{{ $userDetailTile }}">
        <dt class="{{ $userDetailLabel }}">Nama Pengguna</dt>
        <dd class="{{ $userDetailValue }}">{{ $user->name }}</dd>
    </div>
    <div class="{{ $userDetailTile }}">
        <dt class="{{ $userDetailLabel }}">Username</dt>
        <dd class="{{ $userDetailValue }}">{{ $user->username }}</dd>
    </div>
    <div class="{{ $userDetailTile }}">
        <dt class="{{ $userDetailLabel }}">Email</dt>
        <dd class="{{ $userDetailValue }} break-all">{{ $user->email ?: '—' }}</dd>
    </div>
    <div class="{{ $userDetailTile }}">
        <dt class="{{ $userDetailLabel }}">NIP</dt>
        <dd class="{{ $userDetailValue }} tabular-nums">{{ $user->nip ?: '—' }}</dd>
    </div>
    <div class="{{ $userDetailTile }} sm:col-span-2">
        <dt class="{{ $userDetailLabel }}">Role</dt>
        <dd class="{{ $userDetailValue }}">
            @if ($displayRoles->isNotEmpty())
                <div class="flex flex-wrap gap-1.5">
                    @foreach ($displayRoles as $role)
                        <span class="ui-chip">{{ $role->managementLabel() }}</span>
                    @endforeach
                </div>
            @else
                <span class="font-normal text-[color:var(--tm-text-faint)]">Belum ada role</span>
            @endif
        </dd>
    </div>
    <div class="{{ $userDetailTile }} sm:col-span-2">
        <dt class="{{ $userDetailLabel }}">Tim Kerja</dt>
        <dd class="{{ $userDetailValue }}">{{ $teamSummary }}</dd>
    </div>
    <div class="{{ $userDetailTile }} sm:col-span-2">
        <dt class="{{ $userDetailLabel }}">Status</dt>
        <dd class="mt-1.5">
            <span class="ui-status {{ $user->is_active ? 'ui-status-active' : 'ui-status-inactive' }}">
                {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
            </span>
        </dd>
    </div>
</dl>
