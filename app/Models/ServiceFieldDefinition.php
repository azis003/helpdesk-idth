<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceFieldDefinition extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_type_id',
        'key',
        'label',
        'help_text',
        'field_type',
        'visibility',
        'is_required',
        'sort_order',
        'version',
        'validation_rules',
        'visibility_rules',
        'is_active',
        'supersedes_id',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'sort_order' => 'integer',
            'version' => 'integer',
            'validation_rules' => 'array',
            'visibility_rules' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(ServiceFieldOption::class)->orderBy('sort_order')->orderBy('id');
    }

    public function activeOptions(): HasMany
    {
        return $this->options()->where('is_active', true);
    }

    public function supersedes(): BelongsTo
    {
        return $this->belongsTo(self::class, 'supersedes_id');
    }

    public function successors(): HasMany
    {
        return $this->hasMany(self::class, 'supersedes_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
