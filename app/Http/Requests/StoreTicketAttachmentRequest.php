<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isActive() === true;
    }

    public function rules(): array
    {
        return [
            'attachments' => ['required', 'array', 'min:1'],
            'attachments.*' => ['nullable', 'array'],
            'attachments.*.*' => ['nullable', 'file', 'max:1048576'],
        ];
    }

    public function messages(): array
    {
        return [
            'attachments.required' => 'Pilih minimal satu lampiran.',
            'attachments.array' => 'Format lampiran tidak valid.',
            'attachments.min' => 'Pilih minimal satu lampiran.',
            'attachments.*.array' => 'Format kelompok lampiran tidak valid.',
            'attachments.*.*.file' => 'Salah satu lampiran tidak valid.',
            'attachments.*.*.max' => 'Ukuran salah satu lampiran terlalu besar.',
        ];
    }
}
