<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'confirm_reset' => ['accepted'],
            'temporary_password' => [
                'required',
                'confirmed',
                Password::min(12)->mixedCase()->numbers()->symbols(),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'confirm_reset.accepted' => 'Konfirmasi reset password wajib dicentang.',
            'temporary_password.required' => 'Password sementara wajib diisi.',
            'temporary_password.min' => 'Password sementara minimal :min karakter.',
            'temporary_password.mixed' => 'Password sementara harus mengandung huruf besar dan huruf kecil.',
            'temporary_password.numbers' => 'Password sementara harus mengandung angka.',
            'temporary_password.symbols' => 'Password sementara harus mengandung simbol.',
            'temporary_password.confirmed' => 'Konfirmasi password sementara tidak sama.',
        ];
    }
}
