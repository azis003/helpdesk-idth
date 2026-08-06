<?php

namespace App\Http\Requests;

use App\Enums\Priority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class TriageTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isActive() === true;
    }

    public function rules(): array
    {
        return [
            'outcome' => ['required', Rule::in(['self', 'tier_2', 'reject'])],
            'problem_category_id' => ['nullable', 'integer', 'exists:problem_categories,id'],
            'priority' => ['required', Rule::in(array_keys(Priority::labels()))],
            'category_reason' => ['nullable', 'string', 'max:1000'],
            'priority_reason' => ['nullable', 'string', 'max:1000'],
            'assigned_to_id' => ['nullable', 'integer', 'exists:users,id'],
            'rejection_reason' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $outcome = (string) $this->input('outcome');

            if ($outcome !== 'reject' && ! filled($this->input('problem_category_id'))) {
                $validator->errors()->add('problem_category_id', 'Kategori masalah wajib dipilih untuk melanjutkan triase.');
            }

            if ($outcome === 'tier_2' && ! filled($this->input('assigned_to_id'))) {
                $validator->errors()->add('assigned_to_id', 'Teknisi Tier 2 wajib dipilih.');
            }

            if ($outcome === 'reject' && ! filled($this->input('rejection_reason'))) {
                $validator->errors()->add('rejection_reason', 'Alasan penolakan wajib diisi.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'outcome.required' => 'Hasil triase wajib dipilih.',
            'outcome.in' => 'Hasil triase tidak valid.',
            'problem_category_id.integer' => 'Kategori masalah yang dipilih tidak valid.',
            'problem_category_id.exists' => 'Kategori masalah yang dipilih tidak ditemukan.',
            'priority.required' => 'Prioritas tiket wajib dipilih.',
            'priority.in' => 'Prioritas tiket tidak valid.',
            'category_reason.max' => 'Alasan perubahan kategori maksimal 1.000 karakter.',
            'priority_reason.max' => 'Alasan perubahan prioritas maksimal 1.000 karakter.',
            'assigned_to_id.integer' => 'Teknisi Tier 2 yang dipilih tidak valid.',
            'assigned_to_id.exists' => 'Teknisi Tier 2 yang dipilih tidak ditemukan.',
            'rejection_reason.max' => 'Alasan penolakan maksimal 2.000 karakter.',
        ];
    }
}
