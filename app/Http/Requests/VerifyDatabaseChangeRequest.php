<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyDatabaseChangeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isActive() === true;
    }

    public function rules(): array
    {
        return [
            'verification_result' => ['nullable', 'string', 'max:20000'],
            'verification_notes' => ['nullable', 'string', 'max:20000'],
            // Aliases keep the endpoint compatible with integrations that use
            // the shorter result/notes field names.
            'result' => ['nullable', 'string', 'max:20000'],
            'notes' => ['nullable', 'string', 'max:20000'],
        ];
    }

    public function messages(): array
    {
        return [
            'verification_result.max' => 'Hasil verifikasi maksimal 20.000 karakter.',
            'verification_notes.max' => 'Catatan verifikasi maksimal 20.000 karakter.',
            'result.max' => 'Hasil verifikasi maksimal 20.000 karakter.',
            'notes.max' => 'Catatan verifikasi maksimal 20.000 karakter.',
        ];
    }
}
