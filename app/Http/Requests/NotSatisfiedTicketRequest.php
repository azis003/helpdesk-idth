<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NotSatisfiedTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isActive() === true;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'Alasan hasil belum sesuai wajib diisi.',
            'reason.max' => 'Alasan hasil belum sesuai maksimal 5.000 karakter.',
        ];
    }
}
