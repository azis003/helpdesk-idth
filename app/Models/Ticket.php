<?php

namespace App\Models;

use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject',
        'status',
        'assigned_to_id',
        'assigned_tier',
    ];

    protected function casts(): array
    {
        return [
            'status' => TicketStatus::class,
        ];
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    public function approvalRequests(): HasMany
    {
        return $this->hasMany(ApprovalRequest::class);
    }
}
