<?php

namespace App\Http\Requests\Admin;

use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FloorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isActive() === true && $this->user()->hasRole(Role::SuperAdmin);
    }

    public function rules(): array
    {
        $floor = $this->route('floor');
        $building = $this->route('building');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('floors', 'name')
                    ->where(fn ($query) => $query->where('building_id', $building?->getKey() ?? $floor?->building_id))
                    ->ignore($floor),
            ],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama lantai wajib diisi.',
            'name.unique' => 'Nama lantai sudah digunakan pada gedung tersebut.',
        ];
    }
}
