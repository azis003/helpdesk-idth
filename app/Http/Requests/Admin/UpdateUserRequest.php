<?php

namespace App\Http\Requests\Admin;

use App\Enums\Role;
use App\Enums\TeamPosition;
use App\Models\Role as RoleModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'role_ids' => $this->input('role_ids', []),
            'skill_ids' => $this->input('skill_ids', []),
            'team_id' => $this->input('team_id') === '' ? null : $this->input('team_id'),
            'team_position' => $this->input('team_position') === '' ? null : $this->input('team_position'),
            'is_active' => $this->has('is_active') ? $this->boolean('is_active') : null,
        ]);
    }

    public function authorize(): bool
    {
        return $this->user()?->hasRole(Role::SuperAdmin) ?? false;
    }

    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:80', 'alpha_dash', Rule::unique('users', 'username')->ignore($user)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'nip' => ['nullable', 'string', 'max:32', Rule::unique('users', 'nip')->ignore($user)],
            'role_ids' => ['required', 'array', 'min:1'],
            'role_ids.*' => ['integer', 'distinct', 'exists:roles,id'],
            'team_id' => ['required', 'integer', 'exists:work_teams,id'],
            'team_position' => ['required_with:team_id', Rule::enum(TeamPosition::class)],
            'skill_ids' => ['nullable', 'array'],
            'skill_ids.*' => ['integer', 'distinct', 'exists:skills,id'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama pengguna wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
            'username.unique' => 'Username sudah digunakan.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
            'nip.unique' => 'NIP sudah digunakan.',
            'role_ids.array' => 'Daftar role tidak valid.',
            'role_ids.required' => 'Role pengguna wajib dipilih.',
            'role_ids.min' => 'Role pengguna wajib dipilih.',
            'role_ids.*.exists' => 'Role yang dipilih tidak tersedia.',
            'team_id.required' => 'Tim kerja wajib dipilih.',
            'team_id.exists' => 'Tim kerja yang dipilih tidak tersedia.',
            'team_position.required_with' => 'Posisi dalam tim wajib dipilih setelah tim kerja dipilih.',
            'team_position.enum' => 'Posisi dalam tim tidak valid.',
            'skill_ids.array' => 'Daftar keahlian tidak valid.',
            'skill_ids.*.exists' => 'Keahlian yang dipilih tidak tersedia.',
            'is_active.required' => 'Status pengguna wajib dipilih.',
            'is_active.boolean' => 'Status pengguna tidak valid.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $roleIds = collect($this->input('role_ids', []))
                ->filter(fn ($roleId): bool => $roleId !== null && $roleId !== '')
                ->map(fn ($roleId): int => (int) $roleId)
                ->values();

            $hasTechnicianRole = $roleIds->isNotEmpty()
                && RoleModel::query()
                    ->whereIn('id', $roleIds->all())
                    ->where('slug', Role::AgenTier2->value)
                    ->exists();

            if ($hasTechnicianRole && collect($this->input('skill_ids', []))->filter()->isEmpty()) {
                $validator->errors()->add('skill_ids', 'Keahlian wajib diisi untuk role Teknisi.');
            }
        });
    }
}
