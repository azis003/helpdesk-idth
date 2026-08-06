<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportExport extends Model
{
    use HasFactory;

    public const TYPE_MONTHLY_TICKETS = 'monthly_tickets';

    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'type',
        'period_start',
        'period_end',
        'requested_by_id',
        'format',
        'status',
        'file_name',
        'row_count',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'row_count' => 'integer',
            'generated_at' => 'datetime',
        ];
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_id');
    }
}
