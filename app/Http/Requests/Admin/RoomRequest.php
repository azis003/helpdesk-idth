<?php

namespace App\Http\Requests\Admin;

use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isActive() === true && $this->user()->hasRole(Role::SuperAdmin);
    }

    public function rules(): array
    {
        $room = $this->route('room');
        $floor = $this->route('floor');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('rooms', 'name')
                    ->where(fn ($query) => $query->where('floor_id', $floor?->getKey() ?? $room?->floor_id))
                    ->ignore($room),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama ruangan wajib diisi.',
            'name.unique' => 'Nama ruangan sudah digunakan pada lantai tersebut.',
        ];
    }
}
