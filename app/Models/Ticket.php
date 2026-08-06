<?php

namespace App\Models;

use App\Enums\Priority;
use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'ticket_class',
        'ticket_year',
        'ticket_sequence',
        'subject',
        'requester_id',
        'created_by_id',
        'requester_name_snapshot',
        'requester_nip_snapshot',
        'requester_team_snapshot',
        'is_self_created',
        'status',
        'assigned_to_id',
        'assigned_tier',
        'last_triaged_by_id',
        'service_type_id',
        'service_type_variant_id',
        'problem_category_id',
        'service_type_code_snapshot',
        'service_type_name_snapshot',
        'service_type_variant_code_snapshot',
        'service_type_variant_label_snapshot',
        'priority',
        'description',
        'rejection_reason',
        'room_id',
        'building_name_snapshot',
        'floor_name_snapshot',
        'room_name_snapshot',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => TicketStatus::class,
            'priority' => Priority::class,
            'ticket_year' => 'integer',
            'ticket_sequence' => 'integer',
            'is_self_created' => 'boolean',
            'submitted_at' => 'datetime',
        ];
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    public function lastTriagedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_triaged_by_id');
    }

    public function problemCategory(): BelongsTo
    {
        return $this->belongsTo(ProblemCategory::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(TicketStatusHistory::class)->orderBy('occurred_at')->orderBy('id');
    }

    public function assignmentHistories(): HasMany
    {
        return $this->hasMany(TicketAssignmentHistory::class)->orderBy('occurred_at')->orderBy('id');
    }

    public function priorityHistories(): HasMany
    {
        return $this->hasMany(TicketPriorityHistory::class)->orderBy('occurred_at')->orderBy('id');
    }

    public function categoryHistories(): HasMany
    {
        return $this->hasMany(TicketCategoryHistory::class)->orderBy('occurred_at')->orderBy('id');
    }

    public function approvalRequests(): HasMany
    {
        return $this->hasMany(ApprovalRequest::class);
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function serviceTypeVariant(): BelongsTo
    {
        return $this->belongsTo(ServiceTypeVariant::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function fieldValues(): HasMany
    {
        return $this->hasMany(TicketFieldValue::class)->orderBy('id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class)->latest('id');
    }

    public function scopeNewQueue(Builder $query): Builder
    {
        return $query
            ->where('status', TicketStatus::Baru->value)
            ->whereNull('assigned_to_id');
    }

    public function scopeOrderForTierOneQueue(Builder $query): Builder
    {
        return $query
            ->orderByRaw("CASE priority WHEN 'kritis' THEN 1 WHEN 'tinggi' THEN 2 WHEN 'sedang' THEN 3 WHEN 'rendah' THEN 4 ELSE 5 END")
            ->orderByRaw('COALESCE(submitted_at, created_at) ASC')
            ->orderBy('id');
    }
}
