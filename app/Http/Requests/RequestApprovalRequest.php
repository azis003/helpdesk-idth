<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RequestApprovalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isActive() === true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('reason') && $this->filled('note')) {
            $this->merge(['reason' => $this->input('note')]);
        }
    }

    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.string' => 'Catatan permintaan persetujuan harus berupa teks.',
            'reason.max' => 'Catatan permintaan persetujuan maksimal 1.000 karakter.',
        ];
    }
}
