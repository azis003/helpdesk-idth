<?php

namespace App\Http\Requests\Admin;

use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;

class ApproverAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isActive() === true && $this->user()->hasRole(Role::SuperAdmin);
    }

    public function rules(): array
    {
        return [
            'replacement_user_id' => ['required', 'integer', 'exists:users,id'],
            'transfer_pending_approvals' => ['required', 'accepted'],
            'reason' => ['required', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'replacement_user_id.required' => 'Pilih pengguna pengganti.',
            'replacement_user_id.exists' => 'Pengguna pengganti tidak tersedia.',
            'transfer_pending_approvals.accepted' => 'Konfirmasi pemindahan approval tertunda wajib dicentang.',
            'reason.required' => 'Alasan penetapan atau penggantian wajib diisi.',
        ];
    }
}
