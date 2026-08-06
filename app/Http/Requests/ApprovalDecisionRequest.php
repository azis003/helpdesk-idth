<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApprovalDecisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isActive() === true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('decision_note')) {
            $fallback = $this->input('note', $this->input('reason'));

            if (filled($fallback)) {
                $this->merge(['decision_note' => $fallback]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'decision_note' => [
                $this->routeIs('approvals.reject') ? 'required' : 'nullable',
                'string',
                'max:10000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'decision_note.required' => 'Catatan wajib diisi untuk keputusan Tidak Setuju.',
            'decision_note.string' => 'Catatan keputusan harus berupa teks.',
            'decision_note.max' => 'Catatan keputusan maksimal 10.000 karakter.',
        ];
    }
}
