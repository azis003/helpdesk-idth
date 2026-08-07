<?php

namespace App\Http\Requests\Admin;

use App\Enums\Role;
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
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['integer', 'distinct', 'exists:roles,id'],
            'team_id' => ['nullable', 'integer', 'exists:work_teams,id'],
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
            'role_ids.array' => 'Daftar role tidak valid.',
            'role_ids.*.exists' => 'Role yang dipilih tidak tersedia.',
            'team_id.exists' => 'Tim kerja yang dipilih tidak tersedia.',
            'skill_ids.array' => 'Daftar keahlian tidak valid.',
            'skill_ids.*.exists' => 'Keahlian yang dipilih tidak tersedia.',
        ];
    }
}
