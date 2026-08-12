@php
    $isEdit = $isEdit ?? false;
    $isModal = $isModal ?? false;
    $formId = $formId ?? ($isEdit ? 'edit' : 'create');
    $prefix = $prefix ?? 'user';
    $user = $user ?? null;
    $oldForm = old('_user_form');
    $useOld = $oldForm === $formId || ($oldForm === null && ! $isEdit && request()->routeIs('admin.users.create'));

    $nameValue = $useOld ? old('name') : $user?->name;
    $usernameValue = $useOld ? old('username') : $user?->username;
    $emailValue = $useOld ? old('email') : $user?->email;
    $nipValue = $useOld ? old('nip') : $user?->nip;
    $teamValue = $useOld ? old('team_id') : $user?->currentTeamMembership?->work_team_id;
    $activeChairAssignment = $user?->teamChairAssignments?->first(
        fn ($assignment): bool => $assignment->is_active
            && (string) $assignment->work_team_id === (string) $teamValue,
    );
    $teamPositionValue = $useOld
        ? old('team_position')
        : ($activeChairAssignment !== null ? \App\Enums\TeamPosition::Chair->value : (filled($teamValue) ? \App\Enums\TeamPosition::Member->value : null));
    $statusValue = $useOld
        ? old('is_active', $user?->is_active ? '1' : '0')
        : ($user?->is_active ? '1' : '0');
    $selectedRoleIds = collect($useOld ? old('role_ids', []) : ($user?->roles?->pluck('id')->all() ?? []))
        ->map(fn ($id): string => (string) $id)
        ->values()
        ->all();
    $selectedSkillIds = collect($useOld ? old('skill_ids', []) : ($user?->skills?->pluck('id')->all() ?? []))
        ->map(fn ($id): string => (string) $id)
        ->values()
        ->all();
    $technicianRoleSlug = \App\Enums\Role::AgenTier2->value;
    $hasTechnicianRole = $roles->contains(fn ($role): bool => in_array((string) $role->id, $selectedRoleIds, true) && $role->slug === $technicianRoleSlug);
    $assignableRoles = $roles->reject(fn ($role): bool => $role->slug === \App\Enums\Role::KetuaTimKerja->value);
    $roleError = $errors->first('role_ids') ?: $errors->first('role_ids.*');

    // Presentasional saja - tidak mengubah logika, data, maupun alur form.
    $userRequiredMark = 'text-[color:var(--tm-danger-600)]';
    $userChoiceTile = 'flex min-h-11 cursor-pointer items-center gap-3 rounded-[var(--tm-r-sm)] border border-[color:var(--tm-border)] bg-[color:var(--tm-surface)] px-3 py-2.5 text-sm text-[color:var(--tm-text-secondary)] transition-[background-color,border-color,color,box-shadow] duration-[var(--tm-dur-fast)] ease-[var(--tm-ease)] hover:border-[color:var(--tm-brand-300)] hover:bg-[color:var(--tm-brand-50)] has-[:checked]:border-[color:var(--tm-brand-400)] has-[:checked]:bg-[color:var(--tm-brand-50)] has-[:checked]:text-[color:var(--tm-brand-800)]';
    $userFieldsetPanel = 'rounded-[var(--tm-r-md)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-sunken)] p-4';
@endphp

<form
    method="POST"
    action="{{ $action }}"
    data-ui-modal-form
    data-user-form
    data-user-form-id="{{ $formId }}"
    class="p-5 sm:p-6"
>
    @csrf
    @if (($method ?? 'POST') !== 'POST')
        @method($method)
    @endif
    <input type="hidden" name="_user_form" value="{{ $formId }}">
    @if ($isEdit && $user)
        <input type="hidden" name="_user_edit" value="{{ $user->id }}">
    @endif

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="{{ $prefix }}-name" class="ui-field-label">Nama Pengguna <span class="{{ $userRequiredMark }}" aria-hidden="true">*</span><span class="sr-only"> wajib</span></label>
            <input id="{{ $prefix }}-name" name="name" type="text" value="{{ $nameValue }}" autocomplete="name" required data-ui-modal-focus class="ui-input mt-2" @error('name') aria-invalid="true" aria-describedby="{{ $prefix }}-name-error" @enderror>
            @error('name')<x-field-error id="{{ $prefix }}-name-error" data-ui-validation-error :message="$message" />@enderror
        </div>

        <div>
            <label for="{{ $prefix }}-username" class="ui-field-label">Username <span class="{{ $userRequiredMark }}" aria-hidden="true">*</span><span class="sr-only"> wajib</span></label>
            <input id="{{ $prefix }}-username" name="username" type="text" value="{{ $usernameValue }}" autocomplete="username" required class="ui-input mt-2" @error('username') aria-invalid="true" aria-describedby="{{ $prefix }}-username-error" @enderror>
            @error('username')<x-field-error id="{{ $prefix }}-username-error" data-ui-validation-error :message="$message" />@enderror
        </div>

        <div>
            <label for="{{ $prefix }}-email" class="ui-field-label">Email</label>
            <input id="{{ $prefix }}-email" name="email" type="email" value="{{ $emailValue }}" autocomplete="email" class="ui-input mt-2" @error('email') aria-invalid="true" aria-describedby="{{ $prefix }}-email-error" @enderror>
            @error('email')<x-field-error id="{{ $prefix }}-email-error" data-ui-validation-error :message="$message" />@enderror
        </div>

        <div>
            <label for="{{ $prefix }}-nip" class="ui-field-label">NIP</label>
            <input id="{{ $prefix }}-nip" name="nip" type="text" value="{{ $nipValue }}" inputmode="numeric" class="ui-input mt-2 tabular-nums" @error('nip') aria-invalid="true" aria-describedby="{{ $prefix }}-nip-error" @enderror>
            @error('nip')<x-field-error id="{{ $prefix }}-nip-error" data-ui-validation-error :message="$message" />@enderror
        </div>

        <div>
            <label for="{{ $prefix }}-team" class="ui-field-label">Tim Kerja <span class="{{ $userRequiredMark }}" aria-hidden="true">*</span><span class="sr-only"> wajib</span></label>
            <select id="{{ $prefix }}-team" name="team_id" required data-user-team-input class="ui-select mt-2" @error('team_id') aria-invalid="true" aria-describedby="{{ $prefix }}-team-error" @enderror>
                <option value="">Pilih tim kerja</option>
                @foreach ($teams as $team)
                    <option value="{{ $team->id }}" @selected((string) $teamValue === (string) $team->id)>{{ $team->name }}</option>
                @endforeach
            </select>
            @error('team_id')<x-field-error id="{{ $prefix }}-team-error" data-ui-validation-error :message="$message" />@enderror
        </div>

        <div class="sm:col-span-2 {{ filled($teamValue) ? '' : 'hidden' }}" data-user-team-position>
            <label for="{{ $prefix }}-team-position" class="ui-field-label">Posisi dalam Tim <span class="{{ $userRequiredMark }}" aria-hidden="true">*</span><span class="sr-only"> wajib</span></label>
            <select id="{{ $prefix }}-team-position" name="team_position" data-user-team-position-input required @disabled(! filled($teamValue)) class="ui-select mt-2" @error('team_position') aria-invalid="true" aria-describedby="{{ $prefix }}-team-position-help {{ $prefix }}-team-position-error" @else aria-describedby="{{ $prefix }}-team-position-help" @enderror>
                <option value="">Pilih posisi dalam tim</option>
                <option value="{{ \App\Enums\TeamPosition::Member->value }}" @selected($teamPositionValue === \App\Enums\TeamPosition::Member->value)>Anggota</option>
                <option value="{{ \App\Enums\TeamPosition::Chair->value }}" @selected($teamPositionValue === \App\Enums\TeamPosition::Chair->value)>Ketua Tim Kerja</option>
            </select>
            <p id="{{ $prefix }}-team-position-help" class="ui-field-help">Tentukan apakah pengguna menjadi anggota atau ketua dari tim kerja tersebut.</p>
            @error('team_position')<x-field-error id="{{ $prefix }}-team-position-error" data-ui-validation-error :message="$message" />@enderror
        </div>

        @if ($isEdit)
            <div class="sm:col-span-2">
                <label for="{{ $prefix }}-status" class="ui-field-label">Status <span class="{{ $userRequiredMark }}" aria-hidden="true">*</span><span class="sr-only"> wajib</span></label>
                <select id="{{ $prefix }}-status" name="is_active" required class="ui-select mt-2" @error('is_active') aria-invalid="true" aria-describedby="{{ $prefix }}-status-error" @enderror>
                    <option value="1" @selected((string) $statusValue === '1')>Aktif</option>
                    <option value="0" @selected((string) $statusValue === '0')>Nonaktif</option>
                </select>
                @error('is_active')<x-field-error id="{{ $prefix }}-status-error" data-ui-validation-error :message="$message" />@enderror
            </div>
        @endif

        <fieldset class="sm:col-span-2" data-user-role-group @if($roleError) aria-invalid="true" aria-describedby="{{ $prefix }}-role-help {{ $prefix }}-role-error" @else aria-describedby="{{ $prefix }}-role-help" @endif>
            <legend class="ui-field-label">Role <span class="{{ $userRequiredMark }}" aria-hidden="true">*</span><span class="sr-only"> wajib</span></legend>
            <p id="{{ $prefix }}-role-help" class="ui-field-help">Pilih satu atau beberapa role sesuai kewenangan pengguna.</p>
            <div class="mt-2 grid gap-2 sm:grid-cols-2">
                @foreach ($assignableRoles as $role)
                    <label for="{{ $prefix }}-role-{{ $role->id }}" class="{{ $userChoiceTile }}">
                        <input
                            id="{{ $prefix }}-role-{{ $role->id }}"
                            name="role_ids[]"
                            type="checkbox"
                            value="{{ $role->id }}"
                            data-user-role
                            data-role-slug="{{ $role->slug }}"
                            @checked(in_array((string) $role->id, $selectedRoleIds, true))
                            class="ui-checkbox"
                        >
                        <span class="font-semibold">{{ $role->managementLabel() }}</span>
                    </label>
                @endforeach
            </div>
            @if ($roleError)<x-field-error id="{{ $prefix }}-role-error" data-ui-validation-error :message="$roleError" />@endif
        </fieldset>
    </div>

    <fieldset data-user-skills class="{{ $hasTechnicianRole ? '' : 'hidden' }} mt-4 {{ $userFieldsetPanel }}" @if($errors->has('skill_ids')) aria-invalid="true" aria-describedby="{{ $prefix }}-skills-help {{ $prefix }}-skills-error" @else aria-describedby="{{ $prefix }}-skills-help" @endif>
        <legend class="ui-field-label">Keahlian <span class="{{ $userRequiredMark }}" aria-hidden="true">*</span><span class="sr-only"> wajib untuk Teknisi</span></legend>
        <p id="{{ $prefix }}-skills-help" class="ui-field-help">Pilih minimal satu keahlian untuk pengguna dengan role Teknisi.</p>
        <div class="mt-3 grid max-h-48 gap-2 overflow-y-auto pr-1 sm:grid-cols-2">
            @foreach ($skills as $skill)
                <label for="{{ $prefix }}-skill-{{ $skill->id }}" class="{{ $userChoiceTile }}">
                    <input
                        id="{{ $prefix }}-skill-{{ $skill->id }}"
                        name="skill_ids[]"
                        type="checkbox"
                        value="{{ $skill->id }}"
                        data-user-skill
                        @checked(in_array((string) $skill->id, $selectedSkillIds, true))
                        @disabled(! $hasTechnicianRole)
                        class="ui-checkbox"
                    >
                    <span class="font-semibold">{{ $skill->name }}</span>
                </label>
            @endforeach
        </div>
        @if ($skills->isEmpty())
            <p class="mt-3 flex items-start gap-2 rounded-[var(--tm-r-sm)] border border-[color:var(--tm-warning-200)] bg-[color:var(--tm-warning-50)] px-3 py-2.5 text-sm font-semibold text-[color:var(--tm-warning-700)]">
                <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 2.5a7.5 7.5 0 1 0 0 15 7.5 7.5 0 0 0 0-15Zm.75 4a.75.75 0 0 0-1.5 0v4a.75.75 0 0 0 1.5 0v-4ZM10 13a.9.9 0 1 0 0 1.8.9.9 0 0 0 0-1.8Z" clip-rule="evenodd" /></svg>
                <span class="min-w-0">Belum ada keahlian aktif. Tambahkan keahlian terlebih dahulu.</span>
            </p>
        @endif
        @error('skill_ids')<x-field-error id="{{ $prefix }}-skills-error" data-ui-validation-error :message="$message" />@enderror
    </fieldset>

    @if (! $isEdit)
        <div class="mt-5 grid gap-4 border-t border-[color:var(--tm-border-subtle)] pt-5 sm:grid-cols-2">
            <div>
                <label for="{{ $prefix }}-password" class="ui-field-label">Password <span class="{{ $userRequiredMark }}" aria-hidden="true">*</span><span class="sr-only"> wajib</span></label>
                <input id="{{ $prefix }}-password" name="temporary_password" type="password" autocomplete="new-password" required class="ui-input mt-2" @error('temporary_password') aria-invalid="true" aria-describedby="{{ $prefix }}-password-help {{ $prefix }}-password-error" @enderror>
                <p id="{{ $prefix }}-password-help" class="ui-field-help">Minimal 12 karakter dengan huruf besar, huruf kecil, angka, dan simbol.</p>
                @error('temporary_password')<x-field-error id="{{ $prefix }}-password-error" data-ui-validation-error :message="$message" />@enderror
            </div>

            <div>
                <label for="{{ $prefix }}-password-confirmation" class="ui-field-label">Konfirmasi Password <span class="{{ $userRequiredMark }}" aria-hidden="true">*</span><span class="sr-only"> wajib</span></label>
                <input id="{{ $prefix }}-password-confirmation" name="temporary_password_confirmation" type="password" autocomplete="new-password" required class="ui-input mt-2" @error('temporary_password_confirmation') aria-invalid="true" aria-describedby="{{ $prefix }}-password-confirmation-error" @enderror>
                @error('temporary_password_confirmation')<x-field-error id="{{ $prefix }}-password-confirmation-error" data-ui-validation-error :message="$message" />@enderror
            </div>
        </div>
    @else
        <p class="mt-5 flex items-start gap-2 rounded-[var(--tm-r-md)] border border-[color:var(--tm-border-subtle)] bg-[color:var(--tm-sunken)] px-4 py-3 text-xs leading-5 text-[color:var(--tm-text-muted)]">
            <svg class="mt-0.5 h-4 w-4 shrink-0 text-[color:var(--tm-brand-600)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="9" /><path stroke-linecap="round" d="M12 11v5M12 8h.01" /></svg>
            <span class="min-w-0">Untuk mengganti password, gunakan aksi <span class="font-bold text-[color:var(--tm-text-secondary)]">Ganti password</span> pada daftar pengguna.</span>
        </p>
    @endif

    <div class="mt-5 flex flex-col-reverse gap-2 border-t border-[color:var(--tm-border-subtle)] pt-5 sm:flex-row sm:justify-end">
        @if ($isModal)
            <button type="button" data-ui-modal-close class="ui-btn ui-btn-ghost">Tutup</button>
        @else
            <a href="{{ route('admin.users.index') }}" class="ui-btn ui-btn-ghost">Batal</a>
        @endif
        <button type="submit" data-ui-modal-submit class="ui-btn ui-btn-primary" data-user-form-submit>
            <span data-ui-modal-label>{{ $isEdit ? 'Simpan perubahan' : 'Simpan pengguna' }}</span>
            <span data-ui-modal-loading class="hidden">Menyimpan...</span>
        </button>
    </div>
</form>
