<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'body',
        'starts_at',
        'ends_at',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActiveAt(Builder $query, $moment = null): Builder
    {
        $moment ??= now();

        return $query
            ->where('is_active', true)
            ->where('starts_at', '<=', $moment)
            ->where(function (Builder $query) use ($moment): void {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', $moment);
            });
    }
}
