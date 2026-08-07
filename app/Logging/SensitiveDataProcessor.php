<?php

namespace App\Logging;

use App\Support\SensitiveDataSanitizer;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class SensitiveDataProcessor implements ProcessorInterface
{
    public function __construct(private readonly SensitiveDataSanitizer $sanitizer = new SensitiveDataSanitizer) {}

    public function __invoke(LogRecord $record): LogRecord
    {
        return new LogRecord(
            datetime: $record->datetime,
            channel: $record->channel,
            level: $record->level,
            message: $this->sanitizer->sanitizeText($record->message) ?? '',
            context: $this->sanitizer->sanitize($record->context),
            extra: $this->sanitizer->sanitize($record->extra),
            formatted: is_string($record->formatted)
                ? $this->sanitizer->sanitizeText($record->formatted)
                : $this->sanitizer->sanitize($record->formatted),
        );
    }
}
