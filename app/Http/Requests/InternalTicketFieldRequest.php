<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InternalTicketFieldRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! is_array($this->input('internal_fields')) && is_array($this->input('fields'))) {
            $this->merge(['internal_fields' => $this->input('fields')]);
        }

        if (! is_array($this->input('internal_field_versions')) && is_array($this->input('field_versions'))) {
            $this->merge(['internal_field_versions' => $this->input('field_versions')]);
        }
    }

    public function authorize(): bool
    {
        return $this->user()?->isActive() === true;
    }

    public function rules(): array
    {
        return [
            'internal_fields' => ['required', 'array'],
            'internal_fields.*' => ['nullable'],
            'internal_field_versions' => ['nullable', 'array'],
            'internal_field_versions.*' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'internal_fields.required' => 'Field internal Tim TI wajib dikirim.',
            'internal_fields.array' => 'Format field internal Tim TI tidak valid.',
            'internal_fields.*.nullable' => 'Nilai field internal Tim TI tidak valid.',
            'internal_field_versions.array' => 'Versi definisi field internal tidak valid.',
            'internal_field_versions.*.integer' => 'Versi definisi field internal harus berupa angka.',
            'internal_field_versions.*.min' => 'Versi definisi field internal tidak valid.',
        ];
    }
}
