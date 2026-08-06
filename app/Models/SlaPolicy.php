<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlaPolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_type_id',
        'target_working_days',
        'uses_sla',
        'version',
        'effective_from',
        'is_active',
        'changed_by',
    ];

    protected function casts(): array
    {
        return [
            'target_working_days' => 'integer',
            'uses_sla' => 'boolean',
            'version' => 'integer',
            'effective_from' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
