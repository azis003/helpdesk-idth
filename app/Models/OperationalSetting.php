<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperationalSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'value_type',
        'version',
        'effective_from',
        'is_active',
        'changed_by',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'json',
            'version' => 'integer',
            'effective_from' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForKey(Builder $query, string $key): Builder
    {
        return $query->where('key', $key);
    }
}
