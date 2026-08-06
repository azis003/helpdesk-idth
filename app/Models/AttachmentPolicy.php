<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttachmentPolicy extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'service_type_id',
        'type_key',
        'label',
        'max_file_size_kb',
        'max_file_count',
        'allowed_mimes',
        'allowed_extensions',
        'visibility',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'max_file_size_kb' => 'integer',
            'max_file_count' => 'integer',
            'allowed_mimes' => 'array',
            'allowed_extensions' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
