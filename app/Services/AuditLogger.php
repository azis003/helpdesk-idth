<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
        ?array $context = null,
    ): AuditLog {
        $request ??= app()->bound('request') ? request() : null;

        $attributes = [
            'user_id' => $actor?->getKey(),
            'action' => $action,
            'outcome' => $outcome,
            'auditable_type' => $subject?->getMorphClass(),
            'auditable_id' => $subject?->getKey(),
            'reason' => $this->cleanText($reason),
            'before' => $this->sanitize($before),
            'after' => $this->sanitize($after),
            'ip_address' => $request?->ip(),
            'user_agent' => $this->cleanText($request?->userAgent()),
            'request_id' => $this->requestId($request),
            'context' => [
                ...$this->requestContext($request),
                ...$this->sanitize($context ?? []),
            ],
            'created_at' => Carbon::now((string) config('app.timezone', 'Asia/Jakarta')),
        ];

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

        return array_filter([
            'timezone' => config('app.timezone', 'Asia/Jakarta'),
            'route' => is_string($request->route()?->getName()) ? $request->route()?->getName() : null,
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

        return Str::limit($requestId, 100, '');
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

    private function cleanText(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return Str::limit(str_replace(["\0", "\r", "\n"], ' ', trim($value)), 10000, '');
    }

    private function sanitize(mixed $value, ?string $key = null): mixed
    {
        if ($this->isSensitiveKey($key)) {
            return '[REDACTED]';
        }

        if (is_array($value)) {
            $sanitized = [];

            foreach ($value as $childKey => $childValue) {
                $sanitized[$childKey] = $this->sanitize($childValue, is_string($childKey) ? $childKey : null);
            }

            return $sanitized;
        }

        if ($value instanceof \BackedEnum) {
            return $value->value;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }

        if (is_object($value)) {
            return '['.get_debug_type($value).']';
        }

        return is_string($value) ? $this->cleanText($value) : $value;
    }

    private function isSensitiveKey(?string $key): bool
    {
        if ($key === null) {
            return false;
        }

        $normalized = strtolower(str_replace(['-', ' ', '.'], '_', $key));

        foreach ([
            'password',
            'password_confirmation',
            'temporary_password',
            'secret',
            'token',
            'api_key',
            'access_token',
            'refresh_token',
            'authorization',
            'cookie',
        ] as $sensitive) {
            if ($normalized === $sensitive || str_contains($normalized, $sensitive)) {
                return true;
            }
        }

        return false;
    }
}
