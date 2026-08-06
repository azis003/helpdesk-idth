<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isActive() === true;
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:10000'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['nullable', 'array'],
            'attachments.*.*' => ['nullable', 'file', 'max:1048576'],
        ];
    }

    public function messages(): array
    {
        return [
            'body.required' => 'Isi komentar wajib ditulis.',
            'body.max' => 'Isi komentar maksimal 10.000 karakter.',
            'attachments.array' => 'Format lampiran tidak valid.',
            'attachments.*.array' => 'Format kelompok lampiran tidak valid.',
            'attachments.*.*.file' => 'Salah satu lampiran tidak valid.',
            'attachments.*.*.max' => 'Ukuran salah satu lampiran terlalu besar.',
        ];
    }
}
