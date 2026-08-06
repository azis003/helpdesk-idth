<?php

namespace App\Http\Requests\Admin;

use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BuildingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isActive() === true && $this->user()->hasRole(Role::SuperAdmin);
    }

    public function rules(): array
    {
        $building = $this->route('building');

        return [
            'name' => ['required', 'string', 'max:150', Rule::unique('buildings', 'name')->ignore($building)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama gedung wajib diisi.',
            'name.unique' => 'Nama gedung sudah digunakan.',
        ];
    }
}
