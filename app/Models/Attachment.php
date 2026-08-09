<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attachment extends Model
{
    use HasFactory, SoftDeletes;

    public const DATA_EXPORT_RESULT_TYPE = 'data_export_result';

    public const DATA_EXPORT_RESULT_LABEL = 'Hasil tarik data';

    public const DATA_EXPORT_RESULT_MAX_SIZE_KB = 10240;

    protected $fillable = [
        'ticket_id',
        'ticket_comment_id',
        'attachment_policy_id',
        'uploaded_by_id',
        'type_key',
        'type_label_snapshot',
        'original_name',
        'storage_disk',
        'storage_path',
        'size_bytes',
        'mime_type',
        'extension',
        'sha256',
        'visibility',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
        ];
    }

    public function isRequesterAccessible(): bool
    {
        return in_array($this->visibility, ['requester', 'both'], true);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function ticketComment(): BelongsTo
    {
        return $this->belongsTo(TicketComment::class, 'ticket_comment_id');
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(AttachmentPolicy::class, 'attachment_policy_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_id');
    }

    public function scopeVisibleToRequester(Builder $query): Builder
    {
        return $query->whereIn('visibility', ['requester', 'both']);
    }
}
