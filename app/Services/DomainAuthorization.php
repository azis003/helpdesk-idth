<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

class DomainAuthorization
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function authorize(User $actor, string $ability, mixed $subject, string $action): void
    {
        $inspection = Gate::forUser($actor)->inspect($ability, $subject);

        if ($inspection->allowed()) {
            return;
        }

        $reason = $inspection->message() ?: 'Pemeriksaan otorisasi ditolak.';
        $this->auditLogger->denied(
            $actor,
            $action,
            $subject instanceof Model ? $subject : null,
            $reason,
        );

        throw new AuthorizationException('Anda tidak memiliki izin untuk melakukan aksi ini.');
    }
}
