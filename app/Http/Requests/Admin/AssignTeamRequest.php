<?php

namespace App\Http\Requests\Admin;

use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;

class AssignTeamRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->input('team_id') === null && $this->input('work_team_id') !== null) {
            $this->merge(['team_id' => $this->input('work_team_id')]);
        }
    }

    public function authorize(): bool
    {
        return $this->user()?->hasRole(Role::SuperAdmin) ?? false;
    }

    public function rules(): array
    {
        return [
            'team_id' => ['required', 'integer', 'exists:work_teams,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'team_id.required' => 'Tim kerja wajib dipilih.',
            'team_id.exists' => 'Tim kerja yang dipilih tidak tersedia.',
        ];
    }
}
