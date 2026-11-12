<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduledJobRun extends Model
{
    protected $fillable = [
        'job_name',
        'status',
        'processed_count',
        'expired_count',
        'grace_count',
        'reminders_created_count',
        'failed_count',
        'summary',
        'error_message',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'processed_count' => 'integer',
        'expired_count' => 'integer',
        'grace_count' => 'integer',
        'reminders_created_count' => 'integer',
        'failed_count' => 'integer',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];
}
