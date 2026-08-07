<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use App\Support\SensitiveDataSanitizer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuditLogger
{
    private readonly SensitiveDataSanitizer $sanitizer;

    public function __construct(?SensitiveDataSanitizer $sanitizer = null)
    {
        $this->sanitizer = $sanitizer ?? new SensitiveDataSanitizer;
    }

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
        ?array $context = null,
    ): AuditLog {
        $request ??= app()->bound('request') ? request() : null;

        if (! in_array($outcome, ['succeeded', 'denied'], true)) {
            throw new \InvalidArgumentException('Outcome audit harus succeeded atau denied.');
        }

        $attributes = [
            'user_id' => $actor?->getKey(),
            'action' => $this->sanitizer->sanitizeText($action) ?: 'unknown',
            'outcome' => $outcome,
            'auditable_type' => $subject?->getMorphClass(),
            'auditable_id' => $subject?->getKey(),
            'reason' => $this->sanitizer->sanitizeText($reason),
            'before' => $this->sanitizer->sanitize($before),
            'after' => $this->sanitizer->sanitize($after),
            'ip_address' => $request?->ip(),
            'user_agent' => $this->sanitizer->sanitizeText($request?->userAgent()),
            'request_id' => $this->requestId($request),
            'context' => [
                ...$this->requestContext($request),
                ...$this->sanitizer->sanitize($context ?? []),
                'actor_type' => $actor !== null
                    ? 'user'
                    : ($request !== null ? 'anonymous' : 'system'),
            ],
            'created_at' => Carbon::now((string) config('app.timezone', 'Asia/Jakarta')),
        ];

        if ($outcome === 'denied' && $request !== null) {
            $request->attributes->set('sihati_denied_audited', true);
        }

        // A denied action must survive a domain transaction rollback. PostgreSQL
        // uses a second connection for that one write; SQLite defers the write
        // until either commit or rollback because a new in-memory connection
        // has no schema.
        if ($outcome === 'denied' && $this->deferDeniedAudit($attributes)) {
            $log = new AuditLog;
            $log->setConnection((string) config('database.default'));
            $log->forceFill($attributes);

            return $log;
        }

        $connectionName = $outcome === 'denied'
            ? $this->connectionForDeniedAudit()
            : (string) config('database.default');

        return $this->persist($attributes, $connectionName);
    }

    /** @param array<string, mixed> $attributes */
    private function persist(array $attributes, string $connectionName): AuditLog
    {
        $log = new AuditLog;
        $log->setConnection($connectionName);
        $log->forceFill($attributes);
        $log->save();

        return $log;
    }

    /** @param array<string, mixed> $attributes */
    private function deferDeniedAudit(array $attributes): bool
    {
        $default = (string) config('database.default');
        $connection = DB::connection($default);

        if ($connection->transactionLevel() === 0 || $connection->getDriverName() === 'pgsql') {
            return false;
        }

        try {
            $connection->afterCommit(fn () => $this->persist($attributes, $default));
            $connection->afterRollBack(fn () => $this->persist($attributes, $default));

            return true;
        } catch (\RuntimeException) {
            return false;
        }
    }

    public function succeeded(
        ?User $actor,
        string $action,
        ?Model $subject = null,
        ?string $reason = null,
        ?array $before = null,
        ?array $after = null,
        ?array $context = null,
    ): AuditLog {
        return $this->record($actor, $action, 'succeeded', $subject, $reason, $before, $after, null, $context);
    }

    public function denied(
        ?User $actor,
        string $action,
        ?Model $subject = null,
        ?string $reason = null,
        ?array $context = null,
    ): AuditLog {
        return $this->record($actor, $action, 'denied', $subject, $reason, null, null, null, $context);
    }

    /** @return array<string, mixed> */
    private function requestContext(?Request $request): array
    {
        if ($request === null) {
            return ['timezone' => config('app.timezone', 'Asia/Jakarta')];
        }

        $route = $request->route();

        return array_filter([
            'timezone' => config('app.timezone', 'Asia/Jakarta'),
            'route' => is_object($route) && is_string($route->getName()) ? $route->getName() : null,
            'method' => $request->method(),
            'path' => Str::limit($request->path(), 255, ''),
        ], static fn (mixed $value): bool => $value !== null && $value !== '');
    }

    private function requestId(?Request $request): ?string
    {
        if ($request === null) {
            return null;
        }

        $requestId = $request->attributes->get('sihati_request_id')
            ?: $request->header('X-Request-ID');

        if (! is_string($requestId) || trim($requestId) === '') {
            $requestId = (string) Str::uuid();
            $request->attributes->set('sihati_request_id', $requestId);
        }

        return $this->sanitizer->sanitizeText(Str::limit($requestId, 100, ''));
    }

    private function connectionForDeniedAudit(): string
    {
        $default = (string) config('database.default');
        $connection = DB::connection($default);

        if ($connection->transactionLevel() === 0 || $connection->getDriverName() !== 'pgsql') {
            return $default;
        }

        $auditConnection = 'sihati_audit';

        if (! config("database.connections.{$auditConnection}")) {
            config(["database.connections.{$auditConnection}" => config("database.connections.{$default}")]);
        }

        return $auditConnection;
    }
}
