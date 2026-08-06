<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceCalendarHoliday extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_calendar_id',
        'holiday_date',
        'name',
    ];

    protected function casts(): array
    {
        return [
            'holiday_date' => 'date',
        ];
    }

    public function serviceCalendar(): BelongsTo
    {
        return $this->belongsTo(ServiceCalendar::class);
    }
}
