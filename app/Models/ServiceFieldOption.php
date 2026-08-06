<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceFieldOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_field_definition_id',
        'value',
        'label',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function fieldDefinition(): BelongsTo
    {
        return $this->belongsTo(ServiceFieldDefinition::class, 'service_field_definition_id');
    }
}
