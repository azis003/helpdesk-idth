<?php

namespace App\Models;

use App\Models\Builders\AuditLogBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class AuditLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected static string $builder = AuditLogBuilder::class;

    protected $fillable = [
        'user_id',
        'action',
        'outcome',
        'auditable_type',
        'auditable_id',
        'reason',
        'before',
        'after',
        'ip_address',
        'user_agent',
        'request_id',
        'context',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'before' => 'array',
            'after' => 'array',
            'context' => 'array',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Audit log bersifat append-only.'));
        static::deleting(fn () => throw new LogicException('Audit log bersifat append-only.'));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
