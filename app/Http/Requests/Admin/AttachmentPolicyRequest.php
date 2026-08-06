<?php

namespace App\Http\Requests\Admin;

use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AttachmentPolicyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isActive() === true && $this->user()->hasRole(Role::SuperAdmin);
    }

    public function rules(): array
    {
        return [
            'service_type_id' => ['nullable', 'integer', 'exists:service_types,id'],
            'type_key' => ['required', 'string', 'max:60', 'alpha_dash'],
            'label' => ['required', 'string', 'max:150'],
            'max_file_size_kb' => ['required', 'integer', 'min:1', 'max:1048576'],
            'max_file_count' => ['required', 'integer', 'min:1', 'max:100'],
            'allowed_mimes_text' => ['nullable', 'string', 'max:2000'],
            'allowed_extensions_text' => ['nullable', 'string', 'max:1000'],
            'visibility' => ['required', Rule::in(['requester', 'internal', 'both'])],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /** @return array<string, mixed> */
    public function payload(): array
    {
        $data = $this->validated();
        $data['service_type_id'] = ($data['service_type_id'] ?? null) ?: null;
        $data['allowed_mimes'] = $this->parseList($data['allowed_mimes_text'] ?? '', false);
        $data['allowed_extensions'] = $this->parseList($data['allowed_extensions_text'] ?? '', true);
        $data['is_active'] = array_key_exists('is_active', $data)
            ? (bool) $data['is_active']
            : $this->route('attachmentPolicy') === null;

        return $data;
    }

    /** @return list<string> */
    private function parseList(string $text, bool $extension): array
    {
        return collect(preg_split('/[\r\n,]+/', $text) ?: [])
            ->map(function (string $value) use ($extension): string {
                $value = strtolower(trim($value));

                return $extension ? ltrim($value, '.') : $value;
            })
            ->filter()
            ->map(fn (string $value): string => $extension ? Str::lower($value) : $value)
            ->unique()
            ->values()
            ->all();
    }
}
