<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'user_id',
        'description',
        'started_at',
        'ended_at',
        'duration',
        'is_billable',
        'is_running',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'is_billable' => 'boolean',
        'is_running' => 'boolean',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function start(): void
    {
        $this->update([
            'started_at' => now(),
            'is_running' => true,
        ]);
    }

    public function stop(): void
    {
        $endedAt = now();
        $duration = $this->started_at->diffInMinutes($endedAt);

        $this->update([
            'ended_at' => $endedAt,
            'duration' => $duration,
            'is_running' => false,
        ]);

        // Update task's time spent
        $this->task->increment('time_spent', $duration);
    }

    public function getFormattedDurationAttribute(): string
    {
        $hours = floor($this->duration / 60);
        $minutes = $this->duration % 60;

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }

        return "{$minutes}m";
    }
}
