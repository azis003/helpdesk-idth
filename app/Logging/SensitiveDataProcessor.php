<?php

namespace App\Logging;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class SensitiveDataProcessor implements ProcessorInterface
{
    public function __invoke(LogRecord $record): LogRecord
    {
        return new LogRecord(
            datetime: $record->datetime,
            channel: $record->channel,
            level: $record->level,
            message: $this->redactMessage($record->message),
            context: $this->sanitize($record->context),
            extra: $this->sanitize($record->extra),
            formatted: $record->formatted,
        );
    }

    private function sanitize(mixed $value, ?string $key = null): mixed
    {
        if ($this->isSensitiveKey($key)) {
            return '[REDACTED]';
        }

        if (is_array($value)) {
            $result = [];

            foreach ($value as $childKey => $childValue) {
                $result[$childKey] = $this->sanitize(
                    $childValue,
                    is_string($childKey) ? $childKey : null,
                );
            }

            return $result;
        }

        if ($value instanceof \Throwable) {
            return [
                'class' => $value::class,
                'message' => $this->redactMessage($value->getMessage()),
            ];
        }

        if (is_object($value)) {
            return '['.get_debug_type($value).']';
        }

        return is_string($value) ? $this->redactMessage($value) : $value;
    }

    private function redactMessage(string $message): string
    {
        return (string) preg_replace(
            '/((?:password|secret|token|api[_-]?key|authorization)\s*[=:]\s*)([^\s,;]+)/i',
            '$1[REDACTED]',
            str_replace(["\0", "\r", "\n"], ' ', $message),
        );
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
            'authorization',
            'cookie',
            'nip',
            'email',
            'request_body',
            'body',
            'comment',
            'description',
            'solution',
            'location',
            'attachment',
        ] as $sensitive) {
            if ($normalized === $sensitive || str_contains($normalized, $sensitive)) {
                return true;
            }
        }

        return false;
    }
}
