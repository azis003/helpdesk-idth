<?php

namespace App\Models;

use Database\Factories\OrganizationSettingFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationSetting extends Model
{
    /** @use HasFactory<OrganizationSettingFactory> */
    use HasFactory;

    protected $fillable = [
        'organization_name',
        'application_name',
        'tagline',
        'footer_text',
        'logo_path',
        'logo_disk',
        'version',
        'effective_from',
        'is_active',
        'changed_by',
    ];

    protected function casts(): array
    {
        return [
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
}
