<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketFieldValueHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'service_field_definition_id',
        'actor_id',
        'field_key',
        'label_snapshot',
        'field_type_snapshot',
        'visibility_snapshot',
        'version_snapshot',
        'change_type',
        'old_value',
        'new_value',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'version_snapshot' => 'integer',
            'old_value' => 'json',
            'new_value' => 'json',
            'occurred_at' => 'datetime',
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

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
