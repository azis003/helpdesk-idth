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
    $roleError = $errors->first('role_ids') ?: $errors->first('role_ids.*');
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
            <label for="{{ $prefix }}-name" class="ui-field-label">Nama Pegawai <span class="text-rose-600" aria-hidden="true">*</span><span class="sr-only"> wajib</span></label>
            <input id="{{ $prefix }}-name" name="name" type="text" value="{{ $nameValue }}" autocomplete="name" required data-ui-modal-focus class="ui-input mt-2" @error('name') aria-invalid="true" aria-describedby="{{ $prefix }}-name-error" @enderror>
            @error('name')<p id="{{ $prefix }}-name-error" class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="{{ $prefix }}-username" class="ui-field-label">Username <span class="text-rose-600" aria-hidden="true">*</span><span class="sr-only"> wajib</span></label>
            <input id="{{ $prefix }}-username" name="username" type="text" value="{{ $usernameValue }}" autocomplete="username" required class="ui-input mt-2" @error('username') aria-invalid="true" aria-describedby="{{ $prefix }}-username-error" @enderror>
            @error('username')<p id="{{ $prefix }}-username-error" class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="{{ $prefix }}-email" class="ui-field-label">Email</label>
            <input id="{{ $prefix }}-email" name="email" type="email" value="{{ $emailValue }}" autocomplete="email" class="ui-input mt-2" @error('email') aria-invalid="true" aria-describedby="{{ $prefix }}-email-error" @enderror>
            @error('email')<p id="{{ $prefix }}-email-error" class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="{{ $prefix }}-nip" class="ui-field-label">NIP</label>
            <input id="{{ $prefix }}-nip" name="nip" type="text" value="{{ $nipValue }}" inputmode="numeric" class="ui-input mt-2" @error('nip') aria-invalid="true" aria-describedby="{{ $prefix }}-nip-error" @enderror>
            @error('nip')<p id="{{ $prefix }}-nip-error" class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="{{ $prefix }}-team" class="ui-field-label">Tim Kerja <span class="text-rose-600" aria-hidden="true">*</span><span class="sr-only"> wajib</span></label>
            <select id="{{ $prefix }}-team" name="team_id" required class="ui-select mt-2" @error('team_id') aria-invalid="true" aria-describedby="{{ $prefix }}-team-error" @enderror>
                <option value="">Pilih tim kerja</option>
                @foreach ($teams as $team)
                    <option value="{{ $team->id }}" @selected((string) $teamValue === (string) $team->id)>{{ $team->name }}</option>
                @endforeach
            </select>
            @error('team_id')<p id="{{ $prefix }}-team-error" class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
        </div>

        <fieldset class="sm:col-span-2" data-user-role-group @if($roleError) aria-invalid="true" aria-describedby="{{ $prefix }}-role-help {{ $prefix }}-role-error" @else aria-describedby="{{ $prefix }}-role-help" @endif>
            <legend class="ui-field-label">Role <span class="text-rose-600" aria-hidden="true">*</span><span class="sr-only"> wajib</span></legend>
            <p id="{{ $prefix }}-role-help" class="ui-field-help">Pilih satu atau beberapa role sesuai kewenangan pengguna.</p>
            <div class="mt-2 grid gap-2 sm:grid-cols-2">
                @foreach ($roles as $role)
                    <label for="{{ $prefix }}-role-{{ $role->id }}" class="flex min-h-11 cursor-pointer items-center gap-3 rounded-lg border border-[#d7e0e4] bg-white px-3 py-2.5 text-sm text-[#35505b] transition hover:border-[#79c7e8] hover:bg-[#f4fbfe]">
                        <input
                            id="{{ $prefix }}-role-{{ $role->id }}"
                            name="role_ids[]"
                            type="checkbox"
                            value="{{ $role->id }}"
                            data-user-role
                            data-role-slug="{{ $role->slug }}"
                            @checked(in_array((string) $role->id, $selectedRoleIds, true))
                            class="h-4 w-4 rounded border-[#b8c8cf] text-[#087fc1] focus:ring-[#0b98e5]"
                        >
                        <span class="font-semibold">{{ $role->managementLabel() }}</span>
                    </label>
                @endforeach
            </div>
            @if ($roleError)<p id="{{ $prefix }}-role-error" class="mt-2 text-sm text-rose-700">{{ $roleError }}</p>@endif
        </fieldset>
    </div>

    <fieldset data-user-skills class="{{ $hasTechnicianRole ? '' : 'hidden' }} mt-4 rounded-xl border border-[#dbe7eb] bg-[#f8fbfc] p-4" @if($errors->has('skill_ids')) aria-invalid="true" aria-describedby="{{ $prefix }}-skills-help {{ $prefix }}-skills-error" @else aria-describedby="{{ $prefix }}-skills-help" @endif>
        <legend class="ui-field-label">Keahlian <span class="text-rose-600" aria-hidden="true">*</span><span class="sr-only"> wajib untuk Teknisi</span></legend>
        <p id="{{ $prefix }}-skills-help" class="ui-field-help">Pilih minimal satu keahlian untuk pengguna dengan role Teknisi.</p>
        <div class="mt-3 grid max-h-48 gap-2 overflow-y-auto pr-1 sm:grid-cols-2">
            @foreach ($skills as $skill)
                <label for="{{ $prefix }}-skill-{{ $skill->id }}" class="flex min-h-11 cursor-pointer items-center gap-3 rounded-lg border border-[#d7e0e4] bg-white px-3 py-2.5 text-sm text-[#35505b] transition hover:border-[#79c7e8] hover:bg-[#f4fbfe]">
                    <input
                        id="{{ $prefix }}-skill-{{ $skill->id }}"
                        name="skill_ids[]"
                        type="checkbox"
                        value="{{ $skill->id }}"
                        data-user-skill
                        @checked(in_array((string) $skill->id, $selectedSkillIds, true))
                        @disabled(! $hasTechnicianRole)
                        class="h-4 w-4 rounded border-[#b8c8cf] text-[#087fc1] focus:ring-[#0b98e5]"
                    >
                    <span class="font-semibold">{{ $skill->name }}</span>
                </label>
            @endforeach
        </div>
        @if ($skills->isEmpty())
            <p class="mt-2 text-sm text-amber-700">Belum ada keahlian aktif. Tambahkan keahlian terlebih dahulu.</p>
        @endif
        @error('skill_ids')<p id="{{ $prefix }}-skills-error" class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
    </fieldset>

    @if (! $isEdit)
        <div class="mt-4 grid gap-4 border-t border-[#edf2f4] pt-4 sm:grid-cols-2">
            <div>
                <label for="{{ $prefix }}-password" class="ui-field-label">Password <span class="text-rose-600" aria-hidden="true">*</span><span class="sr-only"> wajib</span></label>
                <input id="{{ $prefix }}-password" name="temporary_password" type="password" autocomplete="new-password" required class="ui-input mt-2" @error('temporary_password') aria-invalid="true" aria-describedby="{{ $prefix }}-password-help {{ $prefix }}-password-error" @enderror>
                <p id="{{ $prefix }}-password-help" class="ui-field-help">Minimal 12 karakter dengan huruf besar, huruf kecil, angka, dan simbol.</p>
                @error('temporary_password')<p id="{{ $prefix }}-password-error" class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="{{ $prefix }}-password-confirmation" class="ui-field-label">Konfirmasi Password <span class="text-rose-600" aria-hidden="true">*</span><span class="sr-only"> wajib</span></label>
                <input id="{{ $prefix }}-password-confirmation" name="temporary_password_confirmation" type="password" autocomplete="new-password" required class="ui-input mt-2" @error('temporary_password_confirmation') aria-invalid="true" aria-describedby="{{ $prefix }}-password-confirmation-error" @enderror>
                @error('temporary_password_confirmation')<p id="{{ $prefix }}-password-confirmation-error" class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
            </div>
        </div>
    @else
        <p class="mt-4 rounded-xl bg-[#f8fbfc] px-4 py-3 text-xs leading-5 text-[#6f858e]">Untuk mengganti password, gunakan aksi <span class="font-bold">Ganti password</span> pada daftar pengguna.</p>
    @endif

    <div class="mt-5 flex flex-col-reverse gap-2 border-t border-[#edf2f4] pt-4 sm:flex-row sm:justify-end">
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
