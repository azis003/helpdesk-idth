<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketFieldValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'service_field_definition_id',
        'field_key',
        'label_snapshot',
        'field_type_snapshot',
        'version_snapshot',
        'value',
    ];

    protected function casts(): array
    {
        return [
            'version_snapshot' => 'integer',
            'value' => 'json',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function serviceFieldDefinition(): BelongsTo
    {
        return $this->belongsTo(ServiceFieldDefinition::class);
    }
}
