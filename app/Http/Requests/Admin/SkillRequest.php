<?php

namespace App\Http\Requests\Admin;

use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SkillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(Role::SuperAdmin) ?? false;
    }

    public function rules(): array
    {
        $skill = $this->route('skill');

        return [
            'name' => ['required', 'string', 'max:150', Rule::unique('skills', 'name')->ignore($skill)],
            'slug' => ['nullable', 'string', 'max:150', 'alpha_dash', Rule::unique('skills', 'slug')->ignore($skill)],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama keahlian wajib diisi.',
            'name.unique' => 'Nama keahlian sudah digunakan.',
            'slug.alpha_dash' => 'Kode keahlian hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
            'slug.unique' => 'Kode keahlian sudah digunakan.',
        ];
    }
}
