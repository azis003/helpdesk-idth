<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompleteTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isActive() === true;
    }

    public function rules(): array
    {
        return [
            'solution' => ['required', 'string', 'max:20000'],
        ];
    }

    public function messages(): array
    {
        return [
            'solution.required' => 'Solusi wajib diisi sebelum tiket menunggu konfirmasi.',
            'solution.max' => 'Solusi maksimal 20.000 karakter.',
        ];
    }
}
