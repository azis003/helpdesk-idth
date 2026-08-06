<?php

namespace App\Http\Requests\Admin;

use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCategorySkillsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(Role::SuperAdmin) ?? false;
    }

    public function rules(): array
    {
        return [
            'skill_ids' => ['nullable', 'array'],
            'skill_ids.*' => ['integer', 'distinct', 'exists:skills,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'skill_ids.array' => 'Daftar pemetaan keahlian tidak valid.',
            'skill_ids.*.exists' => 'Keahlian yang dipilih tidak tersedia.',
        ];
    }
}
