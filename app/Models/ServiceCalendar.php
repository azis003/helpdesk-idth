<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceCalendar extends Model
{
    use HasFactory;

    protected $fillable = [
        'timezone',
        'working_days',
        'opens_at',
        'closes_at',
        'version',
        'effective_from',
        'is_active',
        'changed_by',
    ];

    protected function casts(): array
    {
        return [
            'working_days' => 'array',
            'version' => 'integer',
            'effective_from' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function holidays(): HasMany
    {
        return $this->hasMany(ServiceCalendarHoliday::class)->orderBy('holiday_date');
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
