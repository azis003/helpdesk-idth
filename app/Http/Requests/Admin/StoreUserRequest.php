<?php

namespace App\Http\Requests\Admin;

use App\Enums\Role;
use App\Enums\TeamPosition;
use App\Models\Role as RoleModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(Role::SuperAdmin) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:80', 'alpha_dash', Rule::unique('users', 'username')],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')],
            'nip' => ['nullable', 'string', 'max:32', Rule::unique('users', 'nip')],
            'temporary_password' => [
                'required',
                'confirmed',
                Password::min(12)->mixedCase()->numbers()->symbols(),
            ],
            'role_ids' => ['required', 'array', 'min:1'],
            'role_ids.*' => ['integer', 'distinct', 'exists:roles,id'],
            'team_id' => ['required', 'integer', 'exists:work_teams,id'],
            'team_position' => ['required_with:team_id', Rule::enum(TeamPosition::class)],
            'skill_ids' => ['nullable', 'array'],
            'skill_ids.*' => ['integer', 'distinct', 'exists:skills,id'],
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
            'temporary_password.required' => 'Password awal wajib diisi.',
            'temporary_password.confirmed' => 'Konfirmasi password awal tidak sama.',
            'temporary_password_confirmation.required' => 'Konfirmasi password wajib diisi.',
            'temporary_password.min' => 'Password awal minimal :min karakter.',
            'temporary_password.mixed' => 'Password awal harus mengandung huruf besar dan huruf kecil.',
            'temporary_password.numbers' => 'Password awal harus mengandung angka.',
            'temporary_password.symbols' => 'Password awal harus mengandung simbol.',
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
