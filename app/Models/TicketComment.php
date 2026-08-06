<?php

namespace App\Models;

use App\Enums\TicketCommentVisibility;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

class TicketComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'author_id',
        'visibility',
        'body',
    ];

    protected function casts(): array
    {
        return [
            'visibility' => TicketCommentVisibility::class,
        ];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Komentar tiket bersifat append-only.'));
        static::deleting(fn () => throw new LogicException('Komentar tiket bersifat append-only.'));
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'ticket_comment_id')->latest('id');
    }
}
