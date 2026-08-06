<?php

namespace App\Http\Requests\Admin;

use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRolesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(Role::SuperAdmin) ?? false;
    }

    public function rules(): array
    {
        return [
            'role_ids' => ['nullable', 'array'],
            'role_ids.*' => ['integer', 'distinct', 'exists:roles,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'role_ids.array' => 'Daftar role tidak valid.',
            'role_ids.*.exists' => 'Role yang dipilih tidak tersedia.',
        ];
    }
}
