<?php

namespace App\Support;

use Illuminate\Support\Str;

final class SensitiveDataSanitizer
{
    public const REDACTED = '[REDACTED]';

    /**
     * Sanitize nested values before they are written to an audit or application log.
     */
    public function sanitize(mixed $value, ?string $key = null): mixed
    {
        if ($this->isSensitiveKey($key)) {
            return self::REDACTED;
        }

        if (is_array($value)) {
            $sanitized = [];

            foreach ($value as $childKey => $childValue) {
                $sanitized[$childKey] = $this->sanitize(
                    $childValue,
                    is_string($childKey) ? $childKey : null,
                );
            }

            return $sanitized;
        }

        if ($value instanceof \BackedEnum) {
            return $value->value;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }

        if ($value instanceof \Throwable) {
            return [
                'class' => $value::class,
                'message' => $this->sanitizeText($value->getMessage()),
            ];
        }

        if (is_object($value)) {
            return '['.get_debug_type($value).']';
        }

        return is_string($value) ? $this->sanitizeText($value) : $value;
    }

    public function sanitizeText(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = str_replace(["\0", "\r", "\n"], ' ', trim($value));

        // Redact labelled credentials even when they arrive inside a free-form
        // reason or exception message rather than as a structured context key.
        $value = (string) preg_replace(
            '/((?:password|secret|token|api[_-]?key|authorization|cookie|private[_-]?key)\s*[=:]\s*)([^\s,;]+)/i',
            '$1'.self::REDACTED,
            $value,
        );

        // NIP/NIK and email addresses are personal data and should not leak
        // through a log message that was assembled by a third-party package.
        $value = (string) preg_replace(
            '/((?:nip|nik)(?:\s*[=:]\s*|\s+))\d{4,32}\b/i',
            '$1'.self::REDACTED,
            $value,
        );
        $value = (string) preg_replace(
            '/\b[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}\b/i',
            self::REDACTED,
            $value,
        );

        return Str::limit($value, 10000, '');
    }

    private function isSensitiveKey(?string $key): bool
    {
        if ($key === null) {
            return false;
        }

        $normalized = strtolower(str_replace(['-', ' ', '.'], '_', $key));

        foreach ([
            'password',
            'secret',
            'token',
            'api_key',
            'access_token',
            'refresh_token',
            'authorization',
            'cookie',
            'private_key',
            'email',
            'nip',
            'nik',
            'phone',
            'mobile',
            'telephone',
            'username',
            'requester_name',
            'created_by_name',
            'user_name',
            'assignee_name',
            'actor_name',
            'full_name',
            'comment',
            'body',
            'description',
            'solution',
            'location',
            'attachment',
            'original_name',
        ] as $sensitive) {
            if ($normalized === $sensitive || str_contains($normalized, $sensitive)) {
                return true;
            }
        }

        return false;
    }
}
