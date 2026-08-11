<?php

namespace App\Http\Requests;

use App\Enums\Priority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeTicketPriorityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isActive() === true;
    }

    public function rules(): array
    {
        return [
            'priority' => ['required', Rule::in(array_keys(Priority::labels()))],
            'reason' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'priority.required' => 'Prioritas tiket wajib dipilih.',
            'priority.in' => 'Prioritas tiket tidak valid.',
            'reason.required' => 'Alasan perubahan prioritas wajib diisi.',
            'reason.max' => 'Alasan perubahan prioritas maksimal 1.000 karakter.',
        ];
    }
}
