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
@endphp

<dl class="grid gap-3 p-5 sm:grid-cols-2 sm:p-6">
    <div class="rounded-lg bg-[#f8fafb] p-3">
        <dt class="text-xs font-bold uppercase tracking-wide text-[#78909a]">Nama Pengguna</dt>
        <dd class="mt-1 text-sm font-semibold text-[#172d45]">{{ $user->name }}</dd>
    </div>
    <div class="rounded-lg bg-[#f8fafb] p-3">
        <dt class="text-xs font-bold uppercase tracking-wide text-[#78909a]">Username</dt>
        <dd class="mt-1 text-sm font-semibold text-[#172d45]">{{ $user->username }}</dd>
    </div>
    <div class="rounded-lg bg-[#f8fafb] p-3">
        <dt class="text-xs font-bold uppercase tracking-wide text-[#78909a]">Email</dt>
        <dd class="mt-1 break-all text-sm font-semibold text-[#172d45]">{{ $user->email ?: '—' }}</dd>
    </div>
    <div class="rounded-lg bg-[#f8fafb] p-3">
        <dt class="text-xs font-bold uppercase tracking-wide text-[#78909a]">NIP</dt>
        <dd class="mt-1 text-sm font-semibold text-[#172d45]">{{ $user->nip ?: '—' }}</dd>
    </div>
    <div class="rounded-lg bg-[#f8fafb] p-3 sm:col-span-2">
        <dt class="text-xs font-bold uppercase tracking-wide text-[#78909a]">Role</dt>
        <dd class="mt-1 text-sm font-semibold text-[#172d45]">
            @if ($displayRoles->isNotEmpty())
                <ul class="list-disc space-y-1 pl-4">
                    @foreach ($displayRoles as $role)
                        <li>{{ $role->managementLabel() }}</li>
                    @endforeach
                </ul>
            @else
                Belum ada role
            @endif
        </dd>
    </div>
    <div class="rounded-lg bg-[#f8fafb] p-3 sm:col-span-2">
        <dt class="text-xs font-bold uppercase tracking-wide text-[#78909a]">Tim Kerja</dt>
        <dd class="mt-1 text-sm font-semibold text-[#172d45]">{{ $teamSummary }}</dd>
    </div>
    <div class="rounded-lg bg-[#f8fafb] p-3 sm:col-span-2">
        <dt class="text-xs font-bold uppercase tracking-wide text-[#78909a]">Status</dt>
        <dd class="mt-1">
            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $user->is_active ? 'bg-[#e8faf4] text-[#087f5b]' : 'bg-[#eef2f4] text-[#657984]' }}">
                {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
            </span>
        </dd>
    </div>
</dl>
