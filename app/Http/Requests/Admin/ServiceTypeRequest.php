<?php

namespace App\Http\Requests\Admin;

use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ServiceTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isActive() === true && $this->user()->hasRole(Role::SuperAdmin);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'ticket_class' => ['nullable', Rule::in(['INC', 'REQ', 'CHG'])],
            'variants' => ['nullable', 'array'],
            'variants.*.code' => ['required', Rule::in(['repair', 'request'])],
            'variants.*.label' => ['required', 'string', 'max:150'],
            'variants.*.sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama layanan wajib diisi.',
            'ticket_class.in' => 'Kelas nomor harus INC, REQ, atau CHG.',
            'variants.*.label.required' => 'Label subjenis wajib diisi.',
            'variants.*.code.in' => 'Subjenis layanan tidak dikenali.',
        ];
    }
}
