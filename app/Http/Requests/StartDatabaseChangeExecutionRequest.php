<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StartDatabaseChangeExecutionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isActive() === true;
    }
}
