<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ThirdPartyWaitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isActive() === true;
    }

    public function rules(): array
    {
        return [
            'third_party_name' => ['required', 'string', 'max:150'],
            'follow_up_date' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'third_party_name.required' => 'Nama pihak ketiga wajib diisi.',
            'third_party_name.max' => 'Nama pihak ketiga maksimal 150 karakter.',
            'follow_up_date.date' => 'Tanggal tindak lanjut tidak valid.',
            'note.max' => 'Catatan ketergantungan maksimal 1.000 karakter.',
        ];
    }
}
