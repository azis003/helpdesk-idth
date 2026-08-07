<?php

namespace App\Http\Requests\Admin;

use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceSkillsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isActive() === true && $this->user()->hasRole(Role::SuperAdmin);
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
            'skill_ids.array' => 'Daftar keahlian tidak valid.',
            'skill_ids.*.integer' => 'Keahlian yang dipilih tidak valid.',
            'skill_ids.*.distinct' => 'Keahlian tidak boleh dipilih lebih dari sekali.',
            'skill_ids.*.exists' => 'Keahlian yang dipilih tidak ditemukan.',
        ];
    }
}
