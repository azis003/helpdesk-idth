<?php

namespace App\Http\Requests\Admin;

use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProblemCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(Role::SuperAdmin) ?? false;
    }

    public function rules(): array
    {
        $category = $this->route('problemCategory');

        return [
            'name' => ['required', 'string', 'max:150', Rule::unique('problem_categories', 'name')->ignore($category)],
            'slug' => ['nullable', 'string', 'max:150', 'alpha_dash', Rule::unique('problem_categories', 'slug')->ignore($category)],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique' => 'Nama kategori sudah digunakan.',
            'slug.alpha_dash' => 'Kode kategori hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
            'slug.unique' => 'Kode kategori sudah digunakan.',
        ];
    }
}
