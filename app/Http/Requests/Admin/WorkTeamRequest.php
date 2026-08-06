<?php

namespace App\Http\Requests\Admin;

use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WorkTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(Role::SuperAdmin) ?? false;
    }

    public function rules(): array
    {
        $team = $this->route('workTeam');

        return [
            'name' => ['required', 'string', 'max:150', Rule::unique('work_teams', 'name')->ignore($team)],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama tim wajib diisi.',
            'name.unique' => 'Nama tim sudah digunakan.',
            'description.max' => 'Deskripsi tim terlalu panjang.',
        ];
    }
}
