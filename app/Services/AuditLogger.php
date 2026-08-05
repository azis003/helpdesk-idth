<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLogger
{
    /**
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     */
    public function record(
        ?User $actor,
        string $action,
        string $outcome,
        ?Model $subject = null,
        ?string $reason = null,
        ?array $before = null,
        ?array $after = null,
        ?Request $request = null,
    ): AuditLog {
        $request ??= app()->bound('request') ? request() : null;

        return AuditLog::query()->create([
            'user_id' => $actor?->getKey(),
            'action' => $action,
            'outcome' => $outcome,
            'auditable_type' => $subject?->getMorphClass(),
            'auditable_id' => $subject?->getKey(),
            'reason' => $reason,
            'before' => $before,
            'after' => $after,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'request_id' => $request?->header('X-Request-ID'),
        ]);
    }

    public function succeeded(
        ?User $actor,
        string $action,
        ?Model $subject = null,
        ?string $reason = null,
        ?array $before = null,
        ?array $after = null,
    ): AuditLog {
        return $this->record($actor, $action, 'succeeded', $subject, $reason, $before, $after);
    }

    public function denied(
        ?User $actor,
        string $action,
        ?Model $subject = null,
        ?string $reason = null,
    ): AuditLog {
        return $this->record($actor, $action, 'denied', $subject, $reason);
    }
}
